<?php

namespace App\Controllers\Api\V1;

use App\Models\BarangModel;
use App\Models\GambarBarangModel;
use App\Services\Api\ApiPagination;
use App\Services\ProductVariantStock;

class CatalogController extends ApiController
{
    public function products()
    {
        $pagination = ApiPagination::resolve($this->request->getGet('page'), $this->request->getGet('per_page'), 20, 40);
        if ($pagination === null) return $this->failure('VALIDATION_ERROR', 'Nomor halaman atau jumlah data per halaman tidak valid.', 422);
        $page = $pagination['page'];
        $perPage = $pagination['per_page'];
        $query = trim((string)$this->request->getGet('q'));
        $category = trim((string)$this->request->getGet('category'));
        $sort = strtolower(trim((string)$this->request->getGet('sort')));
        if ($sort === '') $sort = 'default';
        if (!in_array($sort, ['default', 'popular', 'newest'], true)) return $this->failure('VALIDATION_ERROR', 'Urutan katalog tidak valid.', 422);
        if (mb_strlen($query) > 120 || mb_strlen($category) > 80) return $this->failure('VALIDATION_ERROR', 'Filter pencarian terlalu panjang.', 422);

        $model = (new BarangModel())->select('id,nama,path,harga,rate,stok,deskripsi_nonhtml,kategori,subkategori,diskon,berat,dimensi,varian,jml_varian,tracking_pop,terjual,terjual_custom');
        $count = (new BarangModel())->where('active', '1');
        if ($query !== '') { $model->groupStart()->like('nama', $query)->orLike('pencarian', $query)->groupEnd(); $count->groupStart()->like('nama', $query)->orLike('pencarian', $query)->groupEnd(); }
        if ($category !== '') { $model->where('kategori', $category); $count->where('kategori', $category); }
        if ($sort === 'newest') {
            $count->where("barang.id = (SELECT MAX(newest.id) FROM barang AS newest WHERE newest.path = barang.path AND newest.active = '1')", null, false);
        }
        $total = $count->countAllResults();
        $model->where('active', '1');
        if ($sort === 'newest') {
            // Match the website's newest-products rule: retain the latest active row per public slug.
            $model->where("barang.id = (SELECT MAX(newest.id) FROM barang AS newest WHERE newest.path = barang.path AND newest.active = '1')", null, false)
                ->orderBy('id', 'DESC');
        } elseif ($sort === 'popular') {
            // Rank by the same displayed sales value (manual count first), then use popularity as a stable tie-breaker.
            $model->orderBy('CASE WHEN terjual_custom > 0 THEN terjual_custom ELSE terjual END', 'DESC', false)
                ->orderBy('tracking_pop', 'DESC')->orderBy('id', 'DESC');
        } else {
            $model->orderBy('nama', 'ASC');
        }
        $rows = $model->findAll($perPage, $pagination['offset']);
        return $this->success(['products' => array_map(fn($row) => $this->publicProduct($row), $rows)], [
            'page' => $page, 'per_page' => $perPage, 'total' => $total, 'pages' => (int)ceil($total / $perPage),
        ]);
    }

    public function product(string $id)
    {
        $row = (new BarangModel())->select('id,nama,path,harga,rate,stok,deskripsi,deskripsi_nonhtml,kategori,subkategori,diskon,berat,dimensi,varian,jml_varian,kaca')->where(['id' => $id, 'active' => '1'])->first();
        if (!$row) return $this->failure('PRODUCT_NOT_FOUND', 'Produk tidak ditemukan.', 404);
        $product = $this->publicProduct($row, true);
        $product['images'] = $this->productImageUrls((string)$row['id']);
        return $this->success(['product' => $product]);
    }

    public function categories()
    {
        $rows = (new BarangModel())->select('kategori')->where('active', '1')->groupBy('kategori')->orderBy('kategori', 'ASC')->findAll();
        $items = array_values(array_filter(array_map(static fn($row) => trim((string)$row['kategori']), $rows)));
        return $this->success(['categories' => array_map(static fn($name) => ['name' => $name], $items)]);
    }

    private function publicProduct(array $row, bool $detail = false): array
    {
        $variants = ProductVariantStock::variants($row['varian'] ?? '[]');
        $variantNames = ProductVariantStock::names($variants);
        $variants = array_map(static fn(string $name, int $index) => [
            'name' => $name,
            'stock' => ProductVariantStock::stockForIndex($variants, (string)($row['stok'] ?? ''), $index),
        ], $variantNames, array_keys($variantNames));
        $product = [
            'id' => (string)$row['id'], 'name' => (string)$row['nama'], 'slug' => (string)$row['path'],
            'price' => $this->rupiah((string)($row['harga'] ?? '0')),
            'discount_percent' => max(0, (int)($row['diskon'] ?? 0)),
            'image_url' => base_url('imgpic/' . rawurlencode((string)$row['id'])),
            'category' => (string)($row['kategori'] ?? ''), 'subcategory' => (string)($row['subkategori'] ?? ''),
            'weight_kg' => (float)($row['berat'] ?? 0), 'dimensions' => (string)($row['dimensi'] ?? ''),
            'variants' => $variants, 'stock' => ProductVariantStock::totalStock($row['varian'] ?? '[]', (string)($row['stok'] ?? '')),
            'rating' => (float)($row['rate'] ?? 0),
            'sold_count' => max(0, (int)(($row['terjual_custom'] ?? 0) > 0 ? $row['terjual_custom'] : ($row['terjual'] ?? 0))),
        ];
        if ($detail) {
            $product['description'] = (string)($row['deskripsi_nonhtml'] ?? strip_tags((string)($row['deskripsi'] ?? '')));
            $product['glass_service_available'] = (bool)($row['kaca'] ?? false);
        }
        return $product;
    }

    private function rupiah(string $value): int
    {
        $digits = preg_replace('/[^0-9]/', '', $value);
        return max(0, (int)($digits === '' ? 0 : $digits));
    }

    /** Return only URLs for populated gallery slots; image bytes are never included in the JSON response. */
    private function productImageUrls(string $productId): array
    {
        $fields = [];
        for ($slot = 1; $slot <= 50; $slot++) {
            $fields[] = "IF(`gambar{$slot}` IS NOT NULL AND OCTET_LENGTH(`gambar{$slot}`) > 0, {$slot}, NULL) AS `slot_{$slot}`";
        }

        try {
            $row = (new GambarBarangModel())
                ->select(implode(', ', $fields), false)
                ->where('id', $productId)
                ->first();
        } catch (\Throwable $exception) {
            log_message('warning', 'Product gallery metadata unavailable for catalog API: {message}', [
                'message' => $exception->getMessage(),
            ]);
            return [];
        }

        if (!is_array($row)) return [];

        $urls = [];
        for ($slot = 1; $slot <= 50; $slot++) {
            if ((int)($row['slot_'.$slot] ?? 0) === $slot) {
                $urls[] = base_url('imgproduct/'.rawurlencode($productId).'/'.$slot);
            }
        }
        $coverUrl = base_url('imgproduct/'.rawurlencode($productId).'/1');
        if (!in_array($coverUrl, $urls, true)) array_unshift($urls, $coverUrl);
        return $urls;
    }
}
