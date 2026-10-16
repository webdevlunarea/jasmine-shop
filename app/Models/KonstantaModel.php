<?php

namespace App\Models;

use CodeIgniter\Model;

class KonstantaModel extends Model
{
    protected $table = 'konstanta';
    private ?int $lunaIdentityCapacity = null;
    protected $allowedFields = [
        'label',
        'value',
    ];

    public function getKonstantaById($id)
    {
        return $this->where(['id' => $id])->first();
    }

    public function getKonstantaByLabel($label)
    {
        return $this->where(['label' => $label])->first();
    }

    public function getLunaProductIdentity(string $websiteId): ?array
    {
        $rows = $this->where('label', \App\Services\Integrations\LunaProductIdentity::PREFIX . $websiteId)->findAll();
        if (!$rows) return null;
        if (count($rows) !== 1) throw new \DomainException('Pemetaan MyLuna duplikat; perlu pemeriksaan admin.');
        $identity = json_decode((string)$rows[0]['value'], true);
        if (!is_array($identity)) throw new \DomainException('Pemetaan MyLuna tersimpan tidak valid.');
        return $identity;
    }

    /** Caller holds the catalog advisory lock and transaction. No schema/primary-key changes. */
    public function saveLunaProductIdentity(string $websiteId, array $identity, bool $dryRun = false): void
    {
        $prefix = \App\Services\Integrations\LunaProductIdentity::PREFIX;
        $rows = $this->like('label', $prefix, 'after')->findAll();
        $existing = null;
        foreach ($rows as $row) {
            $saved = json_decode((string)$row['value'], true);
            if (!is_array($saved)) throw new \DomainException('Pemetaan MyLuna perlu pemeriksaan admin.');
            if ($row['label'] === $prefix . $websiteId) {
                if ($existing || ($saved['product_id'] ?? '') !== $identity['product_id']) throw new \DomainException('Identitas produk lama bertentangan; perubahan otomatis dihentikan.');
                $existing = $row;
            } elseif (($saved['product_id'] ?? '') === $identity['product_id']) {
                throw new \DomainException('Satu produk MyLuna sudah terhubung ke produk website lain.');
            }
        }
        $json = \App\Services\Integrations\LunaCatalogSchema::metadata($websiteId, $identity, $this->lunaIdentityStorageCapacity());
        if ($dryRun) return;
        $ok = $existing ? $this->update($existing['id'], ['value'=>$json]) : $this->insert(['label'=>$prefix . $websiteId, 'value'=>$json]);
        if (!$ok || $this->getLunaProductIdentity($websiteId) !== $identity) throw new \DomainException('Penyimpanan pemetaan MyLuna gagal diverifikasi.');
    }

    /** Inspect the actual schema, never assume the website metadata column was widened. */
    private function lunaIdentityStorageCapacity(): int
    {
        if ($this->lunaIdentityCapacity !== null) return $this->lunaIdentityCapacity;
        $column = $this->db->query(
            'SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$this->db->prefixTable($this->table), 'value']
        )->getRowArray();
        if (!$column || !in_array(strtolower((string)$column['DATA_TYPE']), ['varchar', 'text', 'mediumtext', 'longtext'], true)
            || !is_numeric($column['CHARACTER_MAXIMUM_LENGTH']) || (int)$column['CHARACTER_MAXIMUM_LENGTH'] < 1) {
            throw new \DomainException('Schema penyimpanan metadata MyLuna belum terverifikasi.');
        }
        return $this->lunaIdentityCapacity = min(60000, (int)$column['CHARACTER_MAXIMUM_LENGTH']);
    }

    public static function defaultThemeWarna()
    {
        return [
            'primary' => '#243B6B',
            'primaryHover' => '#1A2C52',
            'soft' => '#FAF7F2',
            'soft1' => '#F3E8DF',
            'soft2' => '#E7D7C9',
            'accent' => '#D98C9A',
            'background' => '#F7F0E8',
            'sidebar' => '#1F2A44',
        ];
    }


    public static function defaultTopPromoTexts()
    {
        return [
            'desktop' => 'Dapatkan harga khusus pembelian pertama | Gratis ongkir hingga 100%',
            'mobile' => 'Harga khusus pembelian pertama plus gratis ongkir',
        ];
    }

    public static function defaultFlashSaleSettings()
    {
        return [
            'enabled' => true,
            'mode' => 'auto',
            'title' => 'Flash Sale Lunarea',
            'subtitle' => 'Harga spesial untuk produk pilihan. Buruan sebelum waktu habis.',
            'kicker' => 'Promo kilat',
            'end_time' => '23:59',
            'limit' => 12,
            'product_ids' => [],
        ];
    }

