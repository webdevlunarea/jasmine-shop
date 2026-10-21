<?php

namespace App\Controllers;

use App\Services\Referrals\AndroidReferralProgram;

final class AndroidReferralLink extends BaseController
{
    public function show(string $code)
    {
        try {
            if (!(new AndroidReferralProgram())->publicCode($code)) return $this->response->setStatusCode(404)->setBody('Kode ajak teman tidak tersedia.');
            return view('pages/androidReferralLink', ['code' => strtoupper($code), 'title' => 'Ajak Teman ke Lunarea']);
        } catch (\Throwable $e) {
            log_message('error', 'Android referral link resolution failed: ' . get_class($e));
            return $this->response->setStatusCode(503)->setBody('Tautan ajak teman sedang tidak tersedia.');
        }
    }
}
