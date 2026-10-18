<?php

use CodeIgniter\Test\CIUnitTestCase;

final class PaymentNotificationIsolationTest extends CIUnitTestCase
{
    private $memoryDb;
    protected function setUp(): void
    {
        parent::setUp();
        $this->memoryDb=\Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
        $this->memoryDb->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT UNIQUE, email_cus TEXT, status TEXT, data_mid TEXT, idVoucher INTEGER DEFAULT 0, pakai_poin INTEGER DEFAULT 0, items TEXT DEFAULT "[]")');
        $this->memoryDb->query('CREATE TABLE customer_push_notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, notification_type TEXT NOT NULL, title TEXT NOT NULL, body TEXT NOT NULL, payload TEXT, dedupe_key TEXT UNIQUE NOT NULL, status TEXT NOT NULL, attempts INTEGER NOT NULL, next_attempt_at TEXT NOT NULL, sent_at TEXT, last_error TEXT, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
        $this->memoryDb->table('pemesanan')->insert(['id_midtrans'=>'LTEST','email_cus'=>'test@example.test','status'=>'Menunggu Pembayaran',
            'data_mid'=>json_encode(['gross_amount'=>100000,'payment_type'=>'bank_transfer','transaction_id'=>'TEST-TX','_app_payment_environment'=>'sandbox','_app_payment_effects_applied'=>false])]);
    }
    protected function tearDown(): void { $this->memoryDb->close();parent::tearDown(); }
    private function controller(array $body)
    {
        return new class($this->memoryDb,$body) extends \App\Controllers\Pages {
            private $memory;
            public array $notifications=[];
            public function __construct($db,array $body) {
                $this->memory=$db; $this->emailUjiCoba=['test@example.test'];
                $this->request=new class($body) { private array $body;public function __construct(array $body){$this->body=$body;}public function getBody(){return json_encode($this->body);} };
                $this->response=\Config\Services::response(null,false);
                $this->pemesananModel=new class($db) {
                    private $memoryDb;private array $where=[];
                    public function __construct($db){$this->memoryDb=$db;}
                    public function getPemesanan($id){return $this->memoryDb->table('pemesanan')->where('id_midtrans',$id)->get()->getRowArray();}
                    public function where($key,$value){$this->where=[$key=>$value];return $this;}
                    public function first(){return $this->memoryDb->table('pemesanan')->where($this->where)->get()->getRowArray();}
                };
                // Operational models intentionally remain null: any accidental sandbox effect fails the test.
            }
            protected function paymentDatabase(){return $this->memory;}
            protected function notifyVerifiedPayment(array $order,string $old,string $status,bool $sandbox):void {
                $this->notifications[]=[$order['id_midtrans'],$old,$status,$sandbox];
            }
        };
    }
    private function body(): array
    {
        $body=['order_id'=>'LTEST','gross_amount'=>'100000.00','status_code'=>'200','transaction_status'=>'settlement','transaction_id'=>'TEST-TX'];
        $key=(string)env('MIDTRANS_SANDBOX_KEY');
        $body['signature_key']=hash('sha512','LTEST'.'200'.'100000.00'.$key);
        return $body;
    }
    private function productionExpiryBody(): array
    {
        $body=['order_id'=>'LTEST','gross_amount'=>'100000.00','status_code'=>'202','transaction_status'=>'expire','transaction_id'=>'TEST-TX'];
        $key=(string)env('MIDTRANS_PRODUCTION_KEY');
        $body['signature_key']=hash('sha512','LTEST'.'202'.'100000.00'.$key);
        return $body;
    }
    public function testSignedSandboxCallbackUpdatesBuyerStatusOnceWithoutOperationalModels(): void
    {
        $old=getenv('MIDTRANS_SANDBOX_KEY');putenv('MIDTRANS_SANDBOX_KEY=isolated-unit-key');
        try {
            $controller=$this->controller($this->body());$response=$controller->updateTransaction();
            $this->assertSame(200,$response->getStatusCode());
            $this->assertSame('Proses',$this->memoryDb->table('pemesanan')->get()->getRowArray()['status']);
            $this->assertCount(1,$controller->notifications);
            $response=$controller->updateTransaction();$this->assertSame(200,$response->getStatusCode());
            $this->assertCount(1,$controller->notifications);
        } finally { $old===false?putenv('MIDTRANS_SANDBOX_KEY'):putenv('MIDTRANS_SANDBOX_KEY='.$old); }
    }
    public function testForgeryAndWrongAmountsNeverUpdateOrder(): void
    {
        foreach ([['order_id'=>'LTEST','signature_key'=>'fake'],['order_id'=>'LTEST','signature_key'=>'fake','gross_amount'=>'1.00','status_code'=>'200','transaction_status'=>'settlement']] as $body) {
            $controller=$this->controller($body);
            $this->assertSame(403,$controller->updateTransaction()->getStatusCode());
            $this->assertSame('Menunggu Pembayaran',$this->memoryDb->table('pemesanan')->get()->getRowArray()['status']);
            $this->assertCount(0,$controller->notifications);
        }
    }
    public function testImmediatePaidSandboxRecordStillCompletesFirstCallbackBookkeeping(): void
    {
        $old=getenv('MIDTRANS_SANDBOX_KEY');putenv('MIDTRANS_SANDBOX_KEY=isolated-unit-key');
        try {
            $this->memoryDb->table('pemesanan')->update(['status'=>'Proses']);
            $controller=$this->controller($this->body());
            $this->assertSame(200,$controller->updateTransaction()->getStatusCode());
            $mid=json_decode($this->memoryDb->table('pemesanan')->get()->getRowArray()['data_mid'],true);
            $this->assertTrue($mid['_app_payment_effects_applied']);
            $this->assertCount(1,$controller->notifications);
        } finally { $old===false?putenv('MIDTRANS_SANDBOX_KEY'):putenv('MIDTRANS_SANDBOX_KEY='.$old); }
    }
    public function testProductionPaymentStatusAndPushOutboxCommitAtomically(): void
    {
        $old=getenv('MIDTRANS_PRODUCTION_KEY');putenv('MIDTRANS_PRODUCTION_KEY=isolated-production-unit-key');
        try {
            $this->memoryDb->table('pemesanan')->update([
                'email_cus'=>'buyer@example.test',
                'data_mid'=>json_encode(['gross_amount'=>100000,'payment_type'=>'bank_transfer','transaction_id'=>'TEST-TX','_app_payment_environment'=>'production','_app_payment_effects_applied'=>false]),
            ]);
            $controller=$this->controller($this->productionExpiryBody());
            $this->assertSame(200,$controller->updateTransaction()->getStatusCode());
            $this->assertSame('Kadaluarsa',$this->memoryDb->table('pemesanan')->get()->getRowArray()['status']);
            $event=$this->memoryDb->table('customer_push_notifications')->get()->getRowArray();
            $this->assertSame('buyer@example.test',$event['email_user']);
            $this->assertSame('payment_status',$event['notification_type']);
            $this->assertSame(['order_id'=>'LTEST','status'=>'Kadaluarsa'],json_decode($event['payload'],true));
            $controller->updateTransaction();
            $this->assertSame(1,$this->memoryDb->table('customer_push_notifications')->countAllResults());
        } finally { $old===false?putenv('MIDTRANS_PRODUCTION_KEY'):putenv('MIDTRANS_PRODUCTION_KEY='.$old); }
    }
    public function testFailedPaymentOutboxInsertRollsBackTheStatusTransition(): void
    {
        $old=getenv('MIDTRANS_PRODUCTION_KEY');putenv('MIDTRANS_PRODUCTION_KEY=isolated-production-unit-key');
        try {
            $this->memoryDb->table('pemesanan')->update([
                'email_cus'=>'buyer@example.test',
                'data_mid'=>json_encode(['gross_amount'=>100000,'payment_type'=>'bank_transfer','transaction_id'=>'TEST-TX','_app_payment_environment'=>'production','_app_payment_effects_applied'=>false]),
            ]);
            $this->memoryDb->query("CREATE TRIGGER reject_customer_push BEFORE INSERT ON customer_push_notifications BEGIN SELECT RAISE(ABORT, 'synthetic outbox failure'); END");
            $controller=$this->controller($this->productionExpiryBody());
            $this->assertSame(500,$controller->updateTransaction()->getStatusCode());
            $this->assertSame('Menunggu Pembayaran',$this->memoryDb->table('pemesanan')->get()->getRowArray()['status']);
            $this->assertSame(0,$this->memoryDb->table('customer_push_notifications')->countAllResults());
        } finally { $old===false?putenv('MIDTRANS_PRODUCTION_KEY'):putenv('MIDTRANS_PRODUCTION_KEY='.$old); }
    }
}
