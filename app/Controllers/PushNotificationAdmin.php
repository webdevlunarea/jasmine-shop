<?php

namespace App\Controllers;

use App\Services\Notifications\FirebaseMessaging;
use App\Services\Notifications\CustomerPushBroadcasts;
use Config\Database;

/** Sends a real, bounded test push to a registered customer device. */
class PushNotificationAdmin extends BaseController
{
    public function index()
    {
        $recipientCount = 0;
        try {
            $recipientCount = (new CustomerPushBroadcasts(Database::connect()))->activeAndroidRecipientCount();
        } catch (\Throwable $e) {
            log_message('error', 'Push broadcast audience count failed: ' . get_class($e));
        }

        return view('pages/pushNotificationAdmin', [
            'title' => 'Notifikasi pelanggan',
            'msg' => session()->getFlashdata('msg'),
            'email' => session()->getFlashdata('push_test_email') ?? '',
            'broadcastMsg' => session()->getFlashdata('push_broadcast_msg'),
            'broadcastRecipientCount' => $recipientCount,
        ]);
    }

    /** Queue a broadcast for opted-in Android installations; delivery remains async via notifications:dispatch. */
    public function sendBroadcast()
    {
        $title = trim((string) $this->request->getPost('title'));
        $body = trim((string) $this->request->getPost('body'));
        $kind = trim((string) $this->request->getPost('kind'));
        if (!in_array($kind, ['voucher', 'flash_sale', 'event', 'general'], true) || $title === '' || $body === '' || mb_strlen($title) > 100 || mb_strlen($body) > 240) {
            return $this->backBroadcast('Periksa kategori dan isi pesan. Judul maksimal 100 karakter, pesan maksimal 240 karakter.');
        }

        $image = $this->request->getFile('image');
        $imageSize = trim((string) $this->request->getPost('image_size'));
        $allowedImageSizes = [
            '800x400' => [800, 400],
            '1024x512' => [1024, 512],
        ];
        if ($imageSize === '') $imageSize = '800x400';
        if (!isset($allowedImageSizes[$imageSize])) {
            return $this->backBroadcast('Pilih salah satu ukuran gambar notifikasi yang tersedia.');
        }
        $imageUrl = null;
        $storedImagePath = null;
        if ($image && $image->getError() !== UPLOAD_ERR_NO_FILE) {
            // Keep each public promo asset small to limit disk, transfer, and device-memory costs.
            $maxBytes = 512 * 1024;
            $mime = strtolower((string) $image->getMimeType());
            $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $imageInfo = $image->isValid() ? @getimagesize($image->getTempName()) : false;
            $imageTypes = [IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png', IMAGETYPE_WEBP => 'image/webp'];
            $size = (int) $image->getSize();
            if (!$image->isValid() || $image->hasMoved() || $size < 1 || $size >= $maxBytes
                || !isset($extensions[$mime]) || !$imageInfo || ($imageTypes[$imageInfo[2] ?? 0] ?? null) !== $mime
                || (int) ($imageInfo[0] ?? 0) !== $allowedImageSizes[$imageSize][0]
                || (int) ($imageInfo[1] ?? 0) !== $allowedImageSizes[$imageSize][1]) {
                return $this->backBroadcast("Gambar harus JPG, PNG, atau WebP, kurang dari 512 KB, dan berdimensi {$imageSize} sesuai ukuran yang dipilih.");
            }

            $directory = FCPATH . 'uploads/push-broadcasts';
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                return $this->backBroadcast('Folder penyimpanan gambar notifikasi belum tersedia.');
            }
            try {
                $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                $image->move($directory, $filename);
                $storedImagePath = $directory . DIRECTORY_SEPARATOR . $filename;
                $imageUrl = rtrim(base_url(), '/') . '/uploads/push-broadcasts/' . rawurlencode($filename);
                $imageUrlParts = parse_url($imageUrl);
                if (!is_array($imageUrlParts) || strtolower((string) ($imageUrlParts['scheme'] ?? '')) !== 'https') {
                    @unlink($storedImagePath);
                    return $this->backBroadcast('Alamat situs harus menggunakan HTTPS untuk gambar notifikasi.');
                }
            } catch (\Throwable $e) {
                log_message('error', 'Admin push broadcast image upload failed: ' . get_class($e));
                return $this->backBroadcast('Gambar notifikasi belum berhasil disimpan.');
            }
        }

        $remaining = 60 - (time() - (int) session('push_broadcast_last_sent'));
        if ((int) session('push_broadcast_last_sent') > 0 && $remaining > 0) {
            if ($storedImagePath) @unlink($storedImagePath);
            return $this->backBroadcast("Tunggu {$remaining} detik sebelum membuat broadcast berikutnya.");
        }

        try {
            $queued = (new CustomerPushBroadcasts(Database::connect()))->queue($kind, $title, $body, $imageUrl);
            if ($queued < 1) {
                if ($storedImagePath) @unlink($storedImagePath);
                return $this->backBroadcast('Belum ada perangkat Android aktif dengan akun penerima valid.');
            }
            session()->set('push_broadcast_last_sent', time());

            return $this->backBroadcast($queued . ' penerima masuk antrean. Pengiriman diproses bertahap oleh layanan notifikasi.');
        } catch (\Throwable $e) {
            if ($storedImagePath) @unlink($storedImagePath);
            log_message('error', 'Admin push broadcast enqueue failed: ' . get_class($e));
            return $this->backBroadcast('Broadcast belum berhasil dijadwalkan. Periksa kesiapan tabel dan log server.');
        }
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

    private function backBroadcast(string $message)
    {
        return redirect()->to('/notificationtestadmin')->withCookies()
            ->with('push_broadcast_msg', $message);
    }
}
