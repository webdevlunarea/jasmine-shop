<?php

namespace App\Controllers\Api\V1;

use App\Models\BarangModel;
use App\Models\PembeliModel;
use App\Services\CustomerCartStore;
use App\Services\ProductVariantStock;
use Config\Database;

class CartController extends ApiController
{
    public function index()
    {
        $state = $this->state();
        if (!$state) return $this->failure('CUSTOMER_NOT_FOUND', 'Data akun tidak tersedia.', 404);
        return $this->success(['cart' => $this->present($state['cart'])]);
    }

    public function add()
    {
        $input = $this->payload();
        $productId = trim((string)($input['product_id'] ?? ''));
        $variant = trim((string)($input['variant'] ?? ''));
        $quantity = (int)($input['quantity'] ?? 1);
        if ($productId === '' || mb_strlen($productId) > 30 || mb_strlen($variant) > 100 || $quantity < 1 || $quantity > 99) {
            return $this->failure('VALIDATION_ERROR', 'Produk, varian, atau jumlah belum valid.', 422);
        }
        $state = $this->state();
        if (!$state) return $this->failure('CUSTOMER_NOT_FOUND', 'Data akun tidak tersedia.', 404);
        $index = $this->findItem($state['cart'], $productId, $variant);
        $nextQuantity = $quantity + ($index === null ? 0 : (int)$state['cart'][$index]['jumlah']);
        $product = $this->availableProduct($productId, $variant, $nextQuantity);
        if (!$product) return $this->failure('PRODUCT_UNAVAILABLE', 'Produk atau varian sudah tidak tersedia.', 422);
        if ($nextQuantity > $product['stock']) return $this->failure('INSUFFICIENT_STOCK', 'Jumlah melebihi stok yang tersedia.', 422);
        if ($index === null) $state['cart'][] = ['id' => $productId, 'jumlah' => $quantity, 'varian' => $variant, 'index_gambar' => 1];
        else $state['cart'][$index]['jumlah'] = $nextQuantity;
        return $this->saveCart($state['email'], $state['raw_cart'], $state['cart']);
    }

    public function update()
    {
        $input = $this->payload();
        $productId = trim((string)($input['product_id'] ?? ''));
        $variant = trim((string)($input['variant'] ?? ''));
        $quantity = (int)($input['quantity'] ?? 0);
        if ($quantity < 1 || $quantity > 99) return $this->failure('VALIDATION_ERROR', 'Jumlah harus antara 1 sampai 99.', 422);
        $state = $this->state();
        if (!$state) return $this->failure('CUSTOMER_NOT_FOUND', 'Data akun tidak tersedia.', 404);
        $index = $this->findItem($state['cart'], $productId, $variant);
        if ($index === null) return $this->failure('CART_ITEM_NOT_FOUND', 'Item keranjang tidak ditemukan.', 404);
        $product = $this->availableProduct($productId, $variant, $quantity);
        if (!$product || $quantity > $product['stock']) return $this->failure('INSUFFICIENT_STOCK', 'Produk tidak tersedia dalam jumlah tersebut.', 422);
        $state['cart'][$index]['jumlah'] = $quantity;
        return $this->saveCart($state['email'], $state['raw_cart'], $state['cart']);
    }

    public function delete()
    {
        $input = $this->payload();
        $productId = trim((string)($input['product_id'] ?? ''));
        $variant = trim((string)($input['variant'] ?? ''));
        $state = $this->state();
        if (!$state) return $this->failure('CUSTOMER_NOT_FOUND', 'Data akun tidak tersedia.', 404);
        $index = $this->findItem($state['cart'], $productId, $variant);
        if ($index === null) return $this->failure('CART_ITEM_NOT_FOUND', 'Item keranjang tidak ditemukan.', 404);
        array_splice($state['cart'], $index, 1);
        return $this->saveCart($state['email'], $state['raw_cart'], $state['cart']);
    }

    private function state(): ?array
    {
        $email = $this->authEmail();
        $customer = (new PembeliModel())->getPembeli($email);
        if (!$customer) return null;
        $rawCart = (string)($customer['keranjang'] ?? '[]');
        $cart = json_decode($rawCart, true);
        return ['email' => $email, 'raw_cart' => $rawCart, 'cart' => is_array($cart) ? array_values($cart) : []];
    }

    private function saveCart(string $email, string $expectedRawCart, array $cart)
    {
        try {
            $saved = (new CustomerCartStore(Database::connect()))->saveIfUnchanged($email, $expectedRawCart, $cart);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API cart persistence failed: ' . get_class($e));
            return $this->failure('CART_SAVE_FAILED', 'Keranjang belum tersimpan. Coba lagi.', 500);
        }
        if (!$saved) return $this->failure('CART_CHANGED_RETRY', 'Keranjang berubah di perangkat lain. Muat ulang lalu coba lagi.', 409);
        return $this->success(['cart' => $this->present($cart)]);
    }

    private function findItem(array $cart, string $id, string $variant): ?int
    {
        foreach ($cart as $index => $item) {
            if ((string)($item['id'] ?? '') === $id && (string)($item['varian'] ?? '') === $variant) return $index;
        }
        return null;
    }

    private function availableProduct(string $id, string $variant, int $quantity): ?array
    {
        $row = (new BarangModel())->select('id,nama,harga,diskon,stok,varian,active')->where(['id' => $id, 'active' => '1'])->first();
        if (!$row) return null;
        $variants = ProductVariantStock::variants((string)$row['varian']);
        $variantNames = ProductVariantStock::names($variants);
        $variantIndex = array_search($variant, $variantNames, true);
        if ($variantIndex === false) return null;
        $stock = ProductVariantStock::stockForIndex($variants, (string)$row['stok'], (int)$variantIndex);
        if ($stock < $quantity) return ['stock' => $stock];
        return ['stock' => $stock, 'row' => $row];
    }

    private function present(array $cart): array
    {
        $items = [];
        $subtotal = 0;
        foreach ($cart as $item) {
            $id = (string)($item['id'] ?? '');
            $qty = max(1, (int)($item['jumlah'] ?? 1));
            $row = (new BarangModel())->select('id,nama,path,harga,diskon,stok,varian')->where(['id' => $id, 'active' => '1'])->first();
            if (!$row) continue;
            $variants = ProductVariantStock::variants((string)$row['varian']);
            $names = ProductVariantStock::names($variants);
            $variant = (string)($item['varian'] ?? '');
            $index = array_search($variant, $names, true);
            if ($index === false) continue;
            $stock = ProductVariantStock::stockForIndex($variants, (string)$row['stok'], (int)$index);
            $priceDigits = preg_replace('/[^0-9]/', '', (string)$row['harga']);
            $basePrice = (int)($priceDigits ?: 0);
            $price = (int)round($basePrice * (100 - max(0, (int)$row['diskon'])) / 100);
            $subtotal += $price * $qty;
            $items[] = [
                'product_id' => $id, 'name' => (string)$row['nama'], 'slug' => (string)$row['path'],
                'variant' => $variant, 'quantity' => $qty, 'available_stock' => $stock,
                'unit_price' => $price, 'discount_percent' => (int)$row['diskon'],
                'image_url' => base_url('imgpic/' . rawurlencode($id)),
            ];
        }
        return ['items' => $items, 'subtotal' => $subtotal, 'currency' => 'IDR'];
    }
}
