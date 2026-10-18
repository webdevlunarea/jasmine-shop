<?php

namespace App\Services\Payments;

/** Runs inside the order status transaction; inventory rows are locked across different orders. */
final class ReservedStockRelease
{
    public static function release($db, array $order): void
    {
        $items = json_decode($order['items'] ?? '', true) ?: [];
        foreach ($items as $item) {
            $quantity = (int)($item['quantity'] ?? 0);
            if ($quantity <= 0) throw new \RuntimeException('Invalid stock reservation quantity');
            preg_match('/^(.*)\s+\((.*)\)$/us', (string)($item['name'] ?? ''), $parts);
            $where = !empty($item['id']) ? ['id'=>(string)$item['id']] : ['nama'=>trim($parts[1] ?? (string)($item['name'] ?? ''))];
            $sql = $db->table('barang')->where($where)->getCompiledSelect();
            $product = $db->query($sql . ($db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$product) throw new \RuntimeException('Reserved product needs review');
            $stocks = explode(',', (string)$product['stok']);
            if (count($stocks) === 1) {
                $stocks[0] = (string)((int)$stocks[0] + $quantity);
            } else {
                $variants = json_decode($product['varian'] ?? '', true) ?: [];
                $names = array_map([InventoryOrderReservation::class, 'variantName'], $variants);
                $index = array_search(trim($parts[2] ?? ''), $names, true);
                $stockIndex = count($names) > count($stocks) ? 0 : $index;
                if ($index === false || !array_key_exists($stockIndex, $stocks)) throw new \RuntimeException('Reserved variant needs review');
                $stocks[$stockIndex] = (string)((int)$stocks[$stockIndex] + $quantity);
            }
            $db->table('barang')->where('id', $product['id'])->update(['stok'=>implode(',', $stocks)]);
        }
    }
}