    public static function defaultCategoryImages()
    {
        return [
            'all' => '/img/logo icon.png',
            'lemari-dewasa' => '/img/logokategori/Lemari_Dewasa.webp',
            'lemari-anak' => '/img/logokategori/Lemari_Anak.webp',
            'meja-rias' => '/img/logokategori/Meja_Rias.webp',
            'meja-belajar' => '/img/logokategori/Meja_Belajar.webp',
            'meja-tv' => '/img/logokategori/Meja_TV.webp',
            'meja-tulis' => '/img/logokategori/Meja_Tulis.webp',
            'meja-komputer' => '/img/logokategori/Meja_Komputer.webp',
            'rak-serbaguna' => '/img/logokategori/Rak_Serbaguna.webp',
            'rak-sepatu' => '/img/logokategori/Rak_Sepatu.webp',
            'rak-besi' => '/img/logokategori/Rak_Besi.webp',
            'kursi' => '/img/logokategori/Kursi.webp',
        ];
    }

    public function getCategoryImages()
    {
        $defaults = self::defaultCategoryImages();

        foreach ($defaults as $key => $value) {
            $row = $this->getKonstantaByLabel('category_image_' . $key);
            if ($row && !empty($row['value']) && is_string($row['value'])) {
                $defaults[$key] = $row['value'];
            }
        }

        $row = $this->getKonstantaByLabel('category_images');
        if ($row && !empty($row['value'])) {
            $decoded = json_decode($row['value'], true);
            if (is_array($decoded)) {
                foreach ($defaults as $key => $value) {
                    $singleRow = $this->getKonstantaByLabel('category_image_' . $key);
                    if (!$singleRow && !empty($decoded[$key]) && is_string($decoded[$key])) {
                        $defaults[$key] = $decoded[$key];
                    }
                }
            }
        }
        return $defaults;
    }

    public function saveCategoryImages($images)
    {
        $clean = self::defaultCategoryImages();
        foreach ($clean as $key => $default) {
            if (!empty($images[$key]) && is_string($images[$key])) {
                $clean[$key] = $images[$key];
            }
        }

        foreach ($clean as $key => $value) {
            $label = 'category_image_' . $key;
            $row = $this->getKonstantaByLabel($label);
            if ($row) {
                $this->where(['id' => $row['id']])->set(['value' => $value])->update();
            } else {
                $this->insert(['label' => $label, 'value' => $value]);
            }
        }

        return $clean;
    }

    public function getProductVariantImageMap($productId)
    {
        $row = $this->getKonstantaByLabel('product_variant_image_map_' . $productId);
        if (!$row || empty($row['value'])) return [];
        $decoded = json_decode($row['value'], true);
        return is_array($decoded) ? $decoded : [];
    }

    public function saveProductVariantImageMap($productId, array $map)
    {
        $clean = [];
        foreach ($map as $variant => $imageIndex) {
            $variant = trim((string) $variant);
            $imageIndex = max(0, (int) $imageIndex);
            if ($variant !== '') $clean[$variant] = $imageIndex;
        }

        $label = 'product_variant_image_map_' . $productId;
        $payload = json_encode($clean, JSON_UNESCAPED_UNICODE);
        $row = $this->getKonstantaByLabel($label);

        if ($row) {
            $this->where(['id' => $row['id']])->set(['value' => $payload])->update();
        } else {
            $this->insert([
                'label' => $label,
                'value' => $payload,
            ]);
        }

        return $clean;
    }

    public function getTopPromoTexts()
    {
        $defaults = self::defaultTopPromoTexts();
        $row = $this->getKonstantaByLabel('top_promo_text');

        if ($row && !empty($row['value'])) {
            $decoded = json_decode($row['value'], true);
            if (is_array($decoded)) {
                foreach ($defaults as $key => $value) {
                    if (isset($decoded[$key]) && trim((string) $decoded[$key]) !== '') {
                        $defaults[$key] = trim((string) $decoded[$key]);
                    }
                }
            }
        }

        return $defaults;
    }

    public function saveTopPromoTexts($texts)
    {
        $defaults = self::defaultTopPromoTexts();
        $clean = [];

        foreach ($defaults as $key => $value) {
            $text = trim((string) ($texts[$key] ?? ''));
            $clean[$key] = $text !== '' ? mb_substr(strip_tags($text), 0, 160) : $value;
        }

        $row = $this->getKonstantaByLabel('top_promo_text');
        $payload = json_encode($clean, JSON_UNESCAPED_UNICODE);

        if ($row) {
            $this->where(['id' => $row['id']])->set(['value' => $payload])->update();
        } else {
            $this->insert([
                'label' => 'top_promo_text',
                'value' => $payload,
            ]);
        }

        return $clean;
    }

