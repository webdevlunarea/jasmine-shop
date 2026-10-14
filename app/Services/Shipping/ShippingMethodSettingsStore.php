<?php

namespace App\Services\Shipping;

class ShippingMethodSettingsStore
{
    public static function merge(int $id, array $patch, ?int $active = null, ?\CodeIgniter\Database\BaseConnection $connection = null): void
    {
        $db = $connection ?? \Config\Database::connect();
        $db->transBegin();
        try {
            $query = $db->table('shipping_methods')->where('id', $id)->getCompiledSelect();
            $row = $db->query($query . ($db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$row) throw new \DomainException('Metode pengiriman tidak ditemukan.');
            $settings = json_decode((string)($row['settings'] ?: '{}'), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($settings)) throw new \DomainException('Konfigurasi pengiriman perlu diperiksa.');
            $data = ['settings' => json_encode(array_replace($settings, $patch), JSON_THROW_ON_ERROR), 'updated_at' => date('Y-m-d H:i:s')];
            if ($active !== null) $data['is_active'] = $active ? 1 : 0;
            $db->table('shipping_methods')->where('id', $id)->update($data);
            if (!$db->transStatus()) throw new \RuntimeException('Shipping settings update failed');
            $db->transCommit();
        } catch (\Throwable $e) { $db->transRollback(); throw $e; }
    }
}
