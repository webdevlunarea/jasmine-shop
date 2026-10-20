<?php

namespace App\Controllers\Api\V1;

use App\Models\PembeliModel;
use App\Models\UserModel;
use App\Services\Auth\CustomerApiTokens;
use App\Services\Auth\GoogleCustomerAuthenticator;
use App\Services\Auth\CustomerEmailChanges;
use App\Services\Auth\CustomerRegistrationService;
use App\Services\Auth\CustomerPasswordChange;
use App\Services\Auth\IndonesianMobileNumber;
use App\Services\Auth\PasswordResetService;
use App\Services\Auth\RecaptchaVerifier;

class AuthController extends ApiController
{
    protected function customerRegistrations(): CustomerRegistrationService { return new CustomerRegistrationService(); }
    protected function passwordChanges(): CustomerPasswordChange { return new CustomerPasswordChange(); }
    protected function passwordResets(): PasswordResetService { return new PasswordResetService(); }
    protected function captchaVerifier(): RecaptchaVerifier { return new RecaptchaVerifier(); }
    protected function customerEmailChanges(): CustomerEmailChanges { return new CustomerEmailChanges(); }

    protected function allowAuthAttempt(string $scope, string $identity = '', int $limit = 8, int $seconds = 900): bool
    {
        $throttler = service('throttler');
        $ip = hash('sha256', (string)$this->request->getIPAddress());
        if (!$throttler->check('android-auth-' . $scope . '-ip-' . $ip, $limit, $seconds)) return false;
        if ($identity !== '') {
            $key = hash('sha256', strtolower(trim($identity)));
            if (!$throttler->check('android-auth-' . $scope . '-identity-' . $key, max(3, (int)ceil($limit / 2)), $seconds)) return false;
        }
        return true;
    }

    public function register()
    {
        $input = $this->payload();
        $name = trim((string)($input['name'] ?? ''));
        $email = strtolower(trim((string)($input['email'] ?? '')));
        $phone = (string)($input['phone'] ?? '');
        if ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || strlen($email) > 254 || (new IndonesianMobileNumber())->normalize($phone) === null
            || !in_array($input['consent'] ?? null, [true, 1, '1'], true)) {
            return $this->failure('VALIDATION_ERROR', 'Nama, email, nomor ponsel, dan persetujuan kebijakan wajib valid.', 422);
        }
        if (!$this->allowAuthAttempt('register', $email, 6, 3600)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak percobaan pendaftaran. Coba lagi nanti.', 429);
        }
        if ($error = $this->captchaVerifier()->error($input['captcha_token'] ?? null, 'android', 'signup')) {
            return $this->failure('CAPTCHA_REQUIRED', $error, 422);
        }
        try {
            $result = $this->customerRegistrations()->register(
                $name, $email, (string)($input['password'] ?? ''),
                (string)($input['password_confirmation'] ?? ''), $phone
            );
        } catch (\DomainException $e) {
            return $this->failure('REGISTRATION_REJECTED', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API registration failed: ' . get_class($e));
            return $this->failure('REGISTRATION_UNAVAILABLE', 'Pendaftaran sementara terganggu. Coba lagi nanti.', 503);
        }
        return $this->success([
            'verification_required' => true,
            'verification_email_sent' => $result['email_sent'],
            'message' => $result['email_sent']
                ? 'Kode verifikasi dikirim ke email Anda. Masukkan kode sebelum 10 menit.'
                : 'Akun menunggu verifikasi. Jika email belum diterima, gunakan kirim ulang kode setelah jeda yang ditentukan.',
        ], [], 202);
    }

