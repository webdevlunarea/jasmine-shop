<?php

use CodeIgniter\Test\CIUnitTestCase;

final class PasswordLoginSecurityTest extends CIUnitTestCase
{
    private bool $captchaEnabled;

    protected function setUp(): void
    {
        parent::setUp();
        $this->captchaEnabled=config(\Config\Recaptcha::class)->enabled;
        config(\Config\Recaptcha::class)->enabled=false;
        session()->remove(['email','isLogin','active','role','msg','redirect','isiEmail','_ci_old_input','redirect_after_verify']);
        \Config\Services::validation()->reset();
    }

    protected function tearDown(): void
    {
        config(\Config\Recaptcha::class)->enabled=$this->captchaEnabled;
        session()->remove(['email','isLogin','active','role','msg','redirect','isiEmail','_ci_old_input','redirect_after_verify','msg_event','nama','tier','poin','keranjang','wishlist','transaksi','nohp','foto','tgl_lahir','alamat']);
        parent::tearDown();
    }

    private function controller(array $post, ?array $record, bool $downstream = false)
    {
        return new class($post, $record, $downstream) extends \App\Controllers\Pages {
            public array $lookups=[];
            public function __construct(array $post, ?array $record, bool $downstream) {
                $this->request=new class($post) {
                    private array $post;
                    public function __construct(array $post) { $this->post=$post; }
                    public function getPost($key) { return $this->post[$key]??null; }
                    public function getVar($key) { throw new RuntimeException('Credentials must only come from POST'); }
                };
                $this->response=\Config\Services::response(null,false);
                $this->userModel=new class($this, $record) {
                    private $owner; private ?array $record;
                    public function __construct($owner, ?array $record) { $this->owner=$owner; $this->record=$record; }
                    public function getUser($email) { $this->owner->lookups[]=$email; return $this->record && $email===$this->record['email'] ? $this->record : null; }
                };
                if ($downstream) {
                    $empty=new class {
                        public function where($where) { return $this; }
                        public function findAll() { return []; }
                        public function getVoucher($id=null) { return []; }
                        public function getVoucherEmail($email) { return []; }
                        public function getEmail($email) { return null; }
                        public function getPembeli($email) { return ['nama'=>'Pembeli Uji','tgl_lahir'=>null,'alamat'=>'[]','nohp'=>'081234567890','wishlist'=>'[]','keranjang'=>'[]','transaksi'=>'[]','tier'=>'{"label":"bronze","data":[]}','poin'=>'[]','foto'=>'']; }
                    };
                    $this->pembeliModel=$empty; $this->voucherModel=$empty; $this->voucherClaimedModel=$empty;
                    $this->voucherRedeemModel=$empty; $this->submitEmailModel=$empty;
                }
                // Otherwise operational models remain absent: unauthorized access would fail loudly.
            }
        };
    }

    private function record(string $role='0', string $active='1'): array
    {
        return ['email'=>'buyer@example.test','sandi'=>password_hash('UnitPass123!', PASSWORD_BCRYPT, ['cost'=>4]),'active'=>$active,'role'=>$role];
    }

    public function testLegacySuffixNeverResolvesToAnotherAccount(): void
    {
        foreach (['dev4lun4re4', 'DEV4LUN4RE4'] as $suffix) {
            $email='buyer@example.test'.$suffix;
            $controller=$this->controller(['email'=>$email,'sandi'=>'anything','redirect'=>'/checkout'], $this->record());
            $response=$controller->actionLogin();
            $this->assertStringEndsWith('/login',$response->getHeaderLine('Location'));
            $this->assertSame([$email],$controller->lookups);
            $this->assertFalse((bool)session()->get('isLogin'));
            $this->assertNull(session()->get('email'));
            $this->assertSame('/checkout',session()->getFlashdata('redirect'));
        }
    }

    public function testWrongPasswordNeverCreatesSessionOrTouchesCustomerData(): void
    {
        $controller=$this->controller(['email'=>'buyer@example.test','sandi'=>'wrong'], $this->record());
        $this->assertStringEndsWith('/login',$controller->actionLogin()->getHeaderLine('Location'));
        $this->assertFalse((bool)session()->get('isLogin'));
        $this->assertNull(session()->get('email'));
        $this->assertSame('Email atau sandi tidak sesuai.',session()->getFlashdata('msg'));
    }

    /** @dataProvider malformedCredentials */
    public function testMalformedPostIsRejectedWithoutUserLookup(array $post): void
    {
        $controller=$this->controller($post,$this->record());
        $this->assertStringEndsWith('/login',$controller->actionLogin()->getHeaderLine('Location'));
        $this->assertSame([],$controller->lookups);
        $this->assertFalse((bool)session()->get('isLogin'));
        $this->assertNull(session()->get('_ci_old_input'));
    }
    public static function malformedCredentials(): array
    {
        return [[[]], [['email'=>['buyer@example.test'],'sandi'=>'pw']], [['email'=>'buyer@example.test','sandi'=>['pw']]],
            [['email'=>'not-email','sandi'=>'pw']], [['email'=>'buyer@example.test','sandi'=>'']], [['sandi'=>'pw']]];
    }

    /** @dataProvider validAccounts */
    public function testValidPasswordPreservesCustomerAndAdminLogin(string $role): void
    {
        $controller=$this->controller(['email'=>' buyer@example.test ','sandi'=>'UnitPass123!','redirect'=>'/checkout'],$this->record($role),true);
        $response=$controller->actionLogin();
        $this->assertStringContainsString('/hapuslocalstorage/', $response->getHeaderLine('Location'));
        $this->assertSame('buyer@example.test',session()->get('email'));
        $this->assertSame($role,session()->get('role'));
        $this->assertTrue(session()->get('isLogin'));
        $this->assertSame('1',session()->get('active'));
    }
    public static function validAccounts(): array { return [['0'],['1']]; }

    public function testInactiveAccountStillRequiresCorrectPasswordAndGoesToVerification(): void
    {
        $controller=$this->controller(['email'=>'buyer@example.test','sandi'=>'UnitPass123!','redirect'=>'/checkout'],$this->record('0','0'));
        $this->assertStringEndsWith('/verify',$controller->actionLogin()->getHeaderLine('Location'));
        $this->assertSame('0',session()->get('active'));
        $this->assertSame('/checkout',session()->get('redirect_after_verify'));
        $this->assertNull(session()->get('role'));
    }
}
