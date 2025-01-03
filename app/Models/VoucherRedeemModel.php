<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherRedeemModel extends Model
{
    protected $table = 'voucher_redeem';
    protected $allowedFields = [
        'id',
        'id_voucher',
        'code',
        'email_user'
    ];

    public function getVoucher($id = false)
    {
        if ($id == false) {
            return $this
                ->join('voucher', 'voucher.id = voucher_redeem.id_voucher')
                ->select('voucher_redeem.*')
                ->select('voucher.nama')
                ->select('voucher.satuan')
                ->select('voucher.nominal')
                ->select('voucher.jenis')
                ->select('voucher.keterangan')
                ->select('voucher.durasi_poin')
                ->select('voucher.poster')
                ->orderBy('voucher.nama', 'asc')
                ->findAll();
        }
        return $this
            ->join('voucher', 'voucher.id = voucher_redeem.id_voucher')
            ->select('voucher_redeem.*')
            ->select('voucher.nama')
            ->select('voucher.satuan')
            ->select('voucher.nominal')
            ->select('voucher.jenis')
            ->select('voucher.keterangan')
            ->select('voucher.durasi_poin')
            ->select('voucher.poster')
            ->where(['voucher_redeem.id' => $id])
            ->orderBy('voucher.nama', 'asc')
            ->first();
    }
}
