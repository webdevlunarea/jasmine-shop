<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherClaimedModel extends Model
{
    protected $table = 'voucher_claimed';
    protected $allowedFields = [
        'id',
        'id_voucher',
        'kadaluarsa',
        'email_user',
        'active'
    ];

    public function getVoucher($id = false)
    {
        if ($id == false) {
            return $this
                ->join('voucher', 'voucher.id = voucher_claimed.id_voucher')
                ->select('voucher_claimed.*')
                ->select('voucher.nama')
                ->select('voucher.satuan')
                ->select('voucher.nominal')
                ->select('voucher.jenis')
                ->select('voucher.keterangan')
                ->select('voucher.durasi_poin')
                ->where(['email_user' => session()->get('email'), 'voucher_claimed.active' => true])
                ->findAll();
        }
        return $this
            ->join('voucher', 'voucher.id = voucher_claimed.id_voucher')
            ->select('voucher_claimed.*')
            ->select('voucher.nama')
            ->select('voucher.satuan')
            ->select('voucher.nominal')
            ->select('voucher.jenis')
            ->select('voucher.keterangan')
            ->select('voucher.durasi_poin')
            ->where(['voucher_claimed.id' => $id, 'voucher_claimed.active' => true])
            ->first();
    }
}
