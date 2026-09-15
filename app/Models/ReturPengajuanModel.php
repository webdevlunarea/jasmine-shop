<?php

namespace App\Models;

use CodeIgniter\Model;

class ReturPengajuanModel extends Model
{
    protected $table = 'retur_pengajuan';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'id_midtrans',
        'email_cus',
        'nama_cus',
        'hp_cus',
        'items',
        'alasan',
        'solusi',
        'bukti',
        'status',
        'luna_response',
    ];
}
