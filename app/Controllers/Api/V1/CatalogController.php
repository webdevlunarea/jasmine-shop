<?php

namespace App\Controllers\Api\V1;

use App\Models\BarangModel;
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
        if (mb_strlen($query) > 120 || mb_strlen($category) > 80) return $this->failure('VALIDATION_ERROR', 'Filter pencarian terlalu panjang.', 422);

        $model = (new BarangModel())->select('id,nama,path,harga,rate,stok,deskripsi_nonhtml,kategori,subkategori,diskon,berat,dimensi,varian,jml_varian');
        $count = (new BarangModel())->where('active', '1');
        if ($query !== '') { $model->groupStart()->like('nama', $query)->orLike('pencarian', $query)->groupEnd(); $count->groupStart()->like('nama', $query)->orLike('pencarian', $query)->groupEnd(); }
        if ($category !== '') { $model->where('kategori', $category); $count->where('kategori', $category); }
        $total = $count->countAllResults();
        $rows = $model->where('active', '1')->orderBy('nama', 'ASC')->findAll($perPage, $pagination['offset']);
        return $this->success(['products' => array_map(fn($row) => $this->publicProduct($row), $rows)], [
            'page' => $page, 'per_page' => $perPage, 'total' => $total, 'pages' => (int)ceil($total / $perPage),
        ]);
    }

    public function product(string $id)
    {
        $row = (new BarangModel())->select('id,nama,path,harga,rate,stok,deskripsi,deskripsi_nonhtml,kategori,subkategori,diskon,berat,dimensi,varian,jml_varian,kaca')->where(['id' => $id, 'active' => '1'])->first();
        if (!$row) return $this->failure('PRODUCT_NOT_FOUND', 'Produk tidak ditemukan.', 404);
        return $this->success(['product' => $this->publicProduct($row, true)]);
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
}
