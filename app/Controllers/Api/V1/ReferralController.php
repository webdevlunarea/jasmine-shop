<?php

namespace App\Controllers\Api\V1;

use App\Services\Referrals\AndroidReferralProgram;

final class ReferralController extends ApiController
{
    public function index()
    {
        try {
            return $this->success((new AndroidReferralProgram())->overview($this->authEmail()));
        } catch (\Throwable $e) {
            log_message('error', 'Customer API referral overview failed: ' . get_class($e));
            return $this->failure('REFERRAL_UNAVAILABLE', 'Informasi Ajak Teman belum dapat dimuat.', 503);
        }
    }

    public function attribute()
    {
        $code = $this->payload()['code'] ?? null;
        if (!is_string($code) || strlen($code) > 12) return $this->failure('VALIDATION_ERROR', 'Kode referral tidak valid.', 422);
        try {
            (new AndroidReferralProgram())->attribute($this->authEmail(), $code);
            return $this->success(['attributed' => true, 'message' => 'Kode referral berhasil ditautkan ke akun ini.']);
        } catch (\DomainException $e) {
            return $this->failure('REFERRAL_REJECTED', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API referral attribution failed: ' . get_class($e));
            return $this->failure('REFERRAL_UNAVAILABLE', 'Kode referral belum dapat diproses. Coba lagi nanti.', 503);
        }
    }
}
