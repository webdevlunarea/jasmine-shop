<?php

use App\Services\Payments\PaymentExpiry;
use App\Services\Payments\MidtransOrderClient;
use CodeIgniter\Test\CIUnitTestCase;

final class PendingPaymentLifecycleTest extends CIUnitTestCase
{
    protected $db;
    private array $oldKeys = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['MIDTRANS_SANDBOX_KEY','MIDTRANS_PRODUCTION_KEY'] as $key) {
            $this->oldKeys[$key]=getenv($key); putenv($key.'=isolated-payment-key');
        }
        $this->db=\Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
        $this->db->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT UNIQUE, email_cus TEXT, status TEXT, data_mid TEXT, items TEXT, idVoucher INTEGER, pakai_poin INTEGER, cashback INTEGER, bukti_bayar TEXT)');
        $this->db->query('CREATE TABLE barang (id TEXT PRIMARY KEY, nama TEXT, varian TEXT, stok TEXT)');
        $this->db->query('CREATE TABLE pembeli (id INTEGER PRIMARY KEY, email_user TEXT UNIQUE, poin TEXT)');
        $this->db->query('CREATE TABLE voucher_claimed (id INTEGER PRIMARY KEY, id_voucher INTEGER, email_user TEXT, active INTEGER)');
        $this->db->table('barang')->insert(['id'=>'B1','nama'=>'Lemari','varian'=>'["Putih"]','stok'=>'8']);
        $this->db->table('pemesanan')->insert(['id_midtrans'=>'LTEST','email_cus'=>'buyer@example.test','status'=>'Menunggu Pembayaran',
            'items'=>'[{"id":"B1","name":"Lemari (Putih)","value":50000,"quantity":2}]','idVoucher'=>0,'pakai_poin'=>0,'cashback'=>0,'bukti_bayar'=>'',
            'data_mid'=>json_encode(['gross_amount'=>100000,'payment_type'=>'bank_transfer','transaction_id'=>'TX-TEST','expiry_time'=>'2020-01-01 10:00:00','_app_payment_environment'=>'sandbox','_app_payment_effects_applied'=>false])]);
        session()->set(['email'=>'buyer@example.test','role'=>'0','isLogin'=>true]);
    }

    protected function tearDown(): void
    {
        foreach ($this->oldKeys as $key=>$value) putenv($value===false ? $key : $key.'='.$value);
        session()->remove(['email','role','isLogin','msg']);
        $this->db->close(); parent::tearDown();
    }

    private function saved(): array { return $this->db->table('pemesanan')->get()->getRowArray(); }

    private function change(array $fields, array $payment=[]): void
    {
        if ($payment) $fields['data_mid']=json_encode(array_merge(json_decode($this->saved()['data_mid'],true),$payment));
        $this->db->table('pemesanan')->where('id_midtrans','LTEST')->update($fields);
    }

    private function body(string $status, string $code='200'): array
    {
        $body=['order_id'=>'LTEST','transaction_id'=>'TX-TEST','status_code'=>$code,'gross_amount'=>'100000.00','transaction_status'=>$status,'fraud_status'=>'accept'];
        $body['signature_key']=hash('sha512','LTEST'.$code.'100000.00'.'isolated-payment-key');
        return $body;
    }

    private function controller(array $queue=[], string $method='post')
    {
        return new class($this->db,$queue,$method) extends \App\Controllers\Pages {
            private $memory;
            public array $calls=[];
            public array $queue;
            public array $notifications=[];
            public int $csrfChecks=0;
            public function __construct($db,array $queue,string $method) {
                $this->memory=$db; $this->queue=$queue; $this->emailUjiCoba=['buyer@example.test'];
                $this->response=\Config\Services::response(null,false);
                $this->request=new class($method) { private string $method; public function __construct($method){$this->method=$method;} public function getMethod(){return $this->method;} };
                $this->pemesananModel=new class($db) {
                    private $db; private array $where=[];
                    public function __construct($db){$this->db=$db;}
                    public function getPemesanan($id){return $this->db->table('pemesanan')->where('id_midtrans',$id)->get()->getRowArray();}
                    public function where($key,$value){$this->where=[$key=>$value];return $this;}
                    public function first(){return $this->db->table('pemesanan')->where($this->where)->get()->getRowArray();}
                };
                $this->barangModel=new \App\Models\BarangModel($db);
                $this->voucherClaimedModel=new \App\Models\VoucherClaimedModel($db);
            }
            public function reconcile(array $order): array { return $this->reconcilePendingOrder($order,true); }
            protected function paymentDatabase(){return $this->memory;}
            protected function verifyBuyerPaymentRequest():void { $this->csrfChecks++; }
            protected function paymentRemoteRequest(array $order,string $operation):array {
                $this->calls[]=$operation;
                $next=array_shift($this->queue);
                if ($next instanceof \Throwable) throw $next;
                if (is_callable($next)) return $next();
                if (!is_array($next)) throw new \RuntimeException('Unexpected remote call');
                return $next;
            }
            protected function notifyVerifiedPayment(array $order,string $old,string $status,bool $sandbox):void { $this->notifications[]=[$old,$status,$sandbox]; }
        };
    }

    public function testCountdownUsesJakartaIndependentlyOfPhpTimezoneAndDoesNotWrapAfter24Hours(): void
    {
        $old=date_default_timezone_get();
        try {
            date_default_timezone_set('America/New_York');
            $payment=['expiry_time'=>'2026-10-16 12:00:00'];
            $stamp=strtotime('2026-10-16T05:00:00Z');
            $this->assertSame($stamp,PaymentExpiry::timestamp($payment));
            $this->assertSame('16 Okt 2026 12:00:00 WIB',PaymentExpiry::label($payment));
            $this->assertSame('25:01:01',PaymentExpiry::countdown($payment,$stamp-90061));
            $this->assertTrue(PaymentExpiry::isDue($payment,$stamp));
            $this->assertSame('00:00:00',PaymentExpiry::countdown($payment,$stamp+1));
            $this->assertNull(PaymentExpiry::timestamp(['expiry_time'=>'2026-02-31 12:00:00']));
            $this->assertFalse(PaymentExpiry::isDue([],$stamp));
        } finally {date_default_timezone_set($old);}
    }

    public function testSandboxExpiresWithoutProductionStockOrLoyaltyModelsAndOnlyOnce(): void
    {
        $controller=$this->controller([$this->body('expire','407')]);
        $this->assertSame('Kadaluarsa',$controller->reconcile($this->saved())['status']);
        $this->assertSame('Kadaluarsa',$controller->reconcile($this->saved())['status']);
        $this->assertSame(['status'],$controller->calls);
        $this->assertCount(1,$controller->notifications);
        $this->assertSame('8',$this->db->table('barang')->get()->getRowArray()['stok']);
    }

    public function testDeadlineAloneOrTimeoutNeverExpiresGatewayOrder(): void
    {
        foreach ([$this->body('pending','201'),new \RuntimeException('timeout')] as $reply) {
            $result=$this->controller([$reply])->reconcile($this->saved());
            $this->assertSame('Menunggu Pembayaran',$result['status']);
        }
        $tampered=$this->body('expire');$tampered['gross_amount']='1';
        $this->assertSame('Menunggu Pembayaran',$this->controller([$tampered])->reconcile($this->saved())['status']);
        $this->assertSame('8',$this->db->table('barang')->get()->getRowArray()['stok']);
    }

    public function testCancellationImmediatelyPersistsConfirmedProviderStateAndIsIdempotent(): void
    {
        $controller=$this->controller([$this->body('pending','201'),['status_code'=>'200'],$this->body('cancel')]);
        $controller->cancelOrder('LTEST');
        $this->assertSame('Dibatalkan',$this->saved()['status']);
        $this->assertSame(['status','cancel','status'],$controller->calls);
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $this->assertStringContainsString("\$routes->post('/cancelorder/(:any)', 'Pages::cancelOrder/$1', ['filter' => ['harusLogin', 'csrf']]);", $routes);
        $controller->cancelOrder('LTEST');
        $this->assertCount(3,$controller->calls);
        $this->assertCount(1,$controller->notifications);
        $this->assertSame('8',$this->db->table('barang')->get()->getRowArray()['stok']);
    }

    public function testPaidStatusWinsCancellationRaceAndNeverCallsCancelForAlreadyPaidProvider(): void
    {
        $controller=$this->controller([$this->body('settlement')]);
        $controller->cancelOrder('LTEST');
        $this->assertSame('Proses',$this->saved()['status']);
        $this->assertSame(['status'],$controller->calls);
        $this->assertSame('8',$this->db->table('barang')->get()->getRowArray()['stok']);
    }

    public function testConcurrentPaidCallbackCannotBeOverwrittenByCancel(): void
    {
        $controller=$this->controller([$this->body('pending','201'),function(){ $this->change(['status'=>'Proses']); return ['status_code'=>'200']; },$this->body('cancel')]);
        $controller->cancelOrder('LTEST');
        $this->assertSame('Proses',$this->saved()['status']);
        $this->assertCount(0,$controller->notifications);
    }

    public function testOwnershipAndHttpMethodCheckedBeforeAnyProviderCall(): void
    {
        session()->set('email','another@example.test');
        $controller=$this->controller();
        $this->assertSame(404,$controller->cancelOrder('LTEST')->getStatusCode());
        $this->assertSame([],$controller->calls);
        $this->assertSame(0,$controller->csrfChecks);
        $this->assertSame(405,$this->controller([],'get')->cancelOrder('LTEST')->getStatusCode());
        session()->set('isLogin',false);
        $this->assertSame(404,$this->controller()->cancelOrder('LTEST')->getStatusCode());
    }

    public function testManualTransferWithProofIsNotAutoExpiredOrCancelled(): void
    {
        $this->change(['status'=>'Menunggu Pembayaran Rekening','bukti_bayar'=>'proof'],['payment_type'=>'rekening']);
        $controller=$this->controller();
        $this->assertSame('Menunggu Pembayaran Rekening',$controller->reconcile($this->saved())['status']);
        $controller->cancelOrder('LTEST');
        $this->assertSame('Menunggu Pembayaran Rekening',$this->saved()['status']);
        $this->assertSame([],$controller->calls);
    }

    public function testManualUnpaidProductionExpiryRestoresReservationExactlyOnce(): void
    {
        $this->change(['status'=>'Menunggu Pembayaran Rekening'],['payment_type'=>'rekening','_app_payment_environment'=>'production','_app_inventory_reserved'=>true]);
        $controller=$this->controller();
        $this->assertSame('Kadaluarsa',$controller->reconcile($this->saved())['status']);
        $this->assertSame('10',$this->db->table('barang')->get()->getRowArray()['stok']);
        $controller->reconcile($this->saved());
        $this->assertSame('10',$this->db->table('barang')->get()->getRowArray()['stok']);
        $this->assertSame([],$controller->calls);
        $this->assertSame(false,$controller->notifications[0][2]);
    }

    public function testClientUsesCorrectEnvironmentAndNeverSendsProductionKeyToSandbox(): void
    {
        foreach ([true,false] as $sandbox) {
            $calls=[];
            $client=new MidtransOrderClient($sandbox,'unit-key',static function($method,$url,$options) use (&$calls){$calls[]=[$method,$url,$options];return ['status_code'=>'200','order_id'=>'L / TEST','transaction_status'=>'pending','gross_amount'=>'100000'];});
            $client->request('L / TEST','status');$client->request('L / TEST','cancel');
            $this->assertSame('GET',$calls[0][0]);$this->assertSame('POST',$calls[1][0]);
            $this->assertSame(($sandbox?'https://api.sandbox.midtrans.com':'https://api.midtrans.com').'/v2/L%20%2F%20TEST/status',$calls[0][1]);
            $this->assertTrue($calls[0][2]['verify']);
            $this->assertFalse($calls[0][2]['allow_redirects']);
        }
    }

    public function testPointsRefundStopsAfterReservedAmountInsteadOfCreditingNextActiveBucketAgain(): void
    {
        $points=[['nominal'=>0,'active'=>false,'kadaluarsa'=>'2027-01-01'],['nominal'=>2000,'active'=>true,'kadaluarsa'=>'2027-02-01'],['nominal'=>500,'active'=>false,'kadaluarsa'=>'2027-03-01']];
        $restored=\App\Services\Payments\ReservedPointsRelease::restore($points,10000);
        $this->assertSame(10000,$restored[0]['nominal']);
        $this->assertSame(2000,$restored[1]['nominal']);
        $this->assertFalse($restored[2]['active']);
        $this->assertSame('2027-01-01',$restored[0]['kadaluarsa']);
    }

    public function testSuccessfulStatusReadCanReturnExpiredBusinessCode407(): void
    {
        $body=$this->body('expire','407');
        $client=new MidtransOrderClient(true,'isolated-payment-key',static fn()=> $body);
        $this->assertSame('407',$client->request('LTEST','status')['status_code']);
        $this->assertSame('Kadaluarsa',\App\Services\Payments\PaymentSafety::notificationStatus($body));
        $this->assertTrue(\App\Services\Payments\PaymentSafety::validNotification($this->saved(),$body,'isolated-payment-key'));
    }

    public function testProofUploadAndExpirySerializeOnSamePendingOrder(): void
    {
        $this->change(['status'=>'Menunggu Pembayaran Rekening'],['payment_type'=>'rekening','expiry_time'=>'2099-01-01 10:00:00']);
        $image=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9l0AAAAASUVORK5CYII=');
        $proof=new \App\Services\Payments\ManualPaymentProof($this->db);
        $this->assertFalse($proof->save('LTEST','other@example.test',$image));
        $this->assertTrue($proof->save('LTEST','buyer@example.test',$image));
        $this->change([],['expiry_time'=>'2020-01-01 10:00:00']);
        $this->assertSame('Menunggu Pembayaran Rekening',$this->controller()->reconcile($this->saved())['status']);
        $this->assertFalse($proof->save('LTEST','buyer@example.test',$image));
        $this->change(['bukti_bayar'=>'']);
        $this->assertSame('Kadaluarsa',$this->controller()->reconcile($this->saved())['status']);
        $this->assertFalse($proof->save('LTEST','buyer@example.test',$image));
    }

    public function testRenamedProductStillRestoresByStoredIdAndCorrectVariant(): void
    {
        $this->change(['status'=>'Menunggu Pembayaran Rekening'],['payment_type'=>'rekening','_app_payment_environment'=>'production','_app_inventory_reserved'=>true]);
        $this->db->table('barang')->where('id','B1')->update(['nama'=>'Nama baru','varian'=>'["Putih","Cokelat"]','stok'=>'8,5']);
        $this->assertSame('Kadaluarsa',$this->controller()->reconcile($this->saved())['status']);
        $this->assertSame('10,5',$this->db->table('barang')->get()->getRowArray()['stok']);
    }

    public function testProductionRefundedPointsVoucherAndStockAreNotDoubledByReplay(): void
    {
        $this->db->table('pembeli')->insert(['email_user'=>'buyer@example.test','poin'=>'[{"nominal":0,"active":false,"kadaluarsa":"2027-01-01"},{"nominal":2000,"active":true,"kadaluarsa":"2027-02-01"}]']);
        $this->db->table('voucher_claimed')->insert(['id_voucher'=>7,'email_user'=>'buyer@example.test','active'=>0]);
        $this->change(['status'=>'Menunggu Pembayaran Rekening','idVoucher'=>7,'pakai_poin'=>10000],['payment_type'=>'rekening','_app_payment_environment'=>'production','_app_inventory_reserved'=>true]);
        $controller=$this->controller();
        $controller->reconcile($this->saved());$controller->reconcile($this->saved());
        $this->assertSame('Kadaluarsa',$this->saved()['status']);
        $this->assertSame(1,(int)$this->db->table('voucher_claimed')->get()->getRowArray()['active']);
        $points=json_decode($this->db->table('pembeli')->get()->getRowArray()['poin'],true);
        $this->assertSame(12000,array_sum(array_column($points,'nominal')));
        $this->assertSame('10',$this->db->table('barang')->get()->getRowArray()['stok']);
        $this->assertCount(1,$controller->notifications);
    }
}
