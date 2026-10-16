<?php

use App\Services\Integrations\LunaOrderSync;
use CodeIgniter\Test\CIUnitTestCase;

final class LunaOrderSyncTest extends CIUnitTestCase
{
    private function order(): array {
        return ['id_midtrans'=>'L00000227','status'=>'Proses','resi'=>'TEST227','email_cus'=>'buyer@example.test', 'nama_pen'=>'Pembeli',
            'hp_pen'=>'081234567890','alamat_pen'=>'Semarang','kurir'=>'JNE','note'=>'Catatan asli',
            'items'=>json_encode([['id'=>'PROD1','name'=>'Meja (Putih)','value'=>950000,'quantity'=>2]]),
            'data_mid'=>json_encode(['_app_payment_environment'=>'production','transaction_time'=>'2026-10-15 14:32:09',
                'settlement_time'=>'2026-10-15 14:33:12','gross_amount'=>'1920000.00','transaction_status'=>'settlement'])];
    }
    private function client(int $status, array $body) {
        return new class($status,$body) {
            public array $requests=[]; private int $status; private array $body;
            public function __construct(int $status,array $body) {$this->status=$status;$this->body=$body;}
            public function post($url,array $options) {
                $this->requests[]=[$url,$options];
                return new class($this->status,$this->body) {
                    private int $status;private array $body;
                    public function __construct(int $status,array $body){$this->status=$status;$this->body=$body;}
                    public function getStatusCode(){return $this->status;}
                    public function getBody(){return json_encode($this->body);}
                };
            }
        };
    }
    private function settings():array{return ['enabled'=>true,'url'=>'https://myluna.example.test/api/lunarea-web-order','token'=>'unit-token'];}

