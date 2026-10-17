<?php

namespace App\Services\Integrations;

/** Project a MyLuna stock snapshot into the existing Lunarea variant order. */
final class LunaProductStockSync
{
    public static function project(array $product, array $identity, array $website): array
    {
        if (!LunaProductIdentity::uuid($product['id'] ?? null)
            || ($identity['version'] ?? null) !== 1
            || ($identity['product_id'] ?? null) !== $product['id']) {
            throw new \DomainException('Identitas master MyLuna untuk sinkron stok tidak cocok.');
        }
        $stock = $product['stock'] ?? null;
        if (!is_int($stock) || $stock < 0) throw new \DomainException('Stok master MyLuna tidak valid.');
        $localNames = json_decode((string)($website['varian'] ?? ''), true);
        if (!is_array($localNames) || array_values($localNames) !== $localNames) throw new \DomainException('Urutan varian Lunarea perlu diperiksa.');
        $localMap = $identity['variants'] ?? null;
        $remoteVariants = $product['variants'] ?? null;
        if (!is_array($localMap) || !is_array($remoteVariants)) throw new \DomainException('Pemetaan varian stok tidak valid.');

        if (!$remoteVariants) {
            if ($localMap || ($localNames && $localNames !== ['Default'])) throw new \DomainException('Struktur varian MyLuna berubah; stok tidak diselaraskan.');
            return ['nama'=>(string)($website['nama'] ?? ''), 'varian'=>(string)($website['varian'] ?? '[]'), 'stok'=>(string)$stock];
        }
        if (!$localNames || count($localMap) !== count($localNames)) throw new \DomainException('Pemetaan seluruh varian diperlukan sebelum sinkron stok.');

        $remote = [];
        foreach ($remoteVariants as $variant) {
            if (!is_array($variant) || !LunaProductIdentity::uuid($variant['id'] ?? null)
                || !is_int($variant['stock'] ?? null) || $variant['stock'] < 0 || isset($remote[$variant['id']])) {
                throw new \DomainException('Data stok varian MyLuna tidak valid.');
            }
            $remote[$variant['id']] = $variant['stock'];
        }
        $ordered = []; $used = [];
        foreach ($localNames as $name) {
            if (!is_string($name) || !isset($localMap[$name])) throw new \DomainException('Pemetaan nama varian Lunarea berubah.');
            $id = $localMap[$name];
            if (!LunaProductIdentity::uuid($id) || !array_key_exists($id, $remote) || isset($used[$id])) throw new \DomainException('ID varian MyLuna tidak lengkap atau duplikat.');
            $used[$id] = true;
            $ordered[] = $remote[$id];
        }
        if (count($used) !== count($remote)) throw new \DomainException('Jumlah varian berubah di MyLuna; tinjau ulang pemetaan.');
        return ['nama'=>(string)($website['nama'] ?? ''), 'varian'=>(string)$website['varian'], 'stok'=>implode(',', $ordered)];
    }
}
