<?php

namespace App\Services\Integrations;

/** Conservative limits verified from the production schema supplied by the operator.
 * Reject oversize input; never shorten identifiers, labels or product data to fit.
 */
final class LunaCatalogSchema
{
    public static function product(string $websiteId, array $data): void
    {
        self::lengths('barang', ['id'=>$websiteId] + $data, [
            'id'=>15, 'nama'=>100, 'path'=>100, 'pencarian'=>10000,
            'harga'=>20, 'stok'=>40, 'dimensi'=>50, 'kategori'=>50,
            'subkategori'=>50, 'shopee'=>255, 'tokped'=>255, 'tiktok'=>255, 'youtube'=>255,
        ]);
    }

    public static function stock(array $data, int $nameCapacity = 50): void
    {
        self::lengths('stok', $data, [
            'id'=>20, 'id_barang'=>30, 'nama'=>max(0, min($nameCapacity, 100)), 'varian'=>20,
            'email_admin'=>50, 'keterangan'=>100,
        ]);
    }

    public static function metadata(string $websiteId, array $identity, int $capacity = 255): string
    {
        self::lengths('konstanta', ['label'=>LunaProductIdentity::PREFIX . $websiteId], ['label'=>50]);
        $json = json_encode($identity, JSON_THROW_ON_ERROR);
        // ASCII JSON output: bytes equal stored character count, including escaped Unicode.
        if ($capacity < 1 || strlen($json) > min($capacity, 60000)) throw new \DomainException('Metadata identitas melampaui kapasitas konstanta.value. Perlu rencana penyimpanan terpisah sebelum aktivasi.');
        return $json;
    }

    private static function lengths(string $table, array $data, array $limits): void
    {
        foreach ($limits as $field=>$max) {
            if (array_key_exists($field, $data) && mb_strlen((string)$data[$field], 'UTF-8') > $max) {
                throw new \DomainException('Data melampaui kapasitas ' . $table . '.' . $field . ' (' . $max . ' karakter).');
            }
        }
    }
}
