<?php

namespace App\Services;

/** Canonical interpretation of Lunarea's legacy product variant and stock vectors. */
final class ProductVariantStock
{
    /** Decode legacy variants and expose the same single-option fallback used by checkout. */
    public static function variants($value): array
    {
        if (is_string($value)) $value = json_decode($value, true);
        if (!is_array($value) || $value === []) return ['Default'];
        $variants = array_values($value);
        $names = array_map([self::class, 'name'], $variants);
        return count(array_filter($names, static fn(string $name): bool => $name !== '')) === 0
            ? ['Default'] : $variants;
    }

    public static function name($variant): string
    {
        if (is_array($variant)) return trim((string)($variant['nama'] ?? $variant['name'] ?? $variant['varian'] ?? ''));
        return trim((string)$variant);
    }

    public static function names($variants): array
    {
        return array_map([self::class, 'name'], self::variants($variants));
    }

    public static function stockForIndex($variants, string $stockVector, int $index): int
    {
        $variants = self::variants($variants);
        if ($index < 0 || $index >= count($variants)) return 0;
        $stocks = array_map('trim', explode(',', $stockVector));
        $stockIndex = count($variants) > count($stocks) ? 0 : $index;
        $value = $stocks[$stockIndex] ?? '';
        return preg_match('/^-?\d+$/D', $value) ? max(0, (int)$value) : 0;
    }

    public static function totalStock($variants, string $stockVector): int
    {
        $variants = self::variants($variants);
        $stocks = array_map('trim', explode(',', $stockVector));
        if (count($variants) > count($stocks)) return self::stockForIndex($variants, $stockVector, 0);
        $total = 0;
        foreach (array_keys($variants) as $index) $total += self::stockForIndex($variants, $stockVector, $index);
        return $total;
    }
}
