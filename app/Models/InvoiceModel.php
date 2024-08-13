<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoice';
    protected $allowedFields = [
        'id',
        'tanggal',
        'nama',
        'alamat',
        'items',
    ];

    public function getInvoice($id = false)
    {
        if (!$id) {
            return $this->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
}
