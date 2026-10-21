<?php

namespace App\Controllers;

use App\Services\Referrals\AndroidReferralProgram;

final class AndroidReferralAdmin extends BaseController
{
    public function index()
    {
        try {
            $program = new AndroidReferralProgram();
            return view('pages/androidReferralAdmin', [
                'title' => 'Referral Android', 'settings' => $program->settings(),
                'msg' => session()->getFlashdata('msg'), 'stats' => $this->stats(), 'schemaReady' => true,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Android referral admin read failed: ' . get_class($e));
            return view('pages/androidReferralAdmin', [
                'title' => 'Referral Android', 'settings' => AndroidReferralProgram::defaults(),
                'msg' => session()->getFlashdata('msg'), 'stats' => [], 'schemaReady' => false,
            ]);
        }
    }

    public function save()
    {
        try {
            (new AndroidReferralProgram())->saveSettings($this->request->getPost());
            return redirect()->to('/androidreferraladmin')->withCookies()->with('msg', 'Pengaturan referral Android berhasil disimpan.');
        } catch (\DomainException $e) {
            $message = $e->getMessage();
        } catch (\Throwable $e) {
            log_message('error', 'Android referral admin save failed: ' . get_class($e));
            $message = 'Pengaturan belum tersimpan. Pastikan tabel referral sudah dibuat dan periksa log server.';
        }
        return redirect()->to('/androidreferraladmin')->withCookies()->with('msg', $message);
    }

    private function stats(): array
    {
        $db = \Config\Database::connect();
        return [
            'invited' => (int)$db->table('android_referral_attributions')->countAllResults(),
            'rewarded' => (int)$db->table('android_referral_rewards')->where('state', 'awarded')->countAllResults(),
            'points' => (int)$db->table('android_referral_rewards')->selectSum('points')->where('state', 'awarded')->get()->getRow('points'),
        ];
    }
}
