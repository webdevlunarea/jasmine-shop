<?php

namespace App\Controllers\Api\V1;

use App\Models\BarangModel;
use App\Models\PembeliModel;
use App\Services\CustomerWishlistStore;
use Config\Database;

final class WishlistController extends ApiController
{
    public function index()
    {
        $profile = (new PembeliModel())->getPembeli($this->authEmail());
        if (!$profile) return $this->failure('CUSTOMER_NOT_FOUND', 'Data akun tidak tersedia.', 404);
        $items = json_decode((string)($profile['wishlist'] ?? '[]'), true);
        $items = is_array($items) ? $items : [];
        $result = [];
        foreach ($items as $item) {
            $id = is_array($item) ? (string)($item['id'] ?? '') : (string)$item;
            if ($id === '') continue;
            $variant = is_array($item) ? trim((string)($item['varian'] ?? '')) : '';
            $product = (new BarangModel())->select('id,nama,path,harga,diskon')->where(['id' => $id, 'active' => '1'])->first();
            if (!$product) continue;
            $digits = preg_replace('/[^0-9]/', '', (string)$product['harga']);
            $price = (int)round((int)($digits ?: 0) * (100 - max(0, (int)$product['diskon'])) / 100);
            $result[] = ['product_id' => $id, 'name' => (string)$product['nama'], 'slug' => (string)$product['path'], 'variant' => $variant, 'price' => $price, 'image_url' => base_url('imgpic/' . rawurlencode($id))];
        }
        return $this->success(['items' => $result]);
    }

    public function add()
    {
        $input = $this->payload();
        $id = trim((string)($input['product_id'] ?? ''));
        $variant = trim((string)($input['variant'] ?? ''));
        $product = (new BarangModel())->select('id,varian')->where(['id' => $id, 'active' => '1'])->first();
        if (!$product || $id === '' || mb_strlen($variant) > 100) return $this->failure('PRODUCT_UNAVAILABLE', 'Produk tidak tersedia.', 404);
        $variants = json_decode((string)$product['varian'], true);
        $variants = is_array($variants) && $variants ? $variants : ['Default'];
        $names = array_map(static fn($v) => is_array($v) ? (string)($v['nama'] ?? $v['name'] ?? $v['varian'] ?? '') : (string)$v, $variants);
        if ($variant === '') $variant = (string)$names[0];
        if (!in_array($variant, $names, true)) return $this->failure('VARIANT_UNAVAILABLE', 'Varian produk tidak tersedia.', 422);
        return $this->mutate($id, $variant, true);
    }

    public function delete()
    {
        $input = $this->payload();
        $id = trim((string)($input['product_id'] ?? ''));
        $variant = trim((string)($input['variant'] ?? ''));
        return $this->mutate($id, $variant, false);
    }

    private function mutate(string $id, string $variant, bool $add)
    {
        $model = new PembeliModel();
        $profile = $model->getPembeli($this->authEmail());
        if (!$profile) return $this->failure('CUSTOMER_NOT_FOUND', 'Data akun tidak tersedia.', 404);
        $rawWishlist = array_key_exists('wishlist', $profile) ? ($profile['wishlist'] === null ? null : (string)$profile['wishlist']) : '[]';
        $items = json_decode((string)($rawWishlist ?? '[]'), true);
        $items = is_array($items) ? $items : [];
        $found = false; $next = [];
        foreach ($items as $item) {
            $normalized = is_array($item) ? ['id' => (string)($item['id'] ?? ''), 'varian' => (string)($item['varian'] ?? ''), 'index_gambar' => max(0, (int)($item['index_gambar'] ?? 0))] : ['id' => (string)$item, 'varian' => '', 'index_gambar' => 0];
            if ($normalized['id'] === $id && mb_strtolower(trim($normalized['varian'])) === mb_strtolower(trim($variant))) { $found = true; if (!$add) continue; }
            $next[] = $normalized;
        }
        if ($add && !$found) $next[] = ['id' => $id, 'varian' => $variant, 'index_gambar' => 0];
        try {
            $saved = (new CustomerWishlistStore(Database::connect()))->saveIfUnchanged($this->authEmail(), $rawWishlist, $next);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API wishlist persistence failed: ' . get_class($e));
            return $this->failure('WISHLIST_SAVE_FAILED', 'Daftar favorit belum tersimpan. Coba lagi.', 500);
        }
        if (!$saved) return $this->failure('WISHLIST_CHANGED_RETRY', 'Daftar favorit berubah di perangkat lain. Muat ulang lalu coba lagi.', 409);
        return $this->success(['items' => $next, 'changed' => $add ? !$found : $found]);
    }
}
