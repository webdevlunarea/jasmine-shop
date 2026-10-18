<?php

use App\Services\Shipping\OrderShippingManager;
use CodeIgniter\Test\CIUnitTestCase;

final class OrderShippingPushOutboxTest extends CIUnitTestCase
{
    private $connection;
    private OrderShippingManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        $this->connection->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT, email_cus TEXT, status TEXT, resi TEXT, kurir TEXT, data_mid TEXT)');
        $this->connection->query('CREATE TABLE order_shipments (id INTEGER PRIMARY KEY, id_midtrans TEXT, shipping_method_code TEXT, shipping_status TEXT, tracking_number TEXT, shipping_cost INTEGER, updated_at TEXT)');
        $this->connection->query('CREATE TABLE customer_push_notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, notification_type TEXT NOT NULL, title TEXT NOT NULL, body TEXT NOT NULL, payload TEXT, dedupe_key TEXT UNIQUE NOT NULL, status TEXT NOT NULL, attempts INTEGER NOT NULL, next_attempt_at TEXT NOT NULL, sent_at TEXT, last_error TEXT, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
        $this->connection->table('pemesanan')->insert(['id_midtrans'=>'L-PUSH','email_cus'=>'buyer@example.test','status'=>'Proses','resi'=>'','kurir'=>'JNE','data_mid'=>'{}']);
        $this->connection->table('order_shipments')->insert(['id_midtrans'=>'L-PUSH','shipping_method_code'=>'rajaongkir_domestic','shipping_status'=>'Disiapkan','tracking_number'=>'','shipping_cost'=>1000,'updated_at'=>'2026-01-01 00:00:00']);
        $this->manager = new OrderShippingManager($this->connection);
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    public function testShippingStateAndPushEventCommitTogetherAndReplayIsDeduplicated(): void
    {
        $result = $this->manager->update('L-PUSH',['kurir'=>'JNE','resi'=>'TEST123','shipping_status'=>'Dalam Pengiriman']);
        $this->assertSame('Disiapkan',$result['previous_shipping_status']);
        $this->assertSame('Dalam Pengiriman',$result['shipment']['shipping_status']);
        $event = $this->connection->table('customer_push_notifications')->get()->getRowArray();
        $this->assertSame('Pesanan sedang dikirim',$event['title']);
        $this->assertSame(['order_id'=>'L-PUSH','status'=>'Dalam Pengiriman','tracking_number'=>'TEST123','courier'=>'JNE'],json_decode($event['payload'],true));

        $replay = $this->manager->update('L-PUSH',['kurir'=>'JNE','resi'=>'TEST123','shipping_status'=>'Dalam Pengiriman']);
        $this->assertFalse($replay['changed']);
        $this->assertSame(1,$this->connection->table('customer_push_notifications')->countAllResults());
    }

    public function testFailedOutboxInsertRollsBackShippingStatus(): void
    {
        $this->connection->query("CREATE TRIGGER reject_customer_push BEFORE INSERT ON customer_push_notifications BEGIN SELECT RAISE(ABORT, 'synthetic outbox failure'); END");
        try {
            $this->manager->update('L-PUSH',['kurir'=>'JNE','resi'=>'TEST123','shipping_status'=>'Dalam Pengiriman']);
            $this->fail('Shipping update committed without its outbox event.');
        } catch (Throwable $error) {
            $this->assertSame('Proses',$this->connection->table('pemesanan')->get()->getRowArray()['status']);
            $this->assertSame('Disiapkan',$this->connection->table('order_shipments')->get()->getRowArray()['shipping_status']);
            $this->assertSame(0,$this->connection->table('customer_push_notifications')->countAllResults());
        }
    }
}
