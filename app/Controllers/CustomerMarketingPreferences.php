<?php

namespace App\Controllers;

use App\Services\Notifications\CustomerMarketingPreferences as Preferences;
use Config\Database;

final class CustomerMarketingPreferences extends BaseController
{
    public function saveWeb()
    {
        if ((string)session('role') !== '0' || (string)session('active') !== '1') {
            return redirect()->to('/account')->with('msg', 'Pengaturan promosi hanya tersedia untuk akun pelanggan aktif.');
        }
        try {
            (new Preferences(Database::connect()))->update(
                (string)session('email'),
                $this->request->getPost('email_promotions') === '1',
                $this->request->getPost('push_promotions') === '1'
            );
            return redirect()->to('/account')->with('msg', 'Preferensi promosi berhasil disimpan.');
        } catch (\Throwable $error) {
            log_message('error', 'Customer marketing preference update failed: ' . get_class($error));
            return redirect()->to('/account')->with('msg', 'Preferensi belum tersimpan. Coba lagi nanti.');
        }
    }
}
