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
        'visibility',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false;

    public function getActiveBanner()
    {
        return $this->where('active', 1)
            ->whereIn('visibility', ['both', 'website'])
            ->orderBy('urutan', 'asc')->orderBy('id', 'asc')->findAll();
    }

    public function getActiveAndroidBanners(): array
    {
        return $this->where('active', 1)
            ->whereIn('visibility', ['both', 'android'])
            ->orderBy('urutan', 'asc')->orderBy('id', 'asc')->findAll();
    }

    public static function normalizeVisibility($value): string
    {
        $visibility = strtolower(trim((string)$value));
        return in_array($visibility, ['both', 'website', 'android'], true) ? $visibility : 'both';
    }
}
