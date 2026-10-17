<?php

use App\Controllers\PasswordReset;
use App\Services\Auth\PasswordResetService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class PasswordResetControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    protected function setUp(): void { parent::setUp(); session()->destroy(); }
    protected function tearDown(): void { session()->destroy(); parent::tearDown(); }

    private function controller(array $post)
    {
        return new class($post) extends PasswordReset {
            public array $calls=[]; public bool $invalid=false;
            public function __construct(array $post) {
                $this->request=new class($post) {
                    private array $post;
                    public function __construct(array $post) { $this->post=$post; }
                    public function getPost($key) { return $this->post[$key]??null; }
                    public function getMethod() { return 'POST'; }
                    public function getIPAddress() { return '192.0.2.1'; }
                    public function getVar($key) { throw new \RuntimeException('POST only'); }
                };
                $this->response=\Config\Services::response(null,false);
            }
            protected function captchaError(): ?string { return null; }
            protected function resets(): PasswordResetService {
                return new class($this) extends PasswordResetService {
                    private $owner;
                    public function __construct($owner) { $this->owner=$owner; }
                    public function request(string $email,string $ip): void { $this->owner->calls[]=['request',$email,$ip]; }
                    public function reset(string $email,$code,$password,$confirmation,string $ip): void {
                        $this->owner->calls[]=['reset',$email,$code,$password,$confirmation,$ip];
                        if($this->owner->invalid) throw new \DomainException(PasswordResetService::INVALID_CODE);
                    }
                };
            }
        };
    }

    public function testRequestUsesGenericReplyAndDoesNotStoreOtpOrPassword(): void
    {
        $c=$this->controller(['email'=>' Buyer@Example.test ','sandi'=>'never-store']);
        $this->assertStringEndsWith('/reset-sandi',$c->requestCode()->getHeaderLine('Location'));
        $this->assertSame([['request','buyer@example.test','192.0.2.1']],$c->calls);
        $this->assertSame('buyer@example.test',session('password_reset_email'));
        $this->assertStringContainsString('Jika email terdaftar',session()->getFlashdata('reset_message'));
        $this->assertStringNotContainsString('never-store',json_encode(session()->get()));
        $this->assertNull(session()->get('_ci_old_input'));
    }

    public function testInvalidEmailNeverCallsStorageOrMailer(): void
    {
        foreach([[],['email'=>[]],['email'=>'not-email']] as $post) {
            $c=$this->controller($post);
            $this->assertStringEndsWith('/lupa-sandi',$c->requestCode()->getHeaderLine('Location'));
            $this->assertSame([],$c->calls);
        }
    }

    public function testCompletionUsesSessionTargetNotPostedVictimAndSignsOut(): void
    {
        session()->set(['password_reset_email'=>'buyer@example.test','isLogin'=>true,'email'=>'buyer@example.test','facebook_oauth'=>['state'=>'old']]);
        $c=$this->controller(['email'=>'victim@example.test','otp'=>'012345','sandi'=>'NewPassword123','konfirmasi_sandi'=>'NewPassword123']);
        $response=$c->complete();
        $this->assertStringEndsWith('/login',$response->getHeaderLine('Location'));
        $this->assertSame('buyer@example.test',$c->calls[0][1]);
        $this->assertSame('012345',$c->calls[0][2]);
        $this->assertFalse((bool)session('isLogin'));
        $this->assertNull(session('password_reset_email'));
        $this->assertNull(session('facebook_oauth'));
        $this->assertNull(session('_ci_old_input'));
        $this->assertSame('no-store, private',$response->getHeaderLine('Cache-Control'));
    }

    public function testInvalidCodeKeepsFlowButDoesNotFlashSubmittedSecrets(): void
    {
        session()->set('password_reset_email','buyer@example.test');
        $c=$this->controller(['otp'=>'012345','sandi'=>'SensitivePassword123','konfirmasi_sandi'=>'SensitivePassword123']); $c->invalid=true;
        $this->assertStringEndsWith('/reset-sandi',$c->complete()->getHeaderLine('Location'));
        $this->assertStringNotContainsString('SensitivePassword123',json_encode(session()->get()));
        $this->assertStringNotContainsString('012345',json_encode(session()->get()));
    }

    public function testNoPendingTargetNeverReachesResetStorage(): void
    {
        $c=$this->controller(['email'=>'victim@example.test','otp'=>'012345']);
        $this->assertStringEndsWith('/lupa-sandi',$c->complete()->getHeaderLine('Location'));
        $this->assertSame([],$c->calls);
    }

    /** @dataProvider csrfRoutes */
    public function testPostEndpointsRequireCsrf(string $path): void
    {
        $this->expectException(\CodeIgniter\Security\Exceptions\SecurityException::class);
        $this->post($path,['email'=>'buyer@example.test','otp'=>'012345','sandi'=>'Password123']);
    }
    public static function csrfRoutes(): array { return [['/lupa-sandi'],['/reset-sandi']]; }
}
