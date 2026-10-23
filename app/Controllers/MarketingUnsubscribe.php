<?php

namespace App\Controllers;

use App\Services\Notifications\CustomerMarketingPreferences;
use Config\Database;

final class MarketingUnsubscribe extends BaseController
{
    public function show(string $token)
    {
        $valid = preg_match('/^[A-Za-z0-9_-]{40,50}$/', $token) === 1;
        if ($valid && Database::connect()->tableExists('customer_marketing_preferences')) {
            $valid = Database::connect()->table('customer_marketing_preferences')
                ->where('email_unsubscribe_token_hash', hash('sha256', $token))
                ->where('email_promotions', 1)->countAllResults() === 1;
        } else $valid = false;

        return view('pages/marketingUnsubscribe', [
            'title' => 'Preferensi email promosi',
            'token' => $token,
            'valid' => $valid,
            'result' => session()->getFlashdata('marketing_unsubscribe_result'),
        ]);
    }

    public function confirm(string $token)
    {
        try {
            $updated = (new CustomerMarketingPreferences(Database::connect()))->unsubscribeByToken($token);
            $message = $updated
                ? 'Email promosi telah dinonaktifkan. Email transaksi dan keamanan akun tetap dapat dikirim.'
                : 'Tautan ini sudah digunakan atau tidak lagi berlaku.';
        } catch (\Throwable $error) {
            log_message('error', 'Marketing unsubscribe failed: ' . get_class($error));
            $message = 'Preferensi belum dapat diperbarui. Coba lagi melalui akun Lunarea.';
        }
        return redirect()->to('/promosi/berhenti/' . rawurlencode($token))->with('marketing_unsubscribe_result', $message);
    }
}
