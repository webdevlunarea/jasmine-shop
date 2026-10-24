<?php

use CodeIgniter\Test\CIUnitTestCase;

final class CustomerVerificationExpiryTest extends CIUnitTestCase
{
    public function testExpiredVerificationPageDoesNotAutomaticallySendAnotherCode(): void
    {
        $controller = file_get_contents(APPPATH . 'Controllers/Pages.php');
        $start = strpos($controller, '    public function verify()');
        $end = strpos($controller, '    public function actionVerify()', $start);
        self::assertNotFalse($start);
        self::assertNotFalse($end);
        $verifyAction = substr($controller, $start, $end - $start);
        self::assertStringNotContainsString('$this->kirimOTP()', $verifyAction);

        $view = file_get_contents(APPPATH . 'Views/pages/verify.php');
        self::assertStringNotContainsString('window.location.reload', $view);
        self::assertStringContainsString('action="/kirimotp" method="post"', $view);
        self::assertStringContainsString('Kode sudah kedaluwarsa. Kirim ulang hanya jika Anda ingin menerima kode baru.', $view);
        self::assertStringContainsString('data-verification-resend', $view);
        self::assertStringContainsString('resendButton.disabled = secondsLeft > 0', $view);
        self::assertStringContainsString('class="verify-resend-card"', $view);
        self::assertStringContainsString('.verify-resend-button:disabled', file_get_contents(FCPATH . 'css/buyer-auth.css'));

        $registrationService = file_get_contents(APPPATH . 'Services/Auth/CustomerRegistrationService.php');
        self::assertStringContainsString("time() + 540", $registrationService);

        $android = file_get_contents(ROOTPATH . 'android/app/src/main/java/com/lunareafurniture/lunarea/MainActivity.kt');
        self::assertStringContainsString('enabled = !busy && email.contains(\'@\') && resendSeconds == 0', $android);
    }
}
