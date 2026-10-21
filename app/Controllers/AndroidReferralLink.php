<?php

namespace App\Controllers;

use App\Services\Referrals\AndroidReferralProgram;

final class AndroidReferralLink extends BaseController
{
    public function show(string $code)
    {
        try {
            $program = new AndroidReferralProgram();
            if (!$program->publicCode($code)) return $this->response->setStatusCode(404)->setBody('Tautan afiliasi tidak tersedia.');
            $code = strtoupper($code);
            session()->set('affiliate_referral_code', $code);
            $slug = strtolower(trim((string)$this->request->getGet('product')));
            if ($slug !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $slug)) {
                $product = (new \App\Models\BarangModel())->where(['path' => $slug, 'active' => '1'])->first();
                if ($product) return redirect()->to('/product/' . rawurlencode($slug));
            }
            return view('pages/androidReferralLink', [
                'code' => $code, 'title' => 'Afiliasi Lunarea',
                'productName' => '',
                'commissionPercent' => (float)$program->settings()['commission_percent'],
                'minimumSubtotal' => (int)$program->settings()['minimum_subtotal'],
                'browseUrl' => base_url('/all'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Android referral link resolution failed: ' . get_class($e));
            return $this->response->setStatusCode(503)->setBody('Tautan ajak teman sedang tidak tersedia.');
        }
    }
}
