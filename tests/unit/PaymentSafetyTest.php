<?php

use App\Services\Payments\PaymentSafety;
use CodeIgniter\Test\CIUnitTestCase;

final class PaymentSafetyTest extends CIUnitTestCase
{
    private function order(string $status = 'Menunggu Pembayaran'): array
    {
        return ['id_midtrans'=>'LTEST', 'status'=>$status, 'email_cus'=>'buyer@example.test', 'email'=>'buyer@example.test',
            'data_mid'=>json_encode(['gross_amount'=>125000,'payment_type'=>'bank_transfer','transaction_id'=>'TX-TEST','_app_payment_environment'=>'sandbox'])];
    }
    private function body(string $key = 'unit-key'): array
    {
        $body = ['order_id'=>'LTEST','status_code'=>'200','gross_amount'=>'125000.00','transaction_status'=>'settlement','transaction_id'=>'TX-TEST'];
        $body['signature_key'] = hash('sha512', $body['order_id'] . $body['status_code'] . $body['gross_amount'] . $key);
        return $body;
    }
    public function testSignatureAndAmountAreVerifiedBeforeApplyingAnything(): void
    {
        $order = $this->order(); $body = $this->body();
        $this->assertTrue(PaymentSafety::validNotification($order,$body,'unit-key'));
        $this->assertFalse(PaymentSafety::validNotification($order,$body,'wrong-key'));
        foreach (['order_id'=>'OTHER','gross_amount'=>'1.00','signature_key'=>'fake','transaction_id'=>'OTHER'] as $field=>$value) {
            $tampered = $body; $tampered[$field] = $value;
            $this->assertFalse(PaymentSafety::validNotification($order,$tampered,'unit-key'),$field);
        }
        $this->assertFalse(PaymentSafety::validNotification($order,$body,''));
        $this->assertFalse(PaymentSafety::validNotification($order,$body,'DefaultValue'));
        $mid = json_decode($order['data_mid'],true); $mid['payment_type']='rekening'; $order['data_mid']=json_encode($mid);
        $this->assertFalse(PaymentSafety::validNotification($order,$body,'unit-key'));
    }
    public function testValidSignatureForAnotherAmountStillFailsOrderReconciliation(): void
    {
        $body=$this->body(); $body['gross_amount']='1.00';
        $body['signature_key']=hash('sha512','LTEST'.'200'.'1.00'.'unit-key');
        $this->assertFalse(PaymentSafety::validNotification($this->order(),$body,'unit-key'));
    }
    public function testPersistedEnvironmentWinsOverLaterAllowlistChanges(): void
    {
        $order=$this->order();
        $this->assertSame('sandbox',PaymentSafety::environment($order,[],'email_cus'));
        $mid=json_decode($order['data_mid'],true);$mid['_app_payment_environment']='production';$order['data_mid']=json_encode($mid);
        $this->assertSame('production',PaymentSafety::environment($order,['buyer@example.test'],'email_cus'));
        $order['data_mid']='{}';$order['email_cus']=' BUYER@EXAMPLE.TEST ';
        $this->assertSame('sandbox',PaymentSafety::environment($order,['buyer@example.test'],'email_cus'));
    }
    public function testNativeTestingAccountIsRoutedToSandbox(): void
    {
        $this->assertTrue(PaymentSafety::isTestEmail('UUUA5021@gmail.com', PaymentSafety::testAccountEmails()));
        $this->assertFalse(PaymentSafety::isTestEmail('uuua5032@gmail.com', PaymentSafety::testAccountEmails()));
        $order = ['email_cus' => 'uuua5021@gmail.com', 'data_mid' => '{}'];
        $this->assertSame('sandbox', PaymentSafety::environment($order, PaymentSafety::testAccountEmails(), 'email_cus'));
        $this->assertTrue(PaymentSafety::nativeCheckoutEnabledFor('uuua5021@gmail.com'));
        $this->assertFalse(PaymentSafety::nativeCheckoutEnabledFor('regular-customer@example.test'));
    }
    /** @dataProvider transitions */
    public function testStateTransitionPolicy(string $old, ?string $new, bool $allowed): void
    {
        $this->assertSame($allowed,PaymentSafety::canTransition($old,$new));
    }
    public static function transitions(): array
    {
        return [
            ['Menunggu Pembayaran','Proses',true],['Menunggu Pembayaran','Kadaluarsa',true],
            ['Menunggu Pembayaran','Menunggu Pembayaran',false],['Proses','Proses',false],
            ['Dikirim','Proses',false],['Selesai','Proses',false],['Proses','Kadaluarsa',false],
            ['Kadaluarsa','Proses',false],['Dibatalkan','Proses',false],['Selesai','Refund',true],
            ['Proses','Partial Refund',true],['Partial Refund','Refund',true],['Selesai',null,false],
            ['Menunggu Pembayaran Rekening','Proses',false],
        ];
    }
    public function testFraudChallengeAndUnknownStatusesNeverMarkPaid(): void
    {
        $this->assertNull(PaymentSafety::notificationStatus(['transaction_status'=>'capture','fraud_status'=>'challenge']));
        $this->assertNull(PaymentSafety::notificationStatus(['transaction_status'=>'capture']));
        $this->assertSame('Proses',PaymentSafety::notificationStatus(['transaction_status'=>'capture','fraud_status'=>'accept']));
        $this->assertSame('Proses',PaymentSafety::notificationStatus(['transaction_status'=>'settlement']));
        $this->assertNull(PaymentSafety::notificationStatus(['transaction_status'=>'unknown']));
    }
    public function testDuplicateClaimAndRollbackAreIsolatedInMemory(): void
    {
        $db=\Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
        try {
            $db->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT UNIQUE, status TEXT, data_mid TEXT)');
            $order=$this->order();unset($order['email'],$order['email_cus']);$db->table('pemesanan')->insert($order);
            $db->transBegin();
            $locked=PaymentSafety::lockedOrder($db,'LTEST');$this->assertSame('Menunggu Pembayaran',$locked['status']);
            $this->assertTrue(PaymentSafety::claim($db,'LTEST','Menunggu Pembayaran','Proses',[]));
            $db->transCommit();
            $this->assertFalse(PaymentSafety::claim($db,'LTEST','Menunggu Pembayaran','Proses',[]));
            $db->transBegin();$this->assertTrue(PaymentSafety::claim($db,'LTEST','Proses','Refund',[]));$db->transRollback();
            $this->assertSame('Proses',$db->table('pemesanan')->get()->getRowArray()['status']);
        } finally { $db->close(); }
    }
    public function testImmediatelyPaidOrderGetsEffectsOnlyOnce(): void
    {
        $order=$this->order('Proses');
        $this->assertFalse(PaymentSafety::needsFirstPaidEffects($order,'Proses'),'Legacy paid orders must not gain duplicate rewards');
        $mid=json_decode($order['data_mid'],true);$mid['_app_payment_effects_applied']=false;$order['data_mid']=json_encode($mid);
        $this->assertTrue(PaymentSafety::needsFirstPaidEffects($order,'Proses'));
        $mid['_app_payment_effects_applied']=true;$order['data_mid']=json_encode($mid);
        $this->assertFalse(PaymentSafety::needsFirstPaidEffects($order,'Proses'));
    }
}
