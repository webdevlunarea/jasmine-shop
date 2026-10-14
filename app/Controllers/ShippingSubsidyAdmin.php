<?php

namespace App\Controllers;

use App\Models\ShippingMethodModel;
use App\Services\Shipping\ShippingMethodSettingsStore;
use App\Services\Shipping\ShippingSubsidyPolicy;

class ShippingSubsidyAdmin extends BaseController
{
    public function save()
    {
        if (strtolower($this->request->getMethod()) !== 'post' || !session('isLogin') || (string)session('role') !== '1') {
            return $this->response->setStatusCode(403)->setBody('Akses admin diperlukan.');
        }
        \Config\Services::security()->verify($this->request);
        try {
            $method = (new ShippingMethodModel())->getByCode('rajaongkir_domestic');
            if (!$method) throw new \DomainException('Metode ekspedisi belum tersedia.');
            $policy = ShippingSubsidyPolicy::fromInput([
                'enabled' => $this->request->getPost('subsidy_enabled') === '1',
                'percentage' => $this->request->getPost('subsidy_percentage'),
                'max_amount' => $this->request->getPost('subsidy_max_amount'),
                'couriers' => $this->request->getPost('subsidy_couriers') ?? [],
                'starts_at' => $this->request->getPost('subsidy_starts_at'),
                'ends_at' => $this->request->getPost('subsidy_ends_at'),
            ]);
            if ($policy['enabled'] && !ShippingSubsidyPolicy::storageReady(\Config\Database::connect())) {
                throw new \DomainException('Aktivasi menunggu pembaruan kolom shipping_pricing. Pengaturan belum diubah.');
            }
            $policy['updated_by'] = (string)session('email');
            $policy['updated_at'] = date('c');
            ShippingMethodSettingsStore::merge((int)$method['id'], ['subsidy' => $policy]);
            return redirect()->to('/shippingadmin')->withCookies()->with('msg', 'Pengaturan subsidi tersimpan. Hanya ongkir akhir yang ditampilkan kepada pembeli; pesanan lama tidak berubah.');
        } catch (\DomainException $e) {
            return redirect()->to('/shippingadmin')->withCookies()->with('msg', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            log_message('error', 'Shipping subsidy settings failed: ' . get_class($e));
            return redirect()->to('/shippingadmin')->withCookies()->with('msg', 'Pengaturan belum tersimpan. Muat ulang lalu coba lagi.')->withInput();
        }
    }
}
