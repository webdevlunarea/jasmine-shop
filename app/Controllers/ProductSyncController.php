<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\StokModel;

class ProductSyncController extends BaseController
{
    protected $barangModel;
    protected $stokModel;

    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->stokModel = new StokModel();
    }

    public function syncFromLuna()
    {
        $token = (string)($this->request->getHeaderLine('X-Luna-Webhook-Token') ?: $this->request->getHeaderLine('X-Webhook-Token'));
        $expectedToken = (string)env('LUNA_SYSTEM_WEB_ORDER_TOKEN', '');

        if (!$expectedToken || !hash_equals($expectedToken, $token)) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Invalid webhook token',
            ]);
        }

        $body = json_decode($this->request->getBody(), true);
        if (!is_array($body)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Payload tidak valid',
            ]);
        }

        $products = $body['products'] ?? [];
        $createMissing = filter_var($body['create_missing'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $dryRun = filter_var($body['dry_run'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $syncIdentity = filter_var($body['sync_identity'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!is_array($products) || count($products) === 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Produk sync kosong',
            ]);
        }

        $summary = [
            'received' => count($products),
            'updated' => 0,
            'created' => 0,
            'skipped' => 0,
            'dry_run' => $dryRun,
            'items' => [],
        ];

        foreach ($products as $product) {
            if (!is_array($product)) {
                $summary['skipped']++;
                continue;
            }

            $match = $this->findWebsiteProduct($product);
            if (!$match && !$createMissing) {
                $summary['skipped']++;
                $summary['items'][] = [
                    'sku' => $this->clean($product['sku'] ?? ''),
                    'name' => $this->clean($product['name'] ?? ''),
                    'status' => 'skipped_not_found',
                ];
                continue;
            }

            $syncData = $this->buildWebsiteProductData($product, $match, $syncIdentity || !$match);
            if (!$match) {
                $syncData['id'] = $this->clean($product['sku'] ?? '') ?: ('LUNA-' . time() . random_int(100, 999));
            }

            if (!$dryRun) {
                if ($match) {
                    $this->barangModel->where(['id' => $match['id']])->set($syncData)->update();
                    $this->recordStockChanges($match, $syncData, $product);
                } else {
                    $this->barangModel->insert($syncData);
                    $this->recordInitialStock($syncData, $product);
                }
            }

            if ($match) {
                $summary['updated']++;
            } else {
                $summary['created']++;
            }

            $summary['items'][] = [
                'website_id' => $match['id'] ?? $syncData['id'],
                'sku' => $this->clean($product['sku'] ?? ''),
                'name' => $syncData['nama'] ?? '',
                'status' => $match ? 'updated' : 'created',
                'match_by' => $match['_luna_match_by'] ?? ($match ? 'unknown' : 'new_product'),
                'stock' => $syncData['stok'] ?? '',
                'price' => $syncData['harga'] ?? 0,
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sync produk Luna selesai',
            'data' => $summary,
        ]);
    }

    private function findWebsiteProduct(array $product): ?array
    {
        $websiteId = $this->clean($product['website_id'] ?? '');
        $sku = $this->clean($product['sku'] ?? '');
        $slug = $this->clean($product['slug'] ?? '');
        $name = $this->clean($product['name'] ?? '');

        $manualWebsiteId = $this->manualWebsiteProductId($product);
        if ($manualWebsiteId) {
            $row = $this->barangModel->where(['id' => $manualWebsiteId])->first();
            if ($row) {
                $row['_luna_match_by'] = 'manual_map';
                return $row;
            }
        }

        foreach ([$websiteId, $sku, $this->clean($product['id'] ?? '')] as $id) {
            if ($id) {
                $row = $this->barangModel->where(['id' => $id])->first();
                if ($row) {
                    $row['_luna_match_by'] = 'id';
                    return $row;
                }
            }
        }

        if ($slug) {
            $row = $this->barangModel->where(['path' => $slug])->first();
            if ($row) {
                $row['_luna_match_by'] = 'slug';
                return $row;
            }
        }

        if ($name) {
            $row = $this->barangModel->where(['nama' => $name])->first();
            if ($row) {
                $row['_luna_match_by'] = 'name_exact';
                return $row;
            }
        }

        $fuzzy = $this->findWebsiteProductByNormalizedName($name);
        if ($fuzzy) return $fuzzy;

        return null;
    }

    private function manualWebsiteProductId(array $product): ?string
    {
        $sku = $this->clean($product['sku'] ?? '');
        $nameKey = $this->normalizeNameKey($this->clean($product['name'] ?? ''));

        $map = [
            // Mapping aman satu produk Luna -> satu produk website.
            'SKU-1773215956903-RSGA' => 'B20240212120041', // RSG A4.4 FULL KUNCI -> RSG A4.4
            'SKU-1773215937587-MT20' => 'B20240212150717', // MT 205
            'SKU-1773215938190-MT55' => 'B20240214225454', // MT 550 SAEMAS -> MT 550
            'SKU-1773215953869-RSG3' => 'B20240212113451', // RSG 3S
            'SKU-1773215938753-MT55' => 'B20240214225740', // MT 552 SAEMAS -> MT 552
            'SKU-1773215934040-MRH8' => 'B20241106133158', // MRH 800
            'SKU-1773215946575-RSD1' => 'B20240301104421', // RSD 10
            'SKU-1773215948649-RSD8' => 'B20240221092912', // RSD 8
            'SKU-1773215943379-RSGK' => 'B20240214221123', // RSG K
            'SKU-1773082013429-ALD8' => 'B20240213111208', // ALD 8113 -> ALD 8113 Andalan
            'SKU-1773215955028-RSG4' => 'B20240212114312', // RSG 4S
            'SKU-1773132253853-RSGC' => 'B20240212120745', // RSG C5.5 FULL KUNCI -> RSG C5.5
            'SKU-1778933299117-SWEET' => 'B20241210121113', // Sweet Package
            'SKU-1773215941668-RB61' => 'B20240214230935', // RB 6180
            'SKU-1775870531163-BUNDL' => 'B20241210154615', // Bundling B MT570 + Kursi Saemas
        ];

        if ($sku && isset($map[$sku])) {
            return $map[$sku];
        }

        $nameMap = [
            'RSGA44FULLKUNCI' => 'B20240212120041',
            'MT205' => 'B20240212150717',
            'MT550SAEMAS' => 'B20240214225454',
            'RSG3S' => 'B20240212113451',
            'MT552SAEMAS' => 'B20240214225740',
            'MRH800' => 'B20241106133158',
            'RSD10' => 'B20240301104421',
            'RSD8' => 'B20240221092912',
            'RSGK' => 'B20240214221123',
            'ALD8113' => 'B20240213111208',
            'RSG4S' => 'B20240212114312',
            'RSGC55FULLKUNCI' => 'B20240212120745',
            'SWEETPACKAGEALD8112KPPUTIHMRS866KOTAKPUTIH' => 'B20241210121113',
            'RB6180' => 'B20240214230935',
            'BUNDLINGBMT570KURSISAEMAS' => 'B20241210154615',
        ];

        return $nameMap[$nameKey] ?? null;
    }

    private function findWebsiteProductByNormalizedName(string $name): ?array
    {
        $normalizedName = $this->normalizeNameKey($name);
        if (strlen($normalizedName) < 4) return null;

        $products = $this->barangModel
            ->select('id,nama,path,pencarian,harga,berat,stok,dimensi,deskripsi,deskripsi_nonhtml,kategori,subkategori,diskon,varian,jml_varian,shopee,tokped,tiktok,youtube,active,kaca')
            ->findAll();

        $exactMatches = [];
        $containsMatches = [];

        foreach ($products as $row) {
            $websiteName = $this->normalizeNameKey($row['nama'] ?? '');
            $websitePath = $this->normalizeNameKey(str_replace('-', ' ', $row['path'] ?? ''));
            $websiteSearch = $this->normalizeNameKey($row['pencarian'] ?? '');
            $haystack = $websiteName . ' ' . $websitePath . ' ' . $websiteSearch;

            if ($websiteName === $normalizedName || $websitePath === $normalizedName) {
                $exactMatches[] = $row;
                continue;
            }

            if (
                str_contains($websiteName, $normalizedName) ||
                str_contains($websitePath, $normalizedName) ||
                str_contains($websiteSearch, $normalizedName) ||
                str_contains($haystack, $normalizedName)
            ) {
                $containsMatches[] = $row;
            }
        }

        if (count($exactMatches) === 1) {
            $exactMatches[0]['_luna_match_by'] = 'name_normalized_exact';
            return $exactMatches[0];
        }

        if (count($containsMatches) === 1) {
            $containsMatches[0]['_luna_match_by'] = 'name_normalized_contains';
            return $containsMatches[0];
        }

        return null;
    }

    private function buildWebsiteProductData(array $product, ?array $existing, bool $syncIdentity): array
    {
        $incomingName = $this->clean($product['name'] ?? ($existing['nama'] ?? ''));
        $name = ($existing && !$syncIdentity) ? ($existing['nama'] ?? $incomingName) : $incomingName;
        $incomingSlug = $this->slugify($this->clean($product['slug'] ?? '') ?: $incomingName);
        $slug = ($existing && !$syncIdentity) ? ($existing['path'] ?? $incomingSlug) : $incomingSlug;
        $variants = $this->normalizeVariants($product);
        $variantNames = array_map(fn($variant) => $variant['name'], $variants);
        $stockValues = array_map(fn($variant) => (string)$variant['stock'], $variants);
        $description = ($existing && !$syncIdentity)
            ? (string)($existing['deskripsi'] ?? '')
            : (string)($product['description'] ?? ($existing['deskripsi'] ?? ''));

        $data = [
            'nama' => $name ?: ($existing['nama'] ?? ''),
            'path' => $slug ?: ($existing['path'] ?? ''),
            'pencarian' => ($existing && !$syncIdentity) ? ($existing['pencarian'] ?? $name) : $this->clean($product['search'] ?? $product['sku'] ?? $name),
            'harga' => (int)round((float)($product['sellingPrice'] ?? $product['price'] ?? ($existing['harga'] ?? 0))),
            'berat' => (string)($product['weight'] ?? ($existing['berat'] ?? '')),
            'stok' => implode(',', $stockValues),
            'dimensi' => $this->formatDimension($product, $existing),
            'deskripsi' => $description,
            'deskripsi_nonhtml' => $this->plainText($description),
            'kategori' => ($existing && !$syncIdentity) ? ($existing['kategori'] ?? '') : $this->clean($product['category'] ?? ($existing['kategori'] ?? '')),
            'subkategori' => ($existing && !$syncIdentity) ? ($existing['subkategori'] ?? '') : $this->clean($product['subCategory'] ?? $product['subcategory'] ?? ($existing['subkategori'] ?? '')),
            'varian' => json_encode($variantNames),
            'jml_varian' => (int)($product['image_variant_count'] ?? ($existing['jml_varian'] ?? 1)) ?: 1,
            'active' => !empty($product['isActive']) ? '1' : '0',
        ];

        foreach (['diskon', 'shopee', 'tokped', 'tiktok', 'youtube', 'kaca'] as $field) {
            if (array_key_exists($field, $product)) {
                $data[$field] = $product[$field];
            } elseif ($existing && array_key_exists($field, $existing)) {
                $data[$field] = $existing[$field];
            }
        }

        return $data;
    }

    private function normalizeVariants(array $product): array
    {
        $variants = $product['variants'] ?? $product['variantItems'] ?? [];
        if (!is_array($variants) || count($variants) === 0) {
            return [[
                'name' => 'Default',
                'stock' => max(0, (int)($product['stock'] ?? 0)),
            ]];
        }

        $normalized = [];
        foreach ($variants as $variant) {
            if (!is_array($variant)) continue;
            $name = $this->clean($variant['value'] ?? $variant['name'] ?? '');
            if (!$name) continue;
            $normalized[] = [
                'name' => $name,
                'stock' => max(0, (int)($variant['stock'] ?? 0)),
            ];
        }

        return count($normalized) ? $normalized : [[
            'name' => 'Default',
            'stock' => max(0, (int)($product['stock'] ?? 0)),
        ]];
    }

    private function recordStockChanges(array $existing, array $syncData, array $product): void
    {
        $oldVariants = json_decode($existing['varian'] ?? '[]', true);
        if (!is_array($oldVariants)) $oldVariants = [];

        $newVariants = json_decode($syncData['varian'] ?? '[]', true);
        if (!is_array($newVariants)) $newVariants = [];

        $oldStock = array_map('intval', explode(',', (string)($existing['stok'] ?? '')));
        $newStock = array_map('intval', explode(',', (string)($syncData['stok'] ?? '')));

        foreach ($newVariants as $index => $variantName) {
            $previousIndex = array_search($variantName, $oldVariants);
            $previousStock = $previousIndex !== false ? (int)($oldStock[$previousIndex] ?? 0) : 0;
            $currentStock = (int)($newStock[$index] ?? 0);

            if ($previousStock === $currentStock) continue;

            $this->stokModel->insert([
                'id' => random_int(1000000000, 2147483647),
                'id_barang' => $existing['id'],
                'nama' => $syncData['nama'],
                'varian' => $variantName,
                'jumlah' => (string)($currentStock - $previousStock),
                'email_admin' => 'luna-system-sync',
                'keterangan' => 'Sync otomatis dari Luna Sistem' . ($this->clean($product['sku'] ?? '') ? ' SKU: ' . $this->clean($product['sku']) : ''),
                'stok_akhir' => $currentStock,
                'tanggal' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function recordInitialStock(array $syncData, array $product): void
    {
        $variants = json_decode($syncData['varian'] ?? '[]', true);
        $stocks = array_map('intval', explode(',', (string)($syncData['stok'] ?? '')));
        foreach ($variants as $index => $variantName) {
            $stock = (int)($stocks[$index] ?? 0);
            $this->stokModel->insert([
                'id' => random_int(1000000000, 2147483647),
                'id_barang' => $syncData['id'],
                'nama' => $syncData['nama'],
                'varian' => $variantName,
                'jumlah' => (string)$stock,
                'email_admin' => 'luna-system-sync',
                'keterangan' => 'Produk dibuat dari sync Luna Sistem' . ($this->clean($product['sku'] ?? '') ? ' SKU: ' . $this->clean($product['sku']) : ''),
                'stok_akhir' => $stock,
                'tanggal' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function formatDimension(array $product, ?array $existing): string
    {
        $length = $product['length'] ?? null;
        $width = $product['width'] ?? null;
        $height = $product['height'] ?? null;

        if ($length !== null || $width !== null || $height !== null) {
            return trim((string)($length ?? 0)) . ' x ' . trim((string)($width ?? 0)) . ' x ' . trim((string)($height ?? 0));
        }

        return (string)($existing['dimensi'] ?? '');
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        return trim((string)$text, '-');
    }

    private function plainText(string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
    }

    private function normalizeNameKey(string $value): string
    {
        $value = strtoupper($value);
        $value = str_replace(['SAEMAS', 'SEAMAS'], 'SAEMAS', $value);
        return preg_replace('/[^A-Z0-9]+/', '', $value) ?? '';
    }

    private function clean($value): string
    {
        return trim(preg_replace('/\s+/', ' ', (string)$value) ?? '');
    }
}
