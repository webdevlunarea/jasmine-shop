<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;

/** New admin catalog media only. Existing images are not converted or removed. */
final class CatalogImageUpload
{
    public const MAX_BYTES = 4194304;
    public const MAX_BATCH_BYTES = 12582912;
    public const ACCEPT = '.webp,.avif,image/webp,image/avif';

    public static function read(?UploadedFile $file, string $label = 'Gambar', int $limit = self::MAX_BYTES, bool $required = false): ?string
    {
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) {
            if ($required) throw new \DomainException($label.' wajib dipilih.');
            return null;
        }
        if (!$file->isValid() || $file->hasMoved()) throw new \DomainException($label.' gagal diunggah. Periksa batas upload server dan pilih ulang file.');
        if ($file->getSize() > $limit) throw new \DomainException($label.' maksimal '.($limit / 1048576).' MB.');
        $bytes = file_get_contents($file->getTempName());
        if ($bytes === false) throw new \DomainException($label.' gagal dibaca. Pilih ulang file.');
        self::validate($bytes, $limit, $label);
        return $bytes;
    }

    public static function validate(string $bytes, int $limit = self::MAX_BYTES, string $label = 'Gambar'): string
    {
        if ($bytes === '' || strlen($bytes) > $limit) throw new \DomainException($label.' kosong atau melebihi batas ukuran.');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        $info = @getimagesizefromstring($bytes);
        if (!in_array($mime, ['image/webp', 'image/avif'], true) || ($info['mime'] ?? '') !== $mime) {
            throw new \DomainException($label.' wajib berformat WebP atau AVIF asli, bukan hanya mengganti ekstensi file.');
        }
        $width = (int)($info[0] ?? 0); $height = (int)($info[1] ?? 0);
        if ($width < 1 || $height < 1) throw new \DomainException($label.' belum dapat divalidasi server. Gunakan WebP atau periksa dukungan AVIF/PHP 8.2+.');
        if ($width > 4096 || $height > 4096 || $width * $height > 16000000) throw new \DomainException($label.' maksimal 4096 px per sisi dan 16 megapiksel.');
        // Decode where supported to reject damaged files, without requiring an AVIF encoder.
        $codec = $mime === 'image/avif' ? 'AVIF Support' : 'WebP Support';
        if (function_exists('gd_info') && !empty(gd_info()[$codec])) {
            $image = @imagecreatefromstring($bytes);
            if ($image === false) throw new \DomainException($label.' rusak atau tidak terbaca. Ekspor ulang sebagai WebP/AVIF.');
            imagedestroy($image);
        }
        return $mime;
    }

    public static function source(?string $bytes): string
    {
        $info = $bytes ? @getimagesizefromstring($bytes) : false;
        $mime = $info['mime'] ?? '';
        return in_array($mime, ['image/webp','image/avif','image/jpeg','image/png','image/gif'], true)
            ? 'data:'.$mime.';base64,'.base64_encode($bytes) : '/img/nopic.jpg';
    }

    public static function slotUrl(string $productId, int $slot, ?string $bytes): string
    {
        if ($productId === '' || $slot < 1 || $slot > 50 || !$bytes) return '/img/nopic.jpg';
        return '/imgproduct/'.rawurlencode($productId).'/'.$slot.'?v='.substr(hash('sha256', $bytes), 0, 32);
    }

    public static function slotCount(array $product, array $images): int
    {
        $variants = json_decode($product['varian'] ?? '[]', true);
        $count = max(1, count(is_array($variants) ? $variants : []) + max(1, (int)($product['jml_varian'] ?? 1)) - 1);
        for ($i = 1; $i <= 50; $i++) if (!empty($images['gambar'.$i])) $count = max($count, $i);
        return min(50, $count);
    }
}