    public function testSnapshotItemsAmountAndOriginalTimeArePreserved():void {
        $order=$this->order();$original=$order;$payload=LunaOrderSync::payload($order,[]);
        $this->assertSame(json_decode($order['items'],true),$payload['items']);
        $this->assertSame(json_decode($order['data_mid'],true),$payload['data_mid']);
        $this->assertSame('2026-10-15T14:32:09+07:00',$payload['transaction_time']);
        $this->assertSame('2026-10-15 14:32:09',$payload['website_transaction_time']);
        $this->assertSame($original,$order);
        $this->assertSame($payload,LunaOrderSync::payload($order,[]));
    }
    /** @dataProvider unpaidStates */
    public function testNonProcessingOrdersAreRejected(string $status):void {
        $order=$this->order();$order['status']=$status;$this->expectException(DomainException::class);LunaOrderSync::payload($order,[]);
    }
    public static function unpaidStates():array{return [['Menunggu Pembayaran'],['Dibatalkan'],['Dikirim'],['Selesai']];}
    public function testSandboxNeverBuildsProductionPayload():void {
        $order=$this->order();$mid=json_decode($order['data_mid'],true);$mid['_app_payment_environment']='sandbox';$order['data_mid']=json_encode($mid);
        $this->expectException(DomainException::class);LunaOrderSync::payload($order,[]);
    }
    /** @dataProvider invalidTimes */
    public function testMissingOrInvalidTimeNeverUsesCurrentTime($time):void {
        $order=$this->order();$mid=json_decode($order['data_mid'],true);$mid['transaction_time']=$time;$order['data_mid']=json_encode($mid);
        $this->expectException(DomainException::class);LunaOrderSync::payload($order,[]);
    }
    public static function invalidTimes():array{return [[null],[''],['now'],['2026-02-30 12:00:00']];}
    public function testSuccessfulAcknowledgementChecksIdentityAndTls():void {
        $payload=LunaOrderSync::payload($this->order(),[]);$client=$this->client(200,['success'=>true,'data'=>['saleNumber'=>'L00000227','tracking_number'=>'TEST227']]);
        $this->assertTrue((new LunaOrderSync($this->settings(),$client))->send($payload)['success']);
        $options=$client->requests[0][1];$this->assertSame($payload,$options['json']);$this->assertTrue($options['verify']);
        $this->assertFalse($options['allow_redirects']);$this->assertSame('unit-token',$options['headers']['X-Luna-Webhook-Token']);
    }
    /** @dataProvider rejectedAcknowledgements */
    public function testWrongIdentitySkippedAndHttpErrorsNeverClaimSuccess(int $status,array $body):void {
        $this->assertFalse((new LunaOrderSync($this->settings(),$this->client($status,$body)))->send(LunaOrderSync::payload($this->order(),[]))['success']);
    }
    public static function rejectedAcknowledgements():array{return [[401,[]],[400,[]],[409,[]],[500,[]],[200,['success'=>true,'data'=>['saleNumber'=>'L00000227','tracking_number'=>'OLD']]], [200,['success'=>true,'data'=>['saleNumber'=>'OTHER']]],[200,['success'=>true,'data'=>['saleNumber'=>'L00000227','skipped'=>true]]]];}
    public function testDisabledAndMissingSettingsNeverMakeRequest():void {
        foreach ([['enabled'=>false],['token'=>''],['url'=>'http://myluna.example.test']] as $patch) {
            $client=$this->client(200,[]);$result=(new LunaOrderSync(array_merge($this->settings(),$patch),$client))->send(LunaOrderSync::payload($this->order(),[]));
            $this->assertFalse($result['success']);$this->assertSame([],$client->requests);
        }
    }
    public function testTimeoutReturnsUncertainOutcomeWithoutChangingOrder():void {
        $client=new class {public function post($url,$options){throw new RuntimeException('unit timeout');}};
        $order=$this->order();$result=(new LunaOrderSync($this->settings(),$client))->send(LunaOrderSync::payload($order,[]));
        $this->assertFalse($result['success']);$this->assertStringContainsString('mungkin sudah diterima',$result['message']);
        $this->assertSame('Proses',$order['status']);
    }
    public function testAdminEndpointUsesStoredOrderAndNeverRequestPayload():void {
        $saved=$this->order();$controller=new class($saved) extends \App\Controllers\Pages {
            public array $sent=[];
            public function __construct($order) {
                $this->request=new class {public function getPost($key){return $key === 'review_confirmed' ? '1' : null;}};
                $this->emailUjiCoba=[];$this->response=\Config\Services::response(null,false);
                $this->pemesananModel=new class($order){private array $order;public function __construct($order){$this->order=$order;}public function getPemesanan($id){return $this->order;}};
            }
            protected function guardOrderAdminRequest(){return null;} // Auth/CSRF tested separately by route/source checks.
            protected function performLunaOrderSync(array $order):array{$this->sent[]=$order;return ['success'=>true,'message'=>'unit accepted'];}
        };
        $response=$controller->syncPaidOrderToLuna('L00000227');
        $this->assertSame([$saved],$controller->sent);$this->assertStringEndsWith('/listcustomer',$response->getHeaderLine('Location'));
    }
    public function testAnonymousEndpointStopsBeforeOrderLookup():void {
        session()->remove(['isLogin','role']);
        $controller=new class extends \App\Controllers\Pages {
            public function __construct(){ $this->request=new class {public function getMethod(){return 'post';}};$this->response=\Config\Services::response(null,false); }
        };
        $this->assertSame(403,$controller->syncPaidOrderToLuna('L00000227')->getStatusCode());
    }
    public function testAdminRouteCsrfAndSnapshotGuardRemainPresent():void {
        $routes=file_get_contents(APPPATH.'Config/Routes.php');$source=file_get_contents(APPPATH.'Controllers/Pages.php');
        $this->assertStringContainsString("'/syncorder/(:segment)', 'Pages::syncPaidOrderToLuna/$"."1', ['filter' => 'harusAdmin']",$routes);
        $this->assertStringContainsString('Services::security()->verify($this->request)',$source);
        $source=substr($source,strpos($source,'private function syncOrderToLunaSistem'),strpos($source,'private function syncReturnToLunaSistem')-strpos($source,'private function syncOrderToLunaSistem'));
        $this->assertStringNotContainsString('barangModel',$source);$this->assertStringNotContainsString('->insert(',$source);
        $view=file_get_contents(APPPATH.'Views/pages/listCustomer.php');$this->assertStringContainsString('order-sync-form',$view);$this->assertStringContainsString('csrf_field()',$view);
    }
    public function testAllDeliveryMethodsCanBeReviewedBeforeAwbExists(): void
    {
        foreach (['legacy','internal_delivery'] as $method) {
            foreach (['','Menunggu pengiriman','kosong'] as $awb) {
                $order=$this->order();$order['_shipping_method_code']=$method;$order['resi']=$awb;
                $this->assertSame('', LunaOrderSync::payload($order,[])['tracking_number']);
            }
        }
    }

    public function testPaymentNotificationsDoNotImportBeforeAdminSubmission(): void
    {
        $source=file_get_contents(APPPATH.'Controllers/Pages.php');
        $start=strpos($source,'public function updateTransaction()');$end=strpos($source,'public function updateTransactionCoreAPI()', $start);
        $callback=substr($source,$start,$end-$start);
        $this->assertStringNotContainsString('$this->syncOrderToLunaSistem(', $callback);
        $this->assertStringNotContainsString('$this->performLunaOrderSync(', $callback);
        $start=strpos($source,'public function payOrderConfirm(');$end=strpos($source,'public function pdf(', $start);
        $this->assertStringNotContainsString('$this->syncOrderToLunaSistem(',substr($source,$start,$end-$start));
    }

}
