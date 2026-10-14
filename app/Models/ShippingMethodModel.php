<?php

namespace App\Models;

use CodeIgniter\Model;

class ShippingMethodModel extends Model
{
    protected $table = 'shipping_methods';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'code',
        'name',
        'type',
        'provider',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $validationRules = [
        'code' => 'required|max_length[80]',
        'name' => 'required|max_length[120]',
        'type' => 'required|max_length[50]',
        'provider' => 'permit_empty|max_length[80]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'sort_order' => 'permit_empty|integer',
    ];

    public function getActiveMethods()
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function getByCode(string $code)
    {
        return $this->where('code', $code)->first();
    }

    public function isInternalDeliveryEnabled(): bool
    {
        $method = $this->where('code', 'internal_delivery')->first();

        return $method !== null && (int) ($method['is_active'] ?? 0) === 1;
    }
}