    public function getFlashSaleSettings()
    {
        $defaults = self::defaultFlashSaleSettings();
        $row = $this->where(['label' => 'flash_sale_settings'])
            ->orderBy('id', 'DESC')
            ->first();

        if ($row && !empty($row['value'])) {
            $decoded = json_decode($row['value'], true);
            if (is_array($decoded)) {
                $defaults = array_merge($defaults, array_intersect_key($decoded, $defaults));
            }
        }

        $defaults['enabled'] = filter_var($defaults['enabled'], FILTER_VALIDATE_BOOLEAN);
        $defaults['mode'] = in_array($defaults['mode'], ['auto', 'manual'], true) ? $defaults['mode'] : 'auto';
        $defaults['title'] = trim((string)$defaults['title']) !== '' ? trim((string)$defaults['title']) : 'Flash Sale Lunarea';
        $defaults['subtitle'] = trim((string)$defaults['subtitle']) !== '' ? trim((string)$defaults['subtitle']) : self::defaultFlashSaleSettings()['subtitle'];
        $defaults['kicker'] = trim((string)$defaults['kicker']) !== '' ? trim((string)$defaults['kicker']) : 'Promo kilat';
        $defaults['end_time'] = preg_match('/^\d{2}:\d{2}$/', (string)$defaults['end_time']) ? $defaults['end_time'] : '23:59';
        $defaults['product_ids'] = array_slice(array_values(array_unique(array_filter(array_map('trim', (array)$defaults['product_ids'])))), 0, 24);
        $defaults['limit'] = $defaults['mode'] === 'manual'
            ? min(24, count($defaults['product_ids']))
            : min(24, max(4, (int)$defaults['limit']));

        return $defaults;
    }

    public function saveFlashSaleSettings(array $settings)
    {
        $defaults = self::defaultFlashSaleSettings();
        $mode = (($settings['mode'] ?? 'auto') === 'manual') ? 'manual' : 'auto';
        $productIds = array_slice(array_values(array_unique(array_filter(array_map('trim', (array)($settings['product_ids'] ?? []))))), 0, 24);
        $limit = min(24, max(4, (int)($settings['limit'] ?? $defaults['limit'])));
        if ($mode === 'manual') {
            $limit = min(24, count($productIds));
        }

        $clean = [
            'enabled' => isset($settings['enabled']),
            'mode' => $mode,
            'title' => mb_substr(trim(strip_tags((string)($settings['title'] ?? $defaults['title']))), 0, 80),
            'subtitle' => mb_substr(trim(strip_tags((string)($settings['subtitle'] ?? $defaults['subtitle']))), 0, 180),
            'kicker' => mb_substr(trim(strip_tags((string)($settings['kicker'] ?? $defaults['kicker']))), 0, 40),
            'end_time' => preg_match('/^\d{2}:\d{2}$/', (string)($settings['end_time'] ?? '')) ? $settings['end_time'] : $defaults['end_time'],
            'limit' => $limit,
            'product_ids' => $productIds,
        ];

        if ($clean['title'] === '') $clean['title'] = $defaults['title'];
        if ($clean['subtitle'] === '') $clean['subtitle'] = $defaults['subtitle'];
        if ($clean['kicker'] === '') $clean['kicker'] = $defaults['kicker'];

        $payload = json_encode($clean, JSON_UNESCAPED_UNICODE);
        $rows = $this->where(['label' => 'flash_sale_settings'])->findAll();

        if (!empty($rows)) {
            $ids = array_column($rows, 'id');
            $this->whereIn('id', $ids)->set(['value' => $payload])->update();
        } else {
            $this->insert([
                'label' => 'flash_sale_settings',
                'value' => $payload,
            ]);
        }

        return $clean;
    }

    public function getThemeWarna()
    {
        $defaults = self::defaultThemeWarna();

        $jsonRow = $this->getKonstantaByLabel('theme_warna');
        if ($jsonRow && !empty($jsonRow['value'])) {
            $decoded = json_decode($jsonRow['value'], true);
            if (is_array($decoded)) {
                foreach ($defaults as $key => $value) {
                    if (isset($decoded[$key]) && $this->isHexColor($decoded[$key])) {
                        $defaults[$key] = strtoupper($decoded[$key]);
                    }
                }
            }
        }

        $labels = [];
        foreach (array_keys($defaults) as $key) {
            $labels[] = $this->themeLabel($key);
        }

        $rows = $this->whereIn('label', $labels)->findAll();
        foreach ($rows as $row) {
            $key = str_replace('theme_warna_', '', $row['label']);
            if (array_key_exists($key, $defaults) && $this->isHexColor($row['value'])) {
                $defaults[$key] = strtoupper($row['value']);
            }
        }

        return $defaults;
    }

    public function saveThemeWarna($colors)
    {
        $theme = self::defaultThemeWarna();
        foreach ($theme as $key => $value) {
            if (isset($colors[$key]) && $this->isHexColor($colors[$key])) {
                $theme[$key] = strtoupper($colors[$key]);
            }
        }

        foreach ($theme as $key => $value) {
            $label = $this->themeLabel($key);
            $row = $this->getKonstantaByLabel($label);

            if ($row) {
                $this->where(['id' => $row['id']])->set(['value' => $value])->update();
            } else {
                $this->insert([
                    'label' => $label,
                    'value' => $value,
                ]);
            }
        }

        return $theme;
    }

    private function isHexColor($color)
    {
        return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
    }

    private function themeLabel($key)
    {
        return 'theme_warna_' . $key;
    }
}
