<?php

namespace App\Controllers;

use App\Services\Notifications\FirebaseMessaging;
use Config\Database;

/** Sends a real, bounded test push to a registered customer device. */
class PushNotificationAdmin extends BaseController
{
    public function index()
    {
        return view('pages/pushNotificationAdmin', [
            'title' => 'Tes notifikasi',
            'msg' => session()->getFlashdata('msg'),
            'email' => session()->getFlashdata('push_test_email') ?? '',
        ]);
    }

    public function sendTest()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            return $this->back('Masukkan email akun pelanggan yang valid.', $email);
        }

        $now = time();
        $lastSent = (int) session('push_test_last_sent');
        $remaining = 30 - ($now - $lastSent);
        if ($lastSent > 0 && $remaining > 0) {
            return $this->back("Tunggu {$remaining} detik sebelum mengirim tes berikutnya.", $email);
        }

        try {
            $db = Database::connect();
            if (!$db->tableExists('customer_push_devices')) {
                return $this->back('Data perangkat notifikasi belum tersedia.', $email);
            }

            $customer = $db->table('user')->select('email')
                ->where(['email' => $email, 'role' => '0', 'active' => '1'])
                ->get()->getRowArray();
            if (!$customer) return $this->back('Akun pelanggan aktif tidak ditemukan.', $email);

            $devices = $db->table('customer_push_devices')
                ->select('id, device_token')
                ->where(['email_user' => $email, 'platform' => 'android', 'is_active' => 1])
                ->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();
            if (!$devices) return $this->back('Belum ada perangkat Android aktif untuk akun ini. Masuk di aplikasi dan aktifkan notifikasi terlebih dahulu.', $email);

            session()->set('push_test_last_sent', $now);
            $sender = new FirebaseMessaging();
            $sent = 0;
            $failed = 0;

            foreach ($devices as $device) {
                try {
                    $stored = (string) $device['device_token'];
                    if (str_starts_with($stored, 'b64:')) {
                        $decoded = base64_decode(substr($stored, 4), true);
                        if ($decoded === false) throw new \UnexpectedValueException('Invalid token envelope.');
                        $stored = $decoded;
                    }
                    $token = service('encrypter')->decrypt($stored);
                    $result = $sender->send($token, [
                        'title' => 'Tes notifikasi Lunarea',
                        'body' => 'Notifikasi perangkat berhasil diterima.',
                    ], ['type' => 'admin_test']);
                    if (!empty($result['ok'])) {
                        $sent++;
                    } else {
                        $failed++;
                        log_message('warning', 'Admin push test rejected by FCM. HTTP status: {status}.', [
                            'status' => (int) ($result['status'] ?? 0),
                        ]);
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    log_message('error', 'Admin push test failed: ' . get_class($e));
                }
            }

            if ($sent > 0) {
                return $this->back("Notifikasi tes berhasil dikirim ke {$sent} perangkat." . ($failed ? " {$failed} perangkat lain gagal." : ''), $email);
            }
            return $this->back('Notifikasi tes belum berhasil dikirim. Periksa konfigurasi Firebase dan log server.', $email);
        } catch (\Throwable $e) {
            log_message('error', 'Admin push test request failed: ' . get_class($e));
            return $this->back('Tes notifikasi belum dapat diproses. Periksa log server dan coba lagi nanti.', $email);
        }
    }

    private function back(string $message, string $email)
    {
        return redirect()->to('/notificationtestadmin')->withCookies()
            ->with('msg', $message)->with('push_test_email', $email);
    }
}
