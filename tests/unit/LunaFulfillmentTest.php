<?php

use App\Services\Integrations\LunaFulfillment;
use CodeIgniter\Test\CIUnitTestCase;

final class LunaFulfillmentTest extends CIUnitTestCase
{
    protected $db;
    private LunaFulfillment $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = \Config\Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $this->db->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT, email_cus TEXT, status TEXT, resi TEXT, data_mid TEXT)');
        $this->db->query('CREATE TABLE customer_push_notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, notification_type TEXT NOT NULL, title TEXT NOT NULL, body TEXT NOT NULL, payload TEXT, dedupe_key TEXT UNIQUE NOT NULL, status TEXT NOT NULL, attempts INTEGER NOT NULL, next_attempt_at TEXT NOT NULL, sent_at TEXT, last_error TEXT, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
        $this->db->table('pemesanan')->insert(['id_midtrans'=>'LTEST', 'email_cus'=>'buyer@example.test', 'status'=>'Proses', 'resi'=>'',
            'data_mid'=>json_encode(['_app_payment_environment'=>'production', 'gross_amount'=>'123456.00', 'transaction_time'=>'2026-01-01 12:00:00'])]);
        $this->service = new LunaFulfillment($this->db);
    }

    protected function tearDown(): void { $this->db->close(); parent::tearDown(); }
    private function event(string $stage = 'processed'): array { return ['stage'=>$stage, 'sale_id'=>'sale-test', 'processed_at'=>$stage === 'processed' ? '2026-01-01T06:00:00.000Z' : null]; }
    private function row(): array { return $this->db->table('pemesanan')->get()->getRowArray(); }

    public function testProcessedDoesNotDispatchOrChangePaymentAndReplayIsIdempotent(): void
    {
        $original = $this->row();
        $this->service->record('LTEST', $this->event('received'), []);
        $this->service->record('LTEST', $this->event(), []);
        $processed = $this->row();
        $this->service->record('LTEST', $this->event(), []);
        $this->service->record('LTEST', $this->event('received'), []);
        $this->assertSame(1, $this->db->table('customer_push_notifications')->countAllResults());
        $this->assertSame($processed, $this->row());
        $this->assertSame('Proses', $processed['status']); $this->assertSame('', $processed['resi']);
        $mid = json_decode($processed['data_mid'], true);
        unset($mid['_app_luna_fulfillment']);
        $this->assertSame(json_decode($original['data_mid'], true), $mid);
    }
    public function testLateCallbackNeverRegressesDispatch(): void
    {
        $this->db->table('pemesanan')->update(['status'=>'Dikirim', 'resi'=>'TEST123']);
        $this->service->record('LTEST', $this->event(), []);
        $this->assertSame('Dikirim', $this->row()['status']); $this->assertSame('TEST123', $this->row()['resi']);
    }
    public function testSandboxIsExcluded(): void
    {
        $this->db->table('pemesanan')->update(['data_mid'=>'{"_app_payment_environment":"sandbox"}']);
        $before = $this->row();
        try { $this->service->record('LTEST', $this->event(), []); $this->fail('Sandbox accepted'); }
        catch (DomainException $error) { $this->assertSame($before, $this->row()); }
    }
    public function testUnpaidOrderIsExcluded(): void
    {
        $this->db->table('pemesanan')->update(['status'=>'Menunggu Pembayaran']);
        $this->expectException(DomainException::class); $this->service->record('LTEST', $this->event(), []);
    }
    public function testDifferentSaleCannotOverwriteAcknowledgement(): void
    {
        $this->service->record('LTEST', $this->event(), []);
        $event = $this->event(); $event['sale_id'] = 'different';
        $this->expectException(DomainException::class); $this->service->record('LTEST', $event, []);
    }
    public function testFailedOutboxInsertRollsBackProcessedAcknowledgement(): void
    {
        $this->db->query("CREATE TRIGGER reject_customer_push BEFORE INSERT ON customer_push_notifications BEGIN SELECT RAISE(ABORT, 'synthetic outbox failure'); END");
        try { $this->service->record('LTEST', $this->event(), []); $this->fail('Acknowledgement committed without push outbox.'); }
        catch (RuntimeException $error) {
            $mid = json_decode($this->row()['data_mid'], true);
            $this->assertArrayNotHasKey('_app_luna_fulfillment', $mid);
            $this->assertSame(0, $this->db->table('customer_push_notifications')->countAllResults());
        }
    }
    public function testInvalidAndMissingProcessTimeIsRejected(): void
    {
        foreach ([null, 'now', '2026-02-30T06:00:00Z', '2099-01-01T00:00:00Z'] as $time) {
            try { $this->service->record('LTEST', array_merge($this->event(), ['processed_at'=>$time]), []); $this->fail('Invalid time accepted'); }
            catch (DomainException $error) { $this->assertSame('Proses', $this->row()['status']); }
        }
    }
    public function testAnonymousCallbackStopsBeforeDatabase(): void
    {
        $controller = new class extends \App\Controllers\Pages {
            public function __construct() {
                $this->response=\Config\Services::response(null,false);
                $this->request=new class { public function getMethod(){return 'post';} public function getHeaderLine($name){return '';} };
            }
        };
        $this->assertSame(401, $controller->updateOrderStatusFromLuna()->getStatusCode());
    }
}
