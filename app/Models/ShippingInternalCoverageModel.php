<?php

namespace App\Models;

use CodeIgniter\Model;

class ShippingInternalCoverageModel extends Model
{
    protected $table = 'shipping_internal_coverages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'shipping_method_id',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $validationRules = [
        'shipping_method_id' => 'required|integer',
        'provinsi_id' => 'required|integer',
        'kabupaten_id' => 'required|integer',
        'kecamatan_id' => 'required|integer',
        'is_active' => 'permit_empty|in_list[0,1]',
        'created_by' => 'permit_empty|integer',
        'updated_by' => 'permit_empty|integer',
    ];

    public function isKecamatanCovered(int $shippingMethodId, int $kecamatanId): bool
    {
        return $this->where('shipping_method_id', $shippingMethodId)
            ->where('kecamatan_id', $kecamatanId)
            ->where('is_active', 1)
            ->first() !== null;
    }

    public function getActiveCoverageByMethod(int $shippingMethodId)
    {
        return $this->where('shipping_method_id', $shippingMethodId)
            ->where('is_active', 1)
            ->orderBy('provinsi_id', 'ASC')
            ->orderBy('kabupaten_id', 'ASC')
            ->orderBy('kecamatan_id', 'ASC')
            ->findAll();
    }

    public function deactivateCoverage(int $id, ?int $adminId = null): bool
    {
        $data = [
            'is_active' => 0,
        ];

        if ($adminId !== null) {
            $data['updated_by'] = $adminId;
        }

        return (bool) $this->update($id, $data);
    }
}
