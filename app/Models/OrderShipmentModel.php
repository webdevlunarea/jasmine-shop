<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderShipmentModel extends Model
{
    protected $table = 'order_shipments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id_midtrans',
        'shipping_quote_token',
        'shipping_method_code',
        'provider',
        'courier_code',
        'courier_name',
        'service_code',
        'service_name',
        'shipping_cost',
        'shipping_pricing',
        'etd',
        'tracking_number',
        'shipping_status',
    ];

    public function getByOrderId(string $idMidtrans)
    {
        return $this->where('id_midtrans', $idMidtrans)->first();
    }
}
