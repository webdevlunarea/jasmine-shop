<?php

namespace App\Models;

use CodeIgniter\Model;

class RatingModel extends Model
{
    protected $table = 'rating';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_barang',
        'email_cus',
        'nama_pembeli',
        'rating',
        'komentar',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;

    public function getByBarang($id_barang)
    {
        return $this->where(['id_barang' => $id_barang])->orderBy('created_at', 'desc')->findAll();
    }

    public function getStats($id_barang)
    {
        $rows = $this->select('rating')->where(['id_barang' => $id_barang])->findAll();
        $count = count($rows);
        if ($count == 0) {
            return ['avg' => 0, 'count' => 0];
        }
        $total = 0;
        foreach ($rows as $r) {
            $total += (int)$r['rating'];
        }
        return ['avg' => round($total / $count, 1), 'count' => $count];
    }

    public function getByUser($id_barang, $email_cus)
    {
        return $this->where(['id_barang' => $id_barang, 'email_cus' => $email_cus])->first();
    }
}
