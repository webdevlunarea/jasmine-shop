<?php

namespace App\Services\Payments;

use CodeIgniter\Database\BaseConnection;
use App\Services\ProductVariantStock;
use DomainException;
use RuntimeException;

/** Reserves cart stock and persists its order shell in one database transaction. */
final class InventoryOrderReservation
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * `$persist` must persist the matching order row using the supplied connection.
     * Gateway network requests must happen only after this transaction commits.
     */
    public function reserveAndPersist(array $items, callable $persist, ?callable $validate = null, bool $reserveInventory = true)
    {
        if (!$items || count($items) > 100 || !$this->db->tableExists('barang')) {
            throw new DomainException('Isi pesanan tidak dapat diproses.');
        }
        if (!$this->db->transBegin()) throw new RuntimeException('Transaksi reservasi pesanan tidak dapat dimulai.');
        try {
            if ($validate !== null) $validate($this->db);
            if ($reserveInventory) {
                foreach ($items as $item) $this->reserveItem($item);
            }
            $result = $persist($this->db);
            if ($result === false) throw new RuntimeException('Penyimpanan pesanan gagal.');
            if ($this->db->transStatus() === false) throw new RuntimeException('Penyimpanan pesanan gagal.');
            if (!$this->db->transCommit()) throw new RuntimeException('Transaksi reservasi pesanan gagal dikomit.');
            return $result;
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $e;
        }
    }

    private function reserveItem($item): void
    {
        if (!is_array($item)) throw new DomainException('Isi pesanan tidak valid.');
        $id = trim((string)($item['product_id'] ?? $item['id'] ?? ''));
        $variant = trim((string)($item['variant'] ?? ''));
        $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);
        if ($id === '' || $variant === '' || $quantity === false || $quantity < 1 || $quantity > 99) {
            throw new DomainException('Isi pesanan tidak valid.');
        }

        $builder = $this->db->table('barang')->select('id,active,stok,varian')->where('id', $id);
        $sql = $builder->getCompiledSelect();
        $product = $this->db->query($sql . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
        if (!$product || (string)$product['active'] !== '1') throw new DomainException('Salah satu produk sudah tidak tersedia.');

        $variants = ProductVariantStock::variants((string)$product['varian']);
        $names = ProductVariantStock::names($variants);
        $variantIndex = array_search($variant, $names, true);
        if ($variantIndex === false) throw new DomainException('Varian pesanan sudah berubah.');
        $stocks = array_map('trim', explode(',', (string)$product['stok']));
        $stockIndex = count($names) > count($stocks) ? 0 : $variantIndex;
        if (!array_key_exists($stockIndex, $stocks) || ProductVariantStock::stockForIndex($variants, (string)$product['stok'], (int)$variantIndex) < $quantity) {
            throw new DomainException('Stok salah satu produk berubah. Perbarui keranjang.');
        }
        $stocks[$stockIndex] = (string)((int)$stocks[$stockIndex] - $quantity);
        if (!$this->db->table('barang')->where('id', $id)->update(['stok' => implode(',', $stocks)])) {
            throw new RuntimeException('Reservasi stok gagal.');
        }
    }

    public static function variantName($variant): string
    {
        if (is_array($variant)) return trim((string)($variant['nama'] ?? $variant['name'] ?? $variant['varian'] ?? ''));
        return trim((string)$variant);
    }
}
