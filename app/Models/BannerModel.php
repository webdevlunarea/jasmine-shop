<?php

namespace App\Models;

use CodeIgniter\Model;

class BannerModel extends Model
{
    protected $table = 'banner';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'judul',
        'alt',
        'link',
        'gambar',
        'urutan',
        'active',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;

    public function getActiveBanner()
    {
        return $this->where(['active' => '1'])->orderBy('urutan', 'asc')->orderBy('id', 'asc')->findAll();
    }
}
