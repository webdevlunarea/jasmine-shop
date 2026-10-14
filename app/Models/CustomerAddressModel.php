<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerAddressModel extends Model
{
    protected $table = 'customer_addresses';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['email_user', 'label', 'recipient_name', 'recipient_phone', 'address_data', 'is_default'];

    public function forCustomer(string $email): array
    {
        return $this->where('email_user', $email)->orderBy('is_default', 'DESC')->orderBy('id', 'ASC')->findAll();
    }

    public function owned(string $email, int $id): ?array
    {
        return $this->where(['email_user' => $email, 'id' => $id])->first();
    }
}
