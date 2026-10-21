<?php

namespace App\Controllers\Api\V1;

use App\Models\BarangModel;
use App\Models\KonstantaModel;
use App\Services\ProductVariantStock;

/** Public read-only representation of the exact promotion configured in website admin. */
final class FlashSaleController extends ApiController
{
    public function index()
    {
        try {
            $settings = (new KonstantaModel())->getFlashSaleSettings();
            $zone = new \DateTimeZone('Asia/Jakarta');
            $now = new \DateTimeImmutable('now', $zone);
            [$hour, $minute] = array_map('intval', explode(':', $settings['end_time']));
            $endsAt = $now->setTime($hour, $minute, 59);
            if ($endsAt <= $now) $endsAt = $endsAt->modify('+1 day');

            $rows = !empty($settings['enabled'])
                ? (new BarangModel())->getBarangFlashSale(
                    (int)$settings['limit'],
                    (array)$settings['product_ids'],
                    ($settings['mode'] ?? 'auto') === 'manual'
                )
                : [];

            $products = array_map(static function (array $row): array {
                $variants = ProductVariantStock::variants($row['varian'] ?? '[]');
                $names = ProductVariantStock::names($variants);
                $priceDigits = preg_replace('/[^0-9]/', '', (string)($row['harga'] ?? '0'));
                return [
                    'id' => (string)$row['id'],
                    'name' => (string)$row['nama'],
                    'slug' => (string)$row['path'],
                    'price' => max(0, (int)($priceDigits === '' ? 0 : $priceDigits)),
                    'discount_percent' => max(0, min(100, (int)($row['diskon'] ?? 0))),
                    'image_url' => base_url('imgpic/' . rawurlencode((string)$row['id'])),
                    'category' => (string)($row['kategori'] ?? ''),
                    'stock' => ProductVariantStock::totalStock($row['varian'] ?? '[]', (string)($row['stok'] ?? '')),
                    'variants' => array_map(static fn(string $name, int $index): array => [
                        'name' => $name,
                        'stock' => ProductVariantStock::stockForIndex($variants, (string)($row['stok'] ?? ''), $index),
                    ], $names, array_keys($names)),
                ];
            }, $rows);

            return $this->success(['flash_sale' => [
                'enabled' => (bool)$settings['enabled'],
                'mode' => (string)$settings['mode'],
                'title' => (string)$settings['title'],
                'subtitle' => (string)$settings['subtitle'],
                'kicker' => (string)$settings['kicker'],
                'end_time' => (string)$settings['end_time'],
                'ends_at' => $endsAt->format(DATE_ATOM),
                'remaining_seconds' => max(0, $endsAt->getTimestamp() - $now->getTimestamp()),
                'products' => $products,
            ]]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API flash-sale feed failed: ' . get_class($e));
            return $this->failure('FLASH_SALE_UNAVAILABLE', 'Informasi promo kilat belum dapat dimuat.', 503);
        }
    }
}
