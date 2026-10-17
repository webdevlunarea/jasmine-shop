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
            public array $calls=[]; public bool $invalid=false; public bool $requestSuccess=true;
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
                    public function request(string $email,string $ip): bool { $this->owner->calls[]=['request',$email,$ip]; return $this->owner->requestSuccess; }
                    public function resolveCustomerEmail(string $identity,string $ip): ?string {
                        $this->owner->calls[]=['resolve',$identity,$ip];
                        return in_array(strtolower($identity), ['buyer@example.test','buyername'], true) ? 'buyer@example.test' : null;
                    }
                    public function reset(string $email,$code,$password,$confirmation,string $ip): void {
                        $this->owner->calls[]=['reset',$email,$code,$password,$confirmation,$ip];
                        if($this->owner->invalid) throw new \DomainException(PasswordResetService::INVALID_CODE);
                    }
                };
            }
        };
    }

    public function testIdentifierMustBeSubmittedBeforeEmailIsConfirmedAndCodeIsSent(): void
    {
        $c=$this->controller(['identity'=>'buyername','sandi'=>'never-store']);
        $this->assertStringEndsWith('/lupa-sandi',$c->requestCode()->getHeaderLine('Location'));
        $this->assertSame([['resolve','buyername','192.0.2.1']],$c->calls);
        $this->assertSame('buyer@example.test',session('password_reset_pending_email'));
        $this->assertNull(session('password_reset_email'));
        $this->assertStringNotContainsString('never-store',json_encode(session()->get()));
        $this->assertNull(session()->get('_ci_old_input'));
    }

    public function testOnlyExplicitEmailConfirmationSendsResetCode(): void
    {
        session()->set(['password_reset_pending_email'=>'buyer@example.test','password_reset_pending_at'=>time()]);
        $c=$this->controller(['confirm_email'=>'1']);
        $this->assertStringEndsWith('/reset-sandi',$c->requestCode()->getHeaderLine('Location'));
        $this->assertSame([['request','buyer@example.test','192.0.2.1']],$c->calls);
        $this->assertSame('buyer@example.test',session('password_reset_email'));
        $this->assertNull(session('password_reset_pending_email'));
    }

    public function testFailedDeliveryKeepsConfirmationStepAndShowsActionableFeedback(): void
    {
        session()->set(['password_reset_pending_email'=>'buyer@example.test','password_reset_pending_at'=>time()]);
        $c=$this->controller(['confirm_email'=>'1']);
        $c->requestSuccess=false;
        $this->assertStringEndsWith('/lupa-sandi',$c->requestCode()->getHeaderLine('Location'));
        $this->assertSame('buyer@example.test',session('password_reset_pending_email'));
        $this->assertNull(session('password_reset_email'));
        $this->assertNotEmpty(session()->getFlashdata('reset_error'));
    }

    /** @dataProvider renderedSteps */
    public function testRealLayoutRetainsTitleAcrossCaptchaPartial(string $step, string $title): void
    {
            session()->remove(array_keys(session()->get()));
            if ($step === 'confirm') session()->set(['password_reset_pending_email'=>'buyer@example.test','password_reset_pending_at'=>time()]);
            if ($step === 'reset') session()->set('password_reset_email', 'buyer@example.test');
        $config = config(\Config\Recaptcha::class);
        $enabled = $config->enabled;
        $config->enabled = false;
        try {
            $controller = $this->controller([]);
            $response = $step === 'reset' ? $controller->resetForm() : $controller->requestForm();
            $html = $response->getBody();
            $this->assertStringContainsString('<title>' . $title . ' | Lunarea Furniture</title>', $html);
            $this->assertStringContainsString('buyer-auth.css', $html);
            $this->assertStringContainsString('buyer-auth-body', $html);
            $this->assertStringContainsString('id="password-reset-form"', $html);
            $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
        } finally {
            $config->enabled = $enabled;
        }
    }

    public static function renderedSteps(): array
    {
        return [['request', 'Lupa sandi'], ['reset', 'Reset sandi']];
    }

    public function testInvalidEmailNeverCallsStorageOrMailer(): void
    {
        foreach([[],['identity'=>[]],['identity'=>'not-email@']] as $post) {
            $c=$this->controller($post);
            $this->assertStringEndsWith('/lupa-sandi',$c->requestCode()->getHeaderLine('Location'));
            $this->assertSame([],$c->calls);
        }
    }

    public function testUnknownIdentityDoesNotReachCodeMailer(): void
    {
        $c=$this->controller(['identity'=>'unknownname']);
        $this->assertStringEndsWith('/lupa-sandi',$c->requestCode()->getHeaderLine('Location'));
        $this->assertSame([['resolve','unknownname','192.0.2.1']],$c->calls);
        $this->assertNull(session('password_reset_pending_email'));
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