    public function verifyEmail()
    {
        $input = $this->payload();
        $email = strtolower(trim((string)($input['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            return $this->failure('VALIDATION_ERROR', 'Alamat email belum valid.', 422);
        }
        if (!$this->allowAuthAttempt('verify-email', $email, 10, 900)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak percobaan verifikasi. Coba lagi nanti.', 429);
        }
        try {
            $this->customerRegistrations()->verify($email, $input['code'] ?? null);
        } catch (\DomainException $e) {
            return $this->failure('INVALID_OR_EXPIRED_CODE', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API email verification failed: ' . get_class($e));
            return $this->failure('VERIFICATION_UNAVAILABLE', 'Verifikasi sementara terganggu. Coba lagi nanti.', 503);
        }
        return $this->success(['verified' => true, 'login_required' => true, 'message' => 'Email berhasil diverifikasi. Silakan masuk.']);
    }

    public function resendVerification()
    {
        $input = $this->payload();
        $email = strtolower(trim((string)($input['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            return $this->failure('VALIDATION_ERROR', 'Alamat email belum valid.', 422);
        }
        if (!$this->allowAuthAttempt('resend-verification', $email, 6, 3600)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak permintaan kode. Coba lagi nanti.', 429);
        }
        if ($error = $this->captchaVerifier()->error($input['captcha_token'] ?? null, 'android', 'verification_resend')) {
            return $this->failure('CAPTCHA_REQUIRED', $error, 422);
        }
        try { $this->customerRegistrations()->resend($email); }
        catch (\Throwable $e) {
            log_message('error', 'Customer API verification resend failed: ' . get_class($e));
            return $this->failure('VERIFICATION_UNAVAILABLE', 'Verifikasi sementara terganggu. Coba lagi nanti.', 503);
        }
        return $this->success(['accepted' => true, 'message' => 'Jika akun belum terverifikasi dan jeda permintaan terpenuhi, kode akan dikirim ke alamat tersebut.'], [], 202);
    }

    public function login()
    {
        $input = $this->payload();
        $identifier = trim((string)($input['identifier'] ?? ''));
        $password = (string)($input['password'] ?? '');
        if ($identifier === '' || $password === '' || strlen($identifier) > 254 || strlen($password) > 4096) {
            return $this->failure('VALIDATION_ERROR', 'Email/username dan sandi wajib diisi.', 422, ['identifier' => 'Wajib diisi.']);
        }
        if (!$this->allowAuthAttempt('login', $identifier, 8, 300)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak percobaan masuk. Coba lagi beberapa menit.', 429);
        }
        if ($error = $this->captchaVerifier()->error($input['captcha_token'] ?? null, 'android', 'login')) {
            return $this->failure('CAPTCHA_REQUIRED', $error, 422);
        }
        $model = new UserModel();
        $isEmail = strpos($identifier, '@') !== false;
        $user = $isEmail ? $model->getUser(strtolower($identifier)) : $model->getUserByUsername($identifier);
        if (!$user || (string)($user['role'] ?? '') !== '0' || !password_verify($password, (string)($user['sandi'] ?? ''))) {
            return $this->failure('INVALID_CREDENTIALS', 'Email/username atau sandi tidak sesuai.', 401);
        }
        if ((string)($user['active'] ?? '') !== '1') {
            return $this->failure('EMAIL_NOT_VERIFIED', 'Verifikasi email diperlukan sebelum masuk ke aplikasi.', 403);
        }
        try {
            $tokens = (new CustomerApiTokens())->issue((string)$user['email'], (string)($input['device_name'] ?? 'Android'));
        } catch (\Throwable $e) {
            log_message('error', 'Customer API token issue failed: ' . get_class($e));
            return $this->failure('AUTH_UNAVAILABLE', 'Layanan akun sementara terganggu. Coba lagi nanti.', 503);
        }
        $profile = (new PembeliModel())->getPembeli((string)$user['email']) ?: [];
        return $this->success([
            'tokens' => $tokens,
            'customer' => [
                'email' => (string)$user['email'],
                'name' => (string)($profile['nama'] ?? ''),
                'phone' => (string)($profile['nohp'] ?? ''),
            ],
        ]);
    }

    public function googleLogin()
    {
        $input = $this->payload();
        $idToken = $input['id_token'] ?? null;
        if (!is_string($idToken) || $idToken === '' || strlen($idToken) > 8192) {
            return $this->failure('VALIDATION_ERROR', 'Token Google tidak valid.', 422);
        }
        if (!$this->allowAuthAttempt('google-login', hash('sha256', $idToken), 8, 300)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak percobaan masuk dengan Google. Coba lagi nanti.', 429);
        }
        $stage = 'google_account_resolution';
        try {
            $customer = (new GoogleCustomerAuthenticator())->authenticate($idToken);
            $stage = 'customer_session_issue';
            $tokens = (new CustomerApiTokens())->issue(
                $customer['email'],
                is_string($input['device_name'] ?? null) ? $input['device_name'] : 'Android'
            );
        } catch (\DomainException $e) {
            return $this->failure('GOOGLE_LOGIN_REJECTED', $e->getMessage(), 401);
        } catch (\Throwable $e) {
            // Keep credentials, tokens, and account data out of logs. The stage
            // identifies whether failure occurred resolving the account or storing its session.
            log_message('error', sprintf(
                'Customer API Google login failed at %s: %s (code %s).',
                $stage,
                get_class($e),
                (string) $e->getCode()
            ));
            return $this->failure('GOOGLE_LOGIN_UNAVAILABLE', 'Masuk dengan Google sementara terganggu. Coba lagi nanti.', 503);
        }
        return $this->success(['tokens' => $tokens, 'customer' => $customer]);
    }

    public function changePassword()
    {
        $email = $this->authEmail();
        if (!$this->allowAuthAttempt('change-password', $email, 5, 900)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak percobaan. Coba lagi nanti.', 429);
        }
        $input = $this->payload();
        try {
            $this->passwordChanges()->change($email, (string)($input['current_password'] ?? ''),
                (string)($input['password'] ?? ''), (string)($input['password_confirmation'] ?? ''));
        } catch (\DomainException $e) {
            return $this->failure('PASSWORD_CHANGE_REJECTED', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API password change failed: ' . get_class($e));
            return $this->failure('PASSWORD_CHANGE_UNAVAILABLE', 'Sandi belum dapat diubah. Coba lagi nanti.', 503);
        }
        return $this->success(['changed' => true, 'login_required' => true,
            'message' => 'Sandi berhasil diubah. Silakan masuk kembali di perangkat Anda.']);
    }

    public function requestEmailChange()
    {
        $email = $this->authEmail();
        if (!$this->allowAuthAttempt('email-change-request', $email, 5, 3600)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak permintaan perubahan email. Coba lagi nanti.', 429);
        }
        $input = $this->payload();
        $password = $input['current_password'] ?? null;
        $newEmail = $input['new_email'] ?? null;
        if (!is_string($password) || $password === '' || strlen($password) > 4096
            || !is_string($newEmail) || trim($newEmail) === '' || strlen($newEmail) > 254) {
            return $this->failure('VALIDATION_ERROR', 'Sandi saat ini dan email baru wajib diisi dengan benar.', 422);
        }
        try {
            $this->customerEmailChanges()->request($email, $password, $newEmail);
        } catch (\DomainException $e) {
            return $this->failure('EMAIL_CHANGE_REJECTED', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API email change request failed: ' . get_class($e));
            return $this->failure('EMAIL_CHANGE_UNAVAILABLE', 'Permintaan perubahan email sementara tidak dapat diproses.', 503);
        }
        return $this->success([
            'verification_required' => true, 'expires_in' => 600,
            'message' => 'Kode verifikasi dikirim ke email baru. Konfirmasi sebelum 10 menit.',
        ], [], 202);
    }

    public function confirmEmailChange()
    {
        $email = $this->authEmail();
        if (!$this->allowAuthAttempt('email-change-confirm', $email, 8, 900)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak percobaan konfirmasi perubahan email. Coba lagi nanti.', 429);
        }
        $input = $this->payload();
        $password = $input['current_password'] ?? null;
        $code = $input['verification_code'] ?? null;
        if (!is_string($password) || $password === '' || strlen($password) > 4096
            || !is_string($code) || !preg_match('/^\d{6}$/', $code)) {
            return $this->failure('VALIDATION_ERROR', 'Sandi saat ini dan kode verifikasi 6 digit wajib diisi.', 422);
        }
        try {
            $newEmail = $this->customerEmailChanges()->confirm($email, $password, $code);
        } catch (\DomainException $e) {
            return $this->failure('EMAIL_CHANGE_REJECTED', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API email change confirmation failed: ' . get_class($e));
            return $this->failure('EMAIL_CHANGE_UNAVAILABLE', 'Email belum berhasil diperbarui. Coba lagi nanti.', 503);
        }
        return $this->success([
            'changed' => true, 'email' => $newEmail, 'login_required' => true,
            'message' => 'Email berhasil diperbarui. Silakan masuk kembali menggunakan email baru.',
        ]);
    }

    public function refresh()
    {
        $refresh = (string)($this->payload()['refresh_token'] ?? '');
        if ($refresh === '') return $this->failure('VALIDATION_ERROR', 'Token pembaruan wajib diisi.', 422);
        try {
            $tokens = (new CustomerApiTokens())->rotate($refresh);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API token rotation failed: ' . get_class($e));
            return $this->failure('AUTH_UNAVAILABLE', 'Layanan akun sementara terganggu. Coba lagi nanti.', 503);
        }
        return $tokens ? $this->success(['tokens' => $tokens]) : $this->failure('INVALID_REFRESH_TOKEN', 'Sesi aplikasi berakhir. Silakan masuk kembali.', 401);
    }

    /** Public response is deliberately identical for existing and unknown accounts. */
    public function requestPasswordReset()
    {
        $input = $this->payload();
        $identity = trim((string)($input['identity'] ?? ''));
        if ($identity === '' || strlen($identity) > 254) {
            return $this->failure('VALIDATION_ERROR', 'Masukkan email atau username akun.', 422, ['identity' => 'Format tidak valid.']);
        }
        if ($error = $this->captchaVerifier()->error($input['captcha_token'] ?? null, 'android', 'password_reset_request')) {
            return $this->failure('CAPTCHA_REQUIRED', $error, 422);
        }
        try {
            $service = $this->passwordResets();
            $email = $service->resolveCustomerEmail($identity, $this->request->getIPAddress());
            if ($email !== null) $service->request($email, $this->request->getIPAddress());
        } catch (\Throwable $e) {
            log_message('error', 'Customer API password-reset request failed: ' . get_class($e));
            return $this->failure('RECOVERY_UNAVAILABLE', 'Pemulihan akun sementara terganggu. Coba lagi nanti.', 503);
        }
        return $this->success([
            'accepted' => true,
            'message' => 'Jika data cocok dengan akun pelanggan dan batas permintaan terpenuhi, kode akan dikirim ke email terdaftar.',
        ], [], 202);
    }

    /** OTP is the proof of email ownership; PasswordResetService enforces expiry and attempt limits. */
    public function completePasswordReset()
    {
        $input = $this->payload();
        $identity = trim((string)($input['identity'] ?? ''));
        if ($identity === '' || strlen($identity) > 254) {
            return $this->failure('VALIDATION_ERROR', 'Masukkan email atau username akun.', 422, ['identity' => 'Format tidak valid.']);
        }
        // Validate public password fields before resolving the identifier to
        // avoid exposing whether an account exists through validation errors.
        if ($passwordError = PasswordResetService::passwordError(
            $input['password'] ?? null,
            $input['password_confirmation'] ?? null
        )) {
            return $this->failure('VALIDATION_ERROR', $passwordError, 422);
        }
        try {
            $service = $this->passwordResets();
            $email = $service->resolveCustomerEmail($identity, $this->request->getIPAddress());
            if ($email === null) throw new \DomainException(PasswordResetService::INVALID_CODE);
            $service->reset(
                $email,
                $input['code'] ?? null,
                $input['password'] ?? null,
                $input['password_confirmation'] ?? null,
                $this->request->getIPAddress()
            );
        } catch (\DomainException $e) {
            return $this->failure('INVALID_OR_EXPIRED_CODE', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API password-reset completion failed: ' . get_class($e));
            return $this->failure('RECOVERY_UNAVAILABLE', 'Pemulihan akun sementara terganggu. Coba lagi nanti.', 503);
        }
        return $this->success(['reset' => true, 'message' => 'Sandi berhasil diperbarui. Silakan masuk kembali.']);
    }

    public function logout()
    {
        $token = preg_replace('/^Bearer\s+/i', '', trim($this->request->getHeaderLine('Authorization')));
        try {
            (new CustomerApiTokens())->revokeFamilyForAccess($token);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API logout failed: ' . get_class($e));
            return $this->failure('AUTH_UNAVAILABLE', 'Sesi belum dapat ditutup. Coba lagi.', 503);
        }
        return $this->success(['logged_out' => true]);
    }

    public function me()
    {
        $email = $this->authEmail();
        $user = (new UserModel())->getUser($email);
        $profile = (new PembeliModel())->getPembeli($email);
        if (!$user || !$profile) return $this->failure('CUSTOMER_NOT_FOUND', 'Data akun tidak tersedia.', 404);
        $encodedEmail = rtrim(strtr(base64_encode($email), '+/', '-_'), '=');
        $photoUrl = site_url('imguser/' . $encodedEmail);
        $photoChangeAvailableAt = null;
        if (preg_match('/(?:\\?|&)v=([a-f0-9]{24})(?:&|$)/i', (string)($profile['foto'] ?? ''), $match)) {
            $photoUrl .= '?v=' . strtolower($match[1]);
        }
        if (preg_match('/(?:\\?|&)at=(\\d{10})(?:&|$)/', (string)($profile['foto'] ?? ''), $match)) {
            $photoChangeAvailableAt = gmdate('c', (int)$match[1] + 604800);
        }
        return $this->success(['customer' => [
            'email' => (string)$user['email'],
            'name' => (string)($profile['nama'] ?? ''),
            'phone' => (string)($profile['nohp'] ?? ''),
            'tier' => (string)($profile['tier'] ?? ''),
            'photo_url' => $photoUrl,
            'photo_change_available_at' => $photoChangeAvailableAt,
        ]]);
    }
}
