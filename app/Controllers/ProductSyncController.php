<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\StokModel;
use App\Models\KonstantaModel;
use App\Services\Integrations\LunaProductIdentity;
use App\Services\Integrations\LunaCatalogSchema;

class ProductSyncController extends BaseController
{
    protected $barangModel;
    protected $stokModel;
    protected $konstantaModel;

    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->stokModel = new StokModel();
        $this->konstantaModel = new KonstantaModel();
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

        // Creating new rows requires a separately verified contract for required legacy columns.
        if ($createMissing) return $this->response->setStatusCode(422)->setJSON([
            'success'=>false, 'message'=>'Pembuatan produk otomatis belum diaktifkan untuk schema website existing. Gunakan produk website yang sudah diverifikasi.',
        ]);

        $summary = [
            'received' => count($products),
            'updated' => 0,
            'created' => 0,
            'skipped' => 0,
            'dry_run' => $dryRun,
            'items' => [],
        ];

        $db = db_connect();
        $lockName = 'lunarea_catalog_identity_' . substr(hash('sha256', $db->getDatabase()), 0, 24);
        $locked = (int)($db->query('SELECT GET_LOCK(?, 5) AS acquired', [$lockName])->getRowArray()['acquired'] ?? 0);
        if ($locked !== 1) return $this->response->setStatusCode(409)->setJSON(['success'=>false, 'message'=>'Sinkron katalog sedang berjalan. Coba kembali setelah selesai.']);
        try {
        if (!$dryRun) $db->transBegin();
        $plannedWebsiteIds = [];
        $plannedLunaIds = [];
        foreach ($products as $product) {
            if (!is_array($product)) {
                $summary['skipped']++;
                continue;
            }

            $identity = LunaProductIdentity::fromProduct($product);
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

            $websiteId = (string)($match['id'] ?? $syncData['id']);
            LunaCatalogSchema::product($websiteId, $syncData);
            if ((isset($plannedWebsiteIds[$websiteId]) && $plannedWebsiteIds[$websiteId] !== $identity['product_id'])
                || (isset($plannedLunaIds[$identity['product_id']]) && $plannedLunaIds[$identity['product_id']] !== $websiteId)) {
                throw new \DomainException('Pemetaan dalam batch bertentangan.');
            }
            $plannedWebsiteIds[$websiteId] = $identity['product_id'];
            $plannedLunaIds[$identity['product_id']] = $websiteId;
            $oldIdentity = $this->konstantaModel->getLunaProductIdentity($websiteId);
            $this->konstantaModel->saveLunaProductIdentity($websiteId, $identity, $dryRun);
            if (!$dryRun) {
                // Preserve selected images when a canonical variant is renamed.
                if ($oldIdentity) {
                    $photos = $this->konstantaModel->getProductVariantImageMap($websiteId);
                    $newPhotos = [];
                    foreach ($identity['variants'] as $name => $uuid) {
                        $oldName = array_search($uuid, $oldIdentity['variants'] ?? [], true);
                        if ($oldName !== false && isset($photos[$oldName])) $newPhotos[$name] = $photos[$oldName];
                    }
                    if ($newPhotos) $this->konstantaModel->saveProductVariantImageMap($websiteId, $newPhotos);
                }
                if ($match) {
                    if (!$this->barangModel->where(['id' => $match['id']])->set($syncData)->update()) throw new \RuntimeException('Product update failed');
                } else {
                    if (!$this->barangModel->insert($syncData)) throw new \RuntimeException('Product insert failed');
                }
            }

            if ($match) $this->recordStockChanges($match, $syncData, $product, $dryRun);
            else $this->recordInitialStock($syncData, $product, $dryRun);

            if ($match) {
                $summary['updated']++;
            } else {
                $summary['created']++;
            }

            $summary['items'][] = [
                'website_id' => $match['id'] ?? $syncData['id'],
                'luna_product_id' => $identity['product_id'],
                'sku' => $this->clean($product['sku'] ?? ''),
                'name' => $syncData['nama'] ?? '',
                'status' => $match ? 'updated' : 'created',
                'match_by' => $match['_luna_match_by'] ?? ($match ? 'unknown' : 'new_product'),
                'stock' => $syncData['stok'] ?? '',
                'price' => $syncData['harga'] ?? 0,
            ];
        }

        if (!$dryRun) {
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Catalog transaction failed');
        }
        } catch (\Throwable $error) {
            if (!$dryRun) $db->transRollback();
            log_message('error', 'Sinkron katalog MyLuna dihentikan karena validasi identitas atau penyimpanan gagal.');
            return $this->response->setStatusCode(422)->setJSON(['success'=>false, 'message'=>'Pemetaan produk/varian belum valid. Periksa ID website, ID MyLuna, dan duplikasi sebelum sinkron ulang.', 'data'=>['dry_run'=>$dryRun, 'rolled_back'=>!$dryRun]]);
        } finally {
            $db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sync produk Luna selesai',
            'data' => $summary,
        ]);
    }

    private function findWebsiteProduct(array $product): ?array
    {
        $canonicalId = (string)($product['id'] ?? '');
        $rows = $this->konstantaModel->like('label', LunaProductIdentity::PREFIX, 'after')->findAll();
        $linkedId = null;
        foreach ($rows as $row) {
            $identity = json_decode((string)$row['value'], true);
            if (($identity['product_id'] ?? '') !== $canonicalId) continue;
            $id = substr($row['label'], strlen(LunaProductIdentity::PREFIX));
            if ($linkedId !== null && $linkedId !== $id) throw new \DomainException('Pemetaan MyLuna duplikat.');
            $linkedId = $id;
        }
        $explicit = $this->clean($product['website_id'] ?? '');
        $candidates = [];
        foreach (array_unique(array_filter([$linkedId, $explicit, $this->clean($product['sku'] ?? ''), $canonicalId])) as $id) {
            $row = $this->barangModel->where('id', $id)->first();
            if ($row) $candidates[$row['id']] = $row;
        }
        if (count($candidates) > 1) throw new \DomainException('ID website dan MyLuna menunjuk produk berbeda.');
        if ($linkedId && !isset($candidates[$linkedId])) throw new \DomainException('Produk website terhubung sudah tidak tersedia.');
        if (!$candidates) return null;
        $row = array_values($candidates)[0];
        $row['_luna_match_by'] = $linkedId ? 'canonical_id' : 'explicit_id';
        return $row;
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

    private function recordStockChanges(array $existing, array $syncData, array $product, bool $dryRun = false): void
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

            $this->persistStockRow([
                'id' => random_int(1000000000, 2147483647),
                'id_barang' => $existing['id'],
                'nama' => $syncData['nama'],
                'varian' => $variantName,
                'jumlah' => (string)($currentStock - $previousStock),
                'email_admin' => 'luna-system-sync',
                'keterangan' => 'Sync otomatis dari Luna Sistem' . ($this->clean($product['sku'] ?? '') ? ' SKU: ' . $this->clean($product['sku']) : ''),
                'stok_akhir' => $currentStock,
                'tanggal' => date('Y-m-d H:i:s'),
            ], $dryRun);
        }
    }

    private function recordInitialStock(array $syncData, array $product, bool $dryRun = false): void
    {
        $variants = json_decode($syncData['varian'] ?? '[]', true);
        $stocks = array_map('intval', explode(',', (string)($syncData['stok'] ?? '')));
        foreach ($variants as $index => $variantName) {
            $stock = (int)($stocks[$index] ?? 0);
            $this->persistStockRow([
                'id' => random_int(1000000000, 2147483647),
                'id_barang' => $syncData['id'],
                'nama' => $syncData['nama'],
                'varian' => $variantName,
                'jumlah' => (string)$stock,
                'email_admin' => 'luna-system-sync',
                'keterangan' => 'Produk dibuat dari sync Luna Sistem' . ($this->clean($product['sku'] ?? '') ? ' SKU: ' . $this->clean($product['sku']) : ''),
                'stok_akhir' => $stock,
                'tanggal' => date('Y-m-d H:i:s'),
            ], $dryRun);
        }
    }

    private function persistStockRow(array $row, bool $dryRun): void
    {
        LunaCatalogSchema::stock($row, $this->stokModel->catalogNameCapacity());
        if (!$dryRun && !$this->stokModel->insert($row)) throw new \RuntimeException('Stock history insert failed');
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
