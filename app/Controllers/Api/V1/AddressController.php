<?php

namespace App\Controllers\Api\V1;

use App\Models\CustomerAddressModel;
use App\Services\CustomerAddressBook;
use Config\Database;

class AddressController extends ApiController
{
    public function index()
    {
        $model = new CustomerAddressModel();
        if (!Database::connect()->tableExists('customer_addresses')) return $this->failure('ADDRESS_BOOK_UNAVAILABLE', 'Buku alamat belum disiapkan.', 503);
        $rows = $model->forCustomer($this->authEmail());
        return $this->success(['addresses' => array_map(fn($row) => $this->format($row), $rows)]);
    }

    public function save()
    {
        $email = $this->authEmail();
        $input = $this->payload();
        if (!Database::connect()->tableExists('customer_addresses')) return $this->failure('ADDRESS_BOOK_UNAVAILABLE', 'Buku alamat belum disiapkan.', 503);
        $id = max(0, (int)($input['id'] ?? 0));
        $model = new CustomerAddressModel();

        $label = trim((string)($input['label'] ?? ''));
        $name = trim((string)($input['recipient_name'] ?? ''));
        $phone = trim((string)($input['recipient_phone'] ?? ''));
        $detail = trim((string)($input['address_detail'] ?? ''));
        $provinceId = (int)($input['province_id'] ?? 0);
        $cityId = (int)($input['city_id'] ?? 0);
        $districtId = (int)($input['district_id'] ?? 0);
        $villageId = (int)($input['village_id'] ?? 0);
        if ($label === '' || mb_strlen($label) > 60 || $name === '' || mb_strlen($name) > 120
            || !preg_match('/^[+0-9 ()-]{8,25}$/', $phone) || $detail === '' || mb_strlen($detail) > 1000
            || min($provinceId, $cityId, $districtId, $villageId) < 1) {
            return $this->failure('VALIDATION_ERROR', 'Lengkapi penerima dan alamat dengan benar.', 422);
        }
        $db = Database::connect();
        $province = $db->table('provinsi')->where('id', $provinceId)->get()->getRowArray();
        $city = $db->table('kabupaten')->where(['id' => $cityId, 'provinsi_id' => $provinceId])->get()->getRowArray();
        $district = $db->table('kecamatan')->where(['id' => $districtId, 'kabupaten_id' => $cityId, 'provinsi_id' => $provinceId])->get()->getRowArray();
        $village = $db->table('kelurahan')->where(['id' => $villageId, 'kecamatan_id' => $districtId, 'kabupaten_id' => $cityId, 'provinsi_id' => $provinceId])->get()->getRowArray();
        if (!$province || !$city || !$district || !$village) return $this->failure('INVALID_ADDRESS_REGION', 'Kombinasi wilayah alamat tidak valid. Pilih ulang wilayah.', 422);

        $address = [
            'prov_id' => (string)$province['id'], 'prov' => (string)$province['label'],
            'kab_id' => (string)$city['id'], 'kab' => (string)$city['label'],
            'kec_id' => (string)$district['id'], 'kec' => (string)$district['label'],
            'desa' => (string)$village['label'], 'kodepos' => (string)$village['kodepos'], 'add' => $detail,
            'alamat' => $detail . ', ' . $village['label'] . ', ' . $district['label'] . ', ' . $city['label'] . ', ' . $province['label'] . ' ' . $village['kodepos'],
        ];
        if (!$db->transBegin()) return $this->failure('ADDRESS_SAVE_FAILED', 'Alamat belum tersimpan. Coba lagi.', 500);
        try {
            $lockBuilder = $db->table('customer_addresses')->where('email_user', $email)->orderBy('id', 'ASC');
            $lockSql = $lockBuilder->getCompiledSelect();
            if (in_array($db->DBDriver, ['MySQLi', 'Postgre'], true)) $lockSql .= ' FOR UPDATE';
            $customerAddresses = $db->query($lockSql)->getResultArray();
            $current = null;
            foreach ($customerAddresses as $savedAddress) {
                if ((int)$savedAddress['id'] === $id && $id > 0) $current = $savedAddress;
            }
            if ($id && !$current) {
                $db->transRollback();
                return $this->failure('ADDRESS_NOT_FOUND', 'Alamat tidak ditemukan.', 404);
            }
            if (!$id && count($customerAddresses) >= 20) {
                $db->transRollback();
                return $this->failure('ADDRESS_LIMIT_REACHED', 'Maksimal 20 alamat tersimpan.', 422);
            }
            // Only the first saved address becomes default automatically.
            // Adding another address without opting in must preserve the existing default.
            $makeDefault = $this->shouldMakeDefault(
                !empty($input['is_default']),
                $id === 0,
                count($customerAddresses) > 0
            );
            if ($makeDefault) $model->where('email_user', $email)->set('is_default', 0)->update();
            $data = [
                'email_user' => $email, 'label' => $label, 'recipient_name' => $name,
                'recipient_phone' => $phone, 'address_data' => json_encode($address, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'is_default' => $makeDefault ? 1 : (int)($current['is_default'] ?? 0),
            ];
            if ($id) {
                if (!$model->update($id, $data)) throw new \RuntimeException('Address update failed.');
                $savedId = $id;
            } else {
                $savedId = (int)$model->insert($data);
            }
            if (!$savedId || $db->transStatus() === false) throw new \RuntimeException('Address save failed.');
            if (!$db->transCommit()) throw new \RuntimeException('Address transaction commit failed.');
        } catch (\Throwable $e) {
            if ((int)$db->transDepth > 0) $db->transRollback();
            log_message('error', 'Customer API address save failed: ' . get_class($e));
            return $this->failure('ADDRESS_SAVE_FAILED', 'Alamat belum tersimpan. Coba lagi.', 500);
        }
        return $this->success(['address' => $this->format($model->owned($email, $savedId))], [], $id ? 200 : 201);
    }

    public function delete(int $id)
    {
        $db = Database::connect();
        if (!$db->tableExists('customer_addresses')) return $this->failure('ADDRESS_BOOK_UNAVAILABLE', 'Buku alamat belum disiapkan.', 503);
        try {
            $deleted = (new CustomerAddressBook($db))->delete($this->authEmail(), $id);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API address delete failed: ' . get_class($e));
            return $this->failure('ADDRESS_DELETE_FAILED', 'Alamat belum terhapus. Coba lagi.', 500);
        }
        if (!$deleted) return $this->failure('ADDRESS_NOT_FOUND', 'Alamat tidak ditemukan.', 404);
        return $this->success(['deleted' => true]);
    }

    public function setDefault(int $id)
    {
        $email = $this->authEmail();
        $db = Database::connect();
        try {
            if (!$db->tableExists('customer_addresses')) return $this->failure('ADDRESS_BOOK_UNAVAILABLE', 'Buku alamat belum disiapkan.', 503);
            $updated = (new CustomerAddressBook($db))->setDefault($email, $id);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API default address update failed: ' . get_class($e));
            return $this->failure('ADDRESS_UPDATE_FAILED', 'Alamat utama belum diperbarui. Coba lagi.', 500);
        }
        if (!$updated) return $this->failure('ADDRESS_NOT_FOUND', 'Alamat tidak ditemukan.', 404);
        return $this->success(['default_address_id' => $id]);
    }

    private function format(?array $row): array
    {
        return [
            'id' => (int)$row['id'], 'label' => (string)$row['label'],
            'recipient_name' => (string)$row['recipient_name'], 'recipient_phone' => (string)$row['recipient_phone'],
            'address' => json_decode((string)$row['address_data'], true) ?: [], 'is_default' => (bool)$row['is_default'],
        ];
    }

    protected function shouldMakeDefault(bool $requested, bool $creating, bool $hasSavedAddresses): bool
    {
        return $requested || ($creating && !$hasSavedAddresses);
    }
}
