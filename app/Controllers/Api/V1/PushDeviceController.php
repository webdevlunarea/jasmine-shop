<?php

namespace App\Controllers\Api\V1;

use Config\Database;
use CodeIgniter\Database\BaseConnection;

class PushDeviceController extends ApiController
{
    protected function connection(): BaseConnection { return Database::connect(); }

    protected function encryptDeviceToken(string $token): string
    {
        return service('encrypter')->encrypt($token);
    }

    public function register()
    {
        $input = $this->payload();
        $token = trim((string)($input['device_token'] ?? ''));
        $platform = strtolower(trim((string)($input['platform'] ?? '')));
        $deviceName = trim((string)($input['device_name'] ?? ''));
        if (strlen($token) < 40 || strlen($token) > 4096 || $platform !== 'android') {
            return $this->failure('VALIDATION_ERROR', 'Token perangkat atau platform tidak valid.', 422);
        }
        $db = $this->connection();
        if (!$db->tableExists('customer_push_devices')) return $this->failure('PUSH_NOT_READY', 'Pendaftaran notifikasi belum disiapkan.', 503);
        $hash = hash('sha256', $token);
        try { $encryptedToken = $this->encryptDeviceToken($token); }
        catch (\Throwable $e) { return $this->failure('PUSH_NOT_READY', 'Penyimpanan perangkat belum dikonfigurasi.', 503); }
        $now = gmdate('Y-m-d H:i:s');
        $existing = $db->table('customer_push_devices')->where('device_token_hash', $hash)->get()->getRowArray();
        $data = [
            'email_user' => $this->authEmail(), 'device_token_hash' => $hash, 'device_token' => $encryptedToken,
            'platform' => 'android', 'device_name' => $deviceName === '' ? null : mb_substr($deviceName, 0, 100),
            'is_active' => 1, 'last_seen_at' => $now, 'updated_at' => $now,
        ];
        try {
            if ($existing) {
                $saved = $db->table('customer_push_devices')->where('id', $existing['id'])->update($data);
                if (!$saved) throw new \RuntimeException('Device update failed.');
            } else {
                // The unique token fingerprint is the concurrency guard when two refreshes
                // register the same installation at nearly the same time.
                $data['created_at'] = $now;
                $inserted = $db->table('customer_push_devices')->ignore(true)->insert($data);
                // INSERT IGNORE may report a duplicate-key race as false even though
                // the competing request created the row. Verify persisted state below.
                unset($data['created_at']);
                $saved = $db->table('customer_push_devices')->where('device_token_hash', $hash)->update($data);
                if (!$saved && !$inserted) throw new \RuntimeException('Device owner update failed.');
            }
            $persisted = $db->table('customer_push_devices')
                ->select('email_user, is_active')
                ->where('device_token_hash', $hash)
                ->get()->getRowArray();
            if (!$persisted || $persisted['email_user'] !== $this->authEmail() || (int)$persisted['is_active'] !== 1) {
                throw new \RuntimeException('Device registration was not persisted.');
            }
        } catch (\Throwable $e) {
            log_message('error', 'Customer API push device registration failed: ' . get_class($e));
            return $this->failure('PUSH_REGISTRATION_FAILED', 'Perangkat belum terdaftar untuk notifikasi. Coba lagi.', 503);
        }
        return $this->success(['registered' => true]);
    }

    public function unregister()
    {
        $token = trim((string)($this->payload()['device_token'] ?? ''));
        if (strlen($token) < 40 || strlen($token) > 4096) return $this->failure('VALIDATION_ERROR', 'Token perangkat belum valid.', 422);
        $db = $this->connection();
        if (!$db->tableExists('customer_push_devices')) return $this->success(['unregistered' => true]);
        try {
            $saved = $db->table('customer_push_devices')->where([
                'email_user' => $this->authEmail(), 'device_token_hash' => hash('sha256', $token),
            ])->update(['is_active' => 0, 'updated_at' => gmdate('Y-m-d H:i:s')]);
            if (!$saved) throw new \RuntimeException('Device unregister failed.');
        } catch (\Throwable $e) {
            log_message('error', 'Customer API push device removal failed: ' . get_class($e));
            return $this->failure('PUSH_UNREGISTER_FAILED', 'Perangkat belum dapat dilepas dari notifikasi. Coba lagi.', 503);
        }
        return $this->success(['unregistered' => true]);
    }
}
