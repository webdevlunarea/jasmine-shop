<?php

namespace App\Commands;

use App\Services\Notifications\FirebaseMessaging;
use App\Services\Notifications\FirebaseMessageSender;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class DispatchCustomerNotifications extends BaseCommand
{
    protected $group = 'Notifications';
    protected $name = 'notifications:dispatch';
    protected $description = 'Kirim antrean notifikasi order pelanggan melalui Firebase Cloud Messaging.';
    protected $options = ['--limit' => 'Jumlah notifikasi diproses (1–100, default 20).'];

    protected function connection(): BaseConnection { return Database::connect(); }
    protected function sender(): FirebaseMessageSender { return new FirebaseMessaging(); }
    protected function decryptDeviceToken(string $ciphertext): string
    {
        // New rows use a base64 envelope so binary ciphertext survives utf8mb4 TEXT.
        // Rows written by earlier versions remain readable as raw ciphertext.
        if (str_starts_with($ciphertext, 'b64:')) {
            $decoded = base64_decode(substr($ciphertext, 4), true);
            if ($decoded === false) throw new \UnexpectedValueException('Invalid encrypted device token encoding.');
            $ciphertext = $decoded;
        }
        return service('encrypter')->decrypt($ciphertext);
    }

    public function run(array $params)
    {
        $db = $this->connection();
        if (!$db->tableExists('customer_push_notifications') || !$db->tableExists('customer_push_devices')) {
            CLI::error('Tabel antrean notifikasi belum tersedia.');
            return EXIT_ERROR;
        }
        $limit = max(1, min(100, (int)(CLI::getOption('limit') ?: 20)));
        $now = gmdate('Y-m-d H:i:s');
        // Recover jobs interrupted by process/server termination after a short lease.
        $db->table('customer_push_notifications')->where('status', 'processing')
            ->where('updated_at <', gmdate('Y-m-d H:i:s', time() - 900))
            ->update(['status' => 'pending', 'updated_at' => $now]);
        $rows = $db->table('customer_push_notifications')->where('status', 'pending')->where('next_attempt_at <=', $now)->orderBy('id', 'ASC')->limit($limit)->get()->getResultArray();
        $sender = $this->sender();
        $sent = 0; $retried = 0; $empty = 0;
        foreach ($rows as $row) {
            $claimed = $db->table('customer_push_notifications')->where(['id' => $row['id'], 'status' => 'pending'])->update(['status' => 'processing', 'updated_at' => $now]);
            if (!$claimed || $db->affectedRows() !== 1) continue;
            $devices = $db->table('customer_push_devices')->where(['email_user' => $row['email_user'], 'is_active' => 1])->get()->getResultArray();
            if (!$devices) {
                // Preserve the message until a device registers. PushDeviceController wakes
                // these rows when the customer later enables notifications on a device.
                $db->table('customer_push_notifications')->where('id', $row['id'])->update([
                    'status' => 'awaiting_device', 'last_error' => 'AWAITING_DEVICE', 'updated_at' => $now,
                ]);
                $empty++;
                continue;
            }
            $data = json_decode((string)$row['payload'], true) ?: [];
            $hasDevice = false; $allOk = true; $invalid = [];
            try {
                foreach ($devices as $device) {
                    $hasDevice = true;
                    try { $deviceToken = $this->decryptDeviceToken((string)$device['device_token']); }
                    catch (\Throwable $e) { $invalid[] = $device['id']; $allOk = false; continue; }
                    $result = $sender->send((string)$deviceToken, ['title' => $row['title'], 'body' => $row['body']], $data + ['type' => $row['notification_type']]);
                    if (!$result['ok']) {
                        $allOk = false;
                        if ($result['error_code'] === 'UNREGISTERED') $invalid[] = $device['id'];
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'Customer push delivery failed: ' . get_class($e));
                $allOk = false;
            }
            if ($invalid) $db->table('customer_push_devices')->whereIn('id', $invalid)->update(['is_active' => 0, 'updated_at' => $now]);
            if (!$hasDevice || $allOk) {
                $db->table('customer_push_notifications')->where('id', $row['id'])->update(['status' => 'sent', 'sent_at' => $now, 'last_error' => null, 'updated_at' => $now]);
                $sent++;
                continue;
            }
            $attempts = (int)$row['attempts'] + 1;
            $failed = $attempts >= 8;
            $delay = min(21600, 60 * (2 ** min($attempts, 8)));
            $db->table('customer_push_notifications')->where('id', $row['id'])->update([
                'status' => $failed ? 'failed' : 'pending', 'attempts' => $attempts,
                'next_attempt_at' => gmdate('Y-m-d H:i:s', time() + $delay), 'last_error' => 'FCM_DELIVERY_FAILED', 'updated_at' => $now,
            ]);
            $retried++;
        }
        CLI::write("Diproses: " . count($rows) . "; terkirim: {$sent}; ditunda/gagal: {$retried}; tanpa perangkat aktif: {$empty}.");
    }
}
