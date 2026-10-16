<?php

namespace App\Services\Integrations;

/** Additive metadata only: local product IDs and historical order snapshots stay intact. */
final class LunaProductIdentity
{
    public const PREFIX = 'luna_product_identity_';

    public static function fromProduct(array $product): array
    {
        $id = (string)($product['id'] ?? '');
        if (!self::uuid($id)) throw new \DomainException('ID master produk MyLuna tidak valid.');
        $variants = [];
        $ids = [];
        foreach (($product['variants'] ?? $product['variantItems'] ?? []) as $variant) {
            $name = trim(preg_replace('/\s+/u', ' ', (string)($variant['value'] ?? $variant['name'] ?? '')) ?? '');
            $variantId = (string)($variant['id'] ?? '');
            if ($name === '' || !self::uuid($variantId) || isset($variants[$name]) || isset($ids[$variantId])) {
                throw new \DomainException('Varian MyLuna harus memiliki nama dan ID yang tunggal.');
            }
            $variants[$name] = $variantId;
            $ids[$variantId] = true;
        }
        return ['version'=>1, 'product_id'=>$id, 'variants'=>$variants];
    }

    public static function enrich(array $item, ?array $identity, string $variant): array
    {
        if ($identity === null) return $item; // Legacy import must still pass strict MyLuna mapping.
        if (($identity['version'] ?? null) !== 1 || !self::uuid($identity['product_id'] ?? '')) {
            throw new \DomainException('Pemetaan produk MyLuna perlu diperiksa admin.');
        }
        $variants = $identity['variants'] ?? null;
        if (!is_array($variants)) throw new \DomainException('Pemetaan varian MyLuna tidak valid.');
        $variantId = $variants[$variant] ?? null;
        if ($variants && !self::uuid($variantId ?? '')) throw new \DomainException('Varian pesanan belum terhubung ke MyLuna.');
        $item['luna_product_id'] = $identity['product_id'];
        $item['luna_variant_id'] = $variantId;
        return $item;
    }

    public static function uuid($value): bool
    {
        return is_string($value) && preg_match('/^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$/iD', $value) === 1;
    }
}
