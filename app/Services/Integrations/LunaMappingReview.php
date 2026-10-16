<?php

namespace App\Services\Integrations;

/** Pure validation: selecting a pair does not rename products, variants, orders or stock. */
final class LunaMappingReview
{
    public static function variants(array $website): array
    {
        $variants = json_decode((string)($website['varian'] ?? ''), true);
        if (!is_array($variants) || array_values($variants) !== $variants || count($variants) > 200) throw new \DomainException('Daftar varian website perlu diperiksa sebelum dipetakan.');
        $seen = [];
        foreach ($variants as $name) {
            if (!is_string($name) || trim($name) === '' || isset($seen[$name])) throw new \DomainException('Nama varian website kosong atau duplikat.');
            $seen[$name] = true;
        }
        return $variants;
    }

    public static function revision(array $website, ?array $identity): string
    {
        return hash('sha256', json_encode([(string)$website['id'], (string)$website['nama'], (string)$website['varian'], (string)$website['active'], $identity], JSON_THROW_ON_ERROR));
    }

    public static function fingerprint(array $master): string
    {
        return hash('sha256', json_encode($master, JSON_THROW_ON_ERROR));
    }

    public static function identity(array $website, array $master, array $selected, ?array $existing): array
    {
        if (!LunaProductIdentity::uuid($master['id'] ?? null) || ($master['active'] ?? false) !== true) throw new \DomainException('Pilih produk MyLuna yang masih aktif.');
        foreach ($master['website_ids'] ?? [] as $id) if ($id !== $website['id']) throw new \DomainException('Produk MyLuna sudah memiliki referensi website lain.');
        $names = self::variants($website);
        $allowed = array_column($master['variants'], 'value', 'id');
        $variants = []; $used = [];
        if (!$allowed) {
            if ($selected || ($names && $names !== ['Default'])) throw new \DomainException('Produk master tanpa varian hanya cocok dengan produk website tanpa varian atau Default.');
        } else {
            if (!$names || count($selected) !== count($names)) throw new \DomainException('Pilih pasangan untuk setiap varian website.');
            foreach ($names as $i => $name) {
                $id = $selected[$i] ?? null;
                if (!is_string($id) || !isset($allowed[$id]) || isset($used[$id])) throw new \DomainException('Varian harus milik produk terpilih dan setiap pasangan harus tunggal.');
                $variants[$name] = $id; $used[$id] = true;
            }
        }
        if ($existing) {
            if (($existing['version'] ?? null) !== 1 || ($existing['product_id'] ?? '') !== $master['id'] || !is_array($existing['variants'] ?? null)) throw new \DomainException('Identitas tersimpan berbeda. Perubahan pasangan lama memerlukan rekonsiliasi terpisah.');
            foreach ($existing['variants'] as $name => $id) {
                if (!isset($allowed[$id]) || (isset($variants[$name]) && $variants[$name] !== $id)) throw new \DomainException('Pasangan varian lama dikunci agar identitas pesanan tetap konsisten.');
                $variants[$name] = $id;
            }
        }
        return ['version'=>1, 'product_id'=>$master['id'], 'variants'=>$variants];
    }
}
