<?php

namespace App\Controllers;

use App\Models\CustomerAddressModel;

class CustomerAddresses extends BaseController
{
    public function manage()
    {
        // Guard also applies when legacy auto-routing is enabled.
        if (strtolower($this->request->getMethod()) !== 'post'
            || !session('isLogin') || !session('email') || session('email') === 'tamu'
            || (string)session('role') !== '0' || (string)session('active') !== '1') {
            return $this->response->setStatusCode(403)->setBody('Akses ditolak.');
        }
        \Config\Services::security()->verify($this->request);
        $email = (string)session('email');
        $db = \Config\Database::connect();
        if (!$db->tableExists('customer_addresses')) {
            return redirect()->to('/checkout')->withCookies()->with('msg', 'Penyimpanan alamat belum tersedia. Silakan gunakan formulir penerima.');
        }

        $model = new CustomerAddressModel();
        $action = (string)$this->request->getPost('address_action');
        $id = (int)$this->request->getPost('address_id');
        $inTransaction = false;
        try {
            $db->transBegin();
            $inTransaction = true;
            // Serialize edits/default changes per customer on MySQL/MariaDB.
            $ownerQuery = $db->table('pembeli')->select('email_user, alamat, nama, nohp')->where('email_user', $email)->getCompiledSelect();
            $owner = $db->query($ownerQuery . ' FOR UPDATE')->getRowArray();
            if (!$owner) {
                throw new \DomainException('Akun pelanggan tidak ditemukan.');
            }
            $row = $id > 0 ? $model->owned($email, $id) : null;
            if ($id > 0 && !$row) {
                throw new \DomainException('Alamat tidak tersedia untuk akun ini.');
            }
            $selected = null;
            if ($action === 'save') {
                if (!$this->validate([
                    'address_label' => 'required|max_length[60]',
                    'nama' => 'required|max_length[120]',
                    'nohp' => 'required|regex_match[/^[+0-9 ()-]{8,25}$/]',
                    'alamat_add' => 'required|max_length[1000]',
                ])) {
                    throw new \DomainException('Lengkapi label, nama penerima, nomor HP, dan detail alamat dengan benar.');
                }
                if (!$id && count($model->forCustomer($email)) >= 20) {
                    throw new \DomainException('Maksimal 20 alamat tersimpan. Hapus alamat yang sudah tidak digunakan.');
                }
                $address = $this->validatedAddress();
                // Preserve a different legacy address on the first explicit save, not on a GET request.
                if (!$id && !$model->forCustomer($email)) {
                    $legacy = json_decode((string)($owner['alamat'] ?? ''), true);
                    if (is_array($legacy) && !empty($legacy['prov_id']) && !empty($legacy['kab_id'])
                        && !empty($legacy['kec_id']) && !empty($legacy['desa']) && !empty($legacy['add'])
                        && ((string)$legacy['kec_id'] !== $address['kec_id']
                            || strcasecmp(trim($legacy['desa']), $address['desa']) !== 0
                            || trim($legacy['add']) !== $address['add'])) {
                        if (!$model->insert([
                            'email_user' => $email, 'label' => 'Alamat sebelumnya',
                            'recipient_name' => (string)($owner['nama'] ?: session('nama')),
                            'recipient_phone' => (string)($owner['nohp'] ?: session('nohp')),
                            'address_data' => json_encode($legacy, JSON_THROW_ON_ERROR), 'is_default' => 1,
                        ])) throw new \DomainException('Alamat sebelumnya belum tersimpan. Silakan coba lagi.');
                    }
                }
                $isDefault = $this->request->getPost('address_default') === '1'
                    || count($model->forCustomer($email)) === 0 || !empty($row['is_default']);
                if ($isDefault) {
                    $model->where('email_user', $email)->set('is_default', 0)->update();
                }
                $data = [
                    'email_user' => $email,
                    'label' => trim((string)$this->request->getPost('address_label')),
                    'recipient_name' => trim((string)$this->request->getPost('nama')),
                    'recipient_phone' => trim((string)$this->request->getPost('nohp')),
                    'address_data' => json_encode($address, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'is_default' => $isDefault ? 1 : 0,
                ];
                $saved = $id ? $model->update($id, $data) : $model->insert($data);
                if (!$saved) throw new \DomainException('Alamat belum tersimpan. Silakan coba lagi.');
                $selected = $model->owned($email, $id ?: (int)$saved);
            } elseif ($action === 'select' && $row) {
                $selected = $row;
            } elseif ($action === 'default' && $row) {
                $model->where('email_user', $email)->set('is_default', 0)->update();
                $model->update($id, ['is_default' => 1]);
            } elseif ($action === 'delete' && $row) {
                $model->delete($id);
                $remaining = $model->forCustomer($email);
                if (!empty($row['is_default']) && $remaining) {
                    $model->update($remaining[0]['id'], ['is_default' => 1]);
                }
                if ((int)session('checkout_address_id') === $id && session('checkout_address_owner') === $email) {
                    $selected = $remaining[0] ?? null;
                    session()->remove(['checkout_address_id', 'checkout_address_owner', 'checkout_recipient']);
                }
            } else {
                throw new \DomainException('Tindakan alamat tidak valid.');
            }
            if ($db->transStatus() === false) throw new \DomainException('Alamat belum tersimpan. Silakan coba lagi.');
            $db->transCommit();
            $inTransaction = false;
            if ($selected) $this->selectAddress($selected, $email);
            return redirect()->to('/checkout')->withCookies()->with('msg', $action === 'delete' ? 'Alamat dihapus.' : 'Alamat berhasil diperbarui. Periksa penerima dan pilih kembali ongkir.');
        } catch (\DomainException $e) {
            if ($inTransaction) $db->transRollback();
            return redirect()->to('/checkout')->withCookies()->with('msg', $e->getMessage());
        } catch (\Throwable $e) {
            if ($inTransaction) $db->transRollback();
            log_message('error', 'Customer address operation failed: ' . get_class($e));
            return redirect()->to('/checkout')->withCookies()->with('msg', 'Alamat belum tersimpan. Silakan coba lagi.');
        }
    }

    protected function validatedAddress(): array
    {
        $db = \Config\Database::connect();
        $provinceId = explode('-', (string)$this->request->getPost('provinsi'), 2)[0];
        $cityId = explode('-', (string)$this->request->getPost('kota'), 2)[0];
        $districtId = explode('-', (string)$this->request->getPost('kecamatan'), 2)[0];
        $villageInput = (string)$this->request->getPost('kodepos');
        $separator = strrpos($villageInput, '-');
        if (!ctype_digit($provinceId) || !ctype_digit($cityId) || !ctype_digit($districtId) || $separator === false) {
            throw new \DomainException('Pilih wilayah alamat secara lengkap.');
        }
        $province = $db->table('provinsi')->where('id', $provinceId)->get()->getRowArray();
        $city = $db->table('kabupaten')->where(['id' => $cityId, 'provinsi_id' => $provinceId])->get()->getRowArray();
        $district = $db->table('kecamatan')->where(['id' => $districtId, 'kabupaten_id' => $cityId, 'provinsi_id' => $provinceId])->get()->getRowArray();
        $villages = $db->table('kelurahan')->where([
            'kecamatan_id' => $districtId,
            'kodepos' => substr($villageInput, $separator + 1),
        ])->get()->getResultArray();
        $village = null;
        foreach ($villages as $candidate) {
            $displayName = ucwords(strtolower(trim(explode('/', (string)$candidate['label'])[0])));
            if (strcasecmp($displayName, trim(substr($villageInput, 0, $separator))) === 0) {
                if ($village !== null) throw new \DomainException('Wilayah alamat memiliki data ganda. Silakan hubungi CS.');
                $village = $candidate;
            }
        }
        if (!$province || !$city || !$district || !$village) {
            throw new \DomainException('Wilayah alamat tidak sesuai. Pilih ulang provinsi hingga kelurahan.');
        }
        $detail = trim((string)$this->request->getPost('alamat_add'));
        return [
            'prov_id' => (string)$province['id'], 'prov' => $province['label'],
            'kab_id' => (string)$city['id'], 'kab' => $city['label'],
            'kec_id' => (string)$district['id'], 'kec' => $district['label'],
            'desa' => ucwords(strtolower(trim(explode('/', (string)$village['label'])[0]))), 'kodepos' => (string)$village['kodepos'], 'add' => $detail,
            'alamat' => $detail . ', ' . $village['label'] . ', ' . $district['label'] . ', ' . $city['label'] . ', ' . $province['label'] . ' ' . $village['kodepos'],
        ];
    }

    protected function selectAddress(array $row, string $email): void
    {
        $address = json_decode($row['address_data'], true);
        if (!is_array($address)) throw new \DomainException('Data alamat perlu diperbarui.');
        // Legacy session shape is retained for checkout, quote hashing, and order snapshots.
        session()->set([
            'alamat' => $address, 'checkout_address_id' => (int)$row['id'], 'checkout_address_owner' => $email,
            'checkout_recipient' => ['nama' => $row['recipient_name'], 'nohp' => $row['recipient_phone']],
        ]);
    }
}
