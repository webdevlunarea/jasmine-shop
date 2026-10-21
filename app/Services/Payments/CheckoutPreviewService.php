<?php

namespace App\Services\Payments;

use App\Models\BarangModel;
use App\Models\CustomerAddressModel;
use App\Models\KonstantaModel;
use App\Models\PembeliModel;
use App\Models\PreorderBarangModel;
use App\Models\VoucherClaimedModel;
use App\Services\Shipping\ShippingService;
use App\Services\ProductVariantStock;
use Config\Database;
use DomainException;

/** Read-only server-authoritative checkout calculation; it never consumes points/vouchers or creates orders. */
final class CheckoutPreviewService
{
    public function preview(string $email, array $input): array
    {
        $email = strtolower(trim($email));
        $profile = (new PembeliModel())->getPembeli($email);
        if (!$profile) throw new DomainException('Data akun pelanggan tidak tersedia.');
        $cart = json_decode((string)($profile['keranjang'] ?? '[]'), true);
        if (!is_array($cart) || !$cart) throw new DomainException('Keranjang masih kosong.');
        if (count($cart) > 100) throw new DomainException('Keranjang memiliki terlalu banyak item untuk diproses.');

        $addressRow = (new CustomerAddressModel())->owned($email, (int)($input['address_id'] ?? 0));
        if (!$addressRow) throw new DomainException('Alamat pengiriman tidak ditemukan.');
        $addressData = json_decode((string)$addressRow['address_data'], true);
        if (!is_array($addressData)) throw new DomainException('Data alamat perlu diperbarui.');

        $productModel = new BarangModel();
        $products = [];
        $items = [];
        $subtotal = 0;
        $glassTotal = 0;
        $weightKg = 0.0;
        $preorders = (new PreorderBarangModel())->where('email_customer', $email)->findAll();
        $preorderDiscount = 0;

        foreach ($cart as $cartItem) {
            $id = trim((string)($cartItem['id'] ?? ''));
            $variant = trim((string)($cartItem['varian'] ?? ''));
            $quantity = (int)($cartItem['jumlah'] ?? 0);
            if ($id === '' || $quantity < 1 || $quantity > 99) throw new DomainException('Keranjang memiliki item yang perlu diperiksa.');
            $product = $productModel->select('id,nama,path,harga,diskon,stok,varian,berat,dimensi,kaca,active')->where(['id' => $id, 'active' => '1'])->first();
            if (!$product) throw new DomainException('Salah satu produk sudah tidak tersedia.');
            $productWeightKg = (float)str_replace(',', '.', (string)($product['berat'] ?? 0));
            if (!is_finite($productWeightKg) || $productWeightKg <= 0) {
                throw new DomainException('Berat salah satu produk belum tersedia. Hubungi admin sebelum melanjutkan checkout.');
            }
            $variants = ProductVariantStock::variants((string)$product['varian']);
            $variantNames = ProductVariantStock::names($variants);
            $variantIndex = array_search($variant, $variantNames, true);
            if ($variantIndex === false) throw new DomainException('Varian produk di keranjang sudah berubah.');
            $stock = ProductVariantStock::stockForIndex($variants, (string)$product['stok'], (int)$variantIndex);
            if ($quantity > $stock) throw new DomainException('Stok salah satu produk berubah. Perbarui keranjang.');

            // Match the existing Pages::actionPayCore() price arithmetic exactly.
            $unitPrice = (int)round(((100 - (int)$product['diskon']) / 100) * (int)$product['harga']);
            $lineTotal = $unitPrice * $quantity;
            $subtotal += $lineTotal;
            $weightKg += $productWeightKg * $quantity;
            if (!empty($product['kaca'])) $glassTotal += $quantity * 25000;
            foreach ($preorders as $preorder) {
                if ((string)$preorder['id_barang'] === $id) $preorderDiscount += ((int)$product['harga'] - (int)$preorder['harga']) * $quantity;
            }
            $items[] = [
                'product_id' => $id, 'name' => (string)$product['nama'], 'variant' => $variant,
                'quantity' => $quantity, 'unit_price' => $unitPrice, 'line_total' => $lineTotal,
                'stock_available' => $stock, 'weight_kg' => $productWeightKg,
                'dimensions' => (string)$product['dimensi'], 'image_url' => base_url('imgpic/' . rawurlencode($id)),
            ];
            $products[] = [
                'id' => $id, 'name' => (string)$product['nama'], 'variant' => $variant,
                'unit_price' => $unitPrice, 'quantity' => $quantity,
            ];
        }

        $voucher = null;
        $voucherDiscount = 0;
        $cashback = 0;
        $voucherId = (int)($input['voucher_claim_id'] ?? 0);
        if ($voucherId > 0) {
            $voucher = (new VoucherClaimedModel())->getVoucher($voucherId);
            if (!$voucher || strtolower((string)$voucher['email_user']) !== $email || empty($voucher['active'])
                || (isset($voucher['voucher_active']) && (string)$voucher['voucher_active'] !== '1')
                || !$this->voucherInPeriod($voucher)) {
                throw new DomainException('Voucher tidak tersedia atau sudah kedaluwarsa.');
            }
            if (($voucher['jenis'] ?? '') === 'cashback') {
                $cashback = max(0, (int)$voucher['nominal']);
            } elseif (($voucher['satuan'] ?? '') === 'persen') {
                $voucherDiscount = max(0, (int)round(((float)$voucher['nominal'] / 100) * ($subtotal - 5000)));
                $cap = (int)($voucher['max_potongan'] ?? 0);
                if ($cap > 0) $voucherDiscount = min($voucherDiscount, $cap);
            } elseif (($voucher['satuan'] ?? '') === 'rupiah') {
                $voucherDiscount = max(0, (int)$voucher['nominal']);
            }
        }

        $pointBatches = json_decode((string)($profile['poin'] ?? '[]'), true);
        $pointBatches = is_array($pointBatches) ? $pointBatches : [];
        $today = new \DateTimeImmutable('today', new \DateTimeZone('Asia/Jakarta'));
        $activePoints = 0;
        foreach ($pointBatches as $batch) {
            if (!is_array($batch) || empty($batch['active'])) continue;
            $expiry = trim((string)($batch['kadaluarsa'] ?? ''));
            if ($expiry === '' || strtotime($expiry) === false || $today->getTimestamp() <= strtotime($expiry)) {
                $activePoints += max(0, (int)($batch['nominal'] ?? 0));
            }
        }
        $pointCap = max(0, $subtotal - $voucherDiscount - $preorderDiscount);
        $usePoints = $this->booleanFlag($input['use_points'] ?? false);
        $pointsApplied = $usePoints ? min($activePoints, $pointCap) : 0;
        $glassApplied = $this->booleanFlag($input['include_glass'] ?? false);

        $shippingCost = 0;
        $shipping = null;
        $shippingAddress = [
            'provinsi_id' => (int)($addressData['prov_id'] ?? 0), 'kabupaten_id' => (int)($addressData['kab_id'] ?? 0),
            'kecamatan_id' => (int)($addressData['kec_id'] ?? 0), 'kelurahan_id' => 0,
            'kodepos' => (string)($addressData['kodepos'] ?? ''), 'provinsi_label' => (string)($addressData['prov'] ?? ''),
            'kabupaten_label' => (string)($addressData['kab'] ?? ''), 'kecamatan_label' => (string)($addressData['kec'] ?? ''),
            'kelurahan_label' => (string)($addressData['desa'] ?? ''), 'rajaongkir_destination_id' => '',
        ];
        $db = Database::connect();
        if (!$db->tableExists('shipping_methods') || !$db->tableExists('shipping_quotes')) throw new DomainException('Pilihan pengiriman belum tersedia.');
        $province = $db->table('provinsi')->where('id', $shippingAddress['provinsi_id'])->get()->getRowArray();
        $city = $db->table('kabupaten')->where(['id' => $shippingAddress['kabupaten_id'], 'provinsi_id' => $shippingAddress['provinsi_id']])->get()->getRowArray();
        $district = $db->table('kecamatan')->where(['id' => $shippingAddress['kecamatan_id'], 'kabupaten_id' => $shippingAddress['kabupaten_id']])->get()->getRowArray();
        if (!$province || !$city || !$district) throw new DomainException('Wilayah alamat tidak valid.');
        $shippingAddress['provinsi_label'] = (string)$province['label'];
        $shippingAddress['kabupaten_label'] = (string)$city['label'];
        $shippingAddress['kecamatan_label'] = (string)$district['label'];
        $weightGrams = max(1000, (int)ceil($weightKg * 1000));
        $shippingToken = trim((string)($input['shipping_quote_token'] ?? ''));
        if ($shippingToken === '') {
            throw new DomainException('Pilih metode pengiriman terlebih dahulu.');
        }
        if ($shippingToken !== '') {
            $shipping = (new ShippingService())->validateQuoteToken($shippingToken, $email, $cart, $shippingAddress, $weightGrams);
            if (!$shipping) throw new DomainException('Pilihan pengiriman sudah kedaluwarsa atau perlu dipilih ulang.');
            $shippingCost = max(0, (int)$shipping['cost']);
        }

        $method = strtolower(trim((string)($input['payment_method'] ?? '')));
        // First-party manual transfer bypasses the gateway and has no gateway fee.
        $allowedMethods = array_merge(MidtransCheckoutRequest::supportedMethods(), ['rekening']);
        if (!in_array($method, $allowedMethods, true)) throw new DomainException('Metode pembayaran belum dipilih.');
        $tax = in_array($method, ['bca', 'bri', 'bni', 'mandiri', 'permata', 'cimb'], true)
            ? (float)((new KonstantaModel())->getKonstantaById(1)['value'] ?? 0) : 0.0;

        $lines = [];
        foreach ($products as $product) {
            $lines[] = ['id' => $product['id'], 'name' => substr($product['name'] . ' (' . ucfirst($product['variant']) . ')', 0, 50), 'price' => $product['unit_price'], 'quantity' => $product['quantity']];
        }
        if ($voucherDiscount > 0) $lines[] = ['id' => 'Diskon Voucher', 'name' => 'Diskon Voucher ' . (string)$voucher['nama'], 'price' => -$voucherDiscount, 'quantity' => 1];
        if ($preorderDiscount > 0) $lines[] = ['id' => 'Potongan Preorder', 'name' => 'Potongan Preorder', 'price' => -$preorderDiscount, 'quantity' => 1];
        if ($pointsApplied > 0) $lines[] = ['id' => 'Luna Point', 'name' => 'Luna Point', 'price' => -$pointsApplied, 'quantity' => 1];
        if ($glassApplied) $lines[] = ['id' => 'Ganti Kaca', 'name' => 'Ganti Kaca', 'price' => $glassTotal, 'quantity' => 1];
        if ($shipping) $lines[] = ['id' => 'Ongkir', 'name' => substr('Ongkir ' . ($shipping['courier_name'] ?? 'Pengiriman'), 0, 50), 'price' => $shippingCost, 'quantity' => 1];
        $beforeFee = (new CheckoutAmountCalculator())->totalFromItems($lines);
        $adminFee = (new CheckoutAmountCalculator())->adminFee($method, $beforeFee, $tax);
        $lines[] = ['id' => 'Biaya Admin', 'name' => 'Biaya Admin', 'price' => $adminFee, 'quantity' => 1];
        foreach ($lines as &$line) {
            $line['id'] = substr((string)$line['id'], 0, 64);
            $line['name'] = substr((string)$line['name'], 0, 50);
            $line['price'] = (int)round((float)$line['price']);
            $line['quantity'] = max(1, (int)$line['quantity']);
        }
        unset($line);
        $total = (new CheckoutAmountCalculator())->totalFromItems($lines);

        return [
            'currency' => 'IDR', 'payment_method' => $method, 'items' => $items,
            'cart_fingerprint' => CheckoutIdempotencyLedger::fingerprint($cart),
            'subtotal' => $subtotal, 'voucher_discount' => $voucherDiscount, 'preorder_discount' => $preorderDiscount,
            'points_available' => $activePoints, 'points_applied' => $pointsApplied,
            'voucher_claim_id' => $voucher ? (int)($voucher['id'] ?? 0) : 0,
            'voucher_id' => $voucher ? (int)($voucher['id_voucher'] ?? 0) : 0,
            'glass_service' => ['selected' => $glassApplied, 'amount' => $glassApplied ? $glassTotal : 0],
            'shipping' => $shipping ? [
                'quote_token' => $shippingToken, 'cost' => $shippingCost,
                'courier' => (string)($shipping['courier_name'] ?? ''), 'service' => (string)($shipping['service_name'] ?? ''),
                'expires_at' => $shipping['expires_at'] ?? null,
            ] : null,
            'admin_fee' => $adminFee, 'cashback_after_order' => $cashback, 'total' => $total,
            'weight_grams' => $weightGrams, 'midtrans_item_details' => $lines,
        ];
    }

    private function voucherInPeriod(array $voucher): bool
    {
        $today = new \DateTimeImmutable('today', new \DateTimeZone('Asia/Jakarta'));
        if (!empty($voucher['kadaluarsa']) && $today->getTimestamp() > strtotime((string)$voucher['kadaluarsa'])) return false;
        $schedule = explode('@', (string)($voucher['jadwal'] ?? ''));
        if (count($schedule) < 2 || !$schedule[0] || !$schedule[1]) return true;
        return $today->getTimestamp() >= strtotime($schedule[0]) && $today->getTimestamp() <= strtotime($schedule[1]);
    }

    private function booleanFlag($value): bool
    {
        if (is_bool($value)) return $value;
        if ($value === 0 || $value === 1 || $value === '0' || $value === '1') return (bool)$value;
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['true', 'yes', 'on'], true)) return true;
            if (in_array($normalized, ['false', 'no', 'off', ''], true)) return false;
        }
        throw new DomainException('Pilihan checkout tidak valid.');
    }
}
