<?php

namespace App\Models;

use CodeIgniter\Model;

class VoucherModel extends Model
{
    protected $table = 'voucher';
    protected $allowedFields = [
        'id',
        'nama',
        'satuan',
        'nominal',
        'durasi',
        'durasi_poin',
        'jenis',
        'active',
        'code',
        'all_user',
        'keterangan',
        'auto_claimed',
        'poster',
        'private',
        'kuota',
        'poster_email',
        'isi_email',
        'isi_email_input',
        'jadwal',
        'syarat_ketentuan',
        'max_potongan',
        'tidak_gabung_voucher_baru',
    ];

    public function getVoucher($id = false)
    {
        if ($id == false) {
            $voucher = $this->where(['active' => '1'])->orderBy('id', 'desc')->findAll();
            return array_values(array_filter($voucher, fn ($v) => $this->masihDalamPeriode($v)));
        }

        $voucher = $this->where(['active' => '1', 'id' => $id])->first();
        return $this->masihDalamPeriode($voucher) ? $voucher : null;
    }

    private function masihDalamPeriode($voucher)
    {
        if (!$voucher || empty($voucher['jadwal'])) {
            return true;
        }

        $jadwal = explode('@', $voucher['jadwal']);
        if (count($jadwal) < 2 || !$jadwal[0] || !$jadwal[1]) {
            return true;
        }

        $tanggalHariIni = strtotime(date('Y-m-d', strtotime('+7 Hours')));
        return $tanggalHariIni >= strtotime($jadwal[0]) && $tanggalHariIni <= strtotime($jadwal[1]);
    }
}
