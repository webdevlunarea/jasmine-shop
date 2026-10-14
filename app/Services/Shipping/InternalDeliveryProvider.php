<?php

namespace App\Services\Shipping;

use App\Models\ShippingInternalCoverageModel;
use App\Models\ShippingMethodModel;

class InternalDeliveryProvider
{
    protected ShippingMethodModel $methodModel;
    protected ShippingInternalCoverageModel $coverageModel;

    public function __construct()
    {
        $this->methodModel = new ShippingMethodModel();
        $this->coverageModel = new ShippingInternalCoverageModel();
    }

    public function getQuotes(array $address): array
    {
        $method = $this->methodModel->getByCode('internal_delivery');
        if (!$method || (int)($method['is_active'] ?? 0) !== 1) {
            return [];
        }

        $kecamatanId = (int)($address['kecamatan_id'] ?? 0);
        if ($kecamatanId <= 0 || !$this->coverageModel->isKecamatanCovered((int)$method['id'], $kecamatanId)) {
            return [];
        }

        return [[
            'shipping_method_code' => 'internal_delivery',
            'provider' => 'internal',
            'courier_code' => 'internal',
            'courier_name' => 'Jasa Kirim Toko',
            'service_code' => 'gratis',
            'service_name' => 'Gratis',
            'description' => 'Pengiriman oleh toko untuk area yang tersedia',
            'cost' => 0,
            'etd' => null,
        ]];
    }
}
