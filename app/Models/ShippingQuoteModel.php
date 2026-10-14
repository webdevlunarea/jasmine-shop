<?php

namespace App\Models;

use CodeIgniter\Model;

class ShippingQuoteModel extends Model
{
    protected $table = 'shipping_quotes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'token',
        'email_user',
        'shipping_method_code',
        'provider',
        'courier_code',
        'courier_name',
        'service_code',
        'service_name',
        'description',
        'cost',
        'etd',
        'origin_id',
        'destination_id',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'kelurahan_id',
        'kodepos',
        'weight_grams',
        'cart_hash',
        'address_hash',
        'raw_response',
        'expires_at',
        'used_at',
    ];

    public function findValidToken(string $token, string $email, string $cartHash, string $addressHash)
    {
        return $this->where('token', $token)
            ->where('email_user', $email)
            ->where('cart_hash', $cartHash)
            ->where('address_hash', $addressHash)
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    public function markUsed(string $token): bool
    {
        return (bool) $this->where('token', $token)
            ->set('used_at', date('Y-m-d H:i:s'))
            ->update();
    }
}
