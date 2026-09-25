<?php

namespace App\Models;

use CodeIgniter\Model;

class KonstantaModel extends Model
{
    protected $table = 'konstanta';
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
