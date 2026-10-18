<?php

namespace App\Controllers\Api\V1;

use App\Models\CustomerAddressModel;
use App\Models\BarangModel;
use App\Models\PembeliModel;
use App\Services\ProductVariantStock;
use App\Services\Shipping\ShippingService;
use Config\Database;

class ShippingController extends ApiController
{
    public function quote()
    {
        $email = $this->authEmail();
        $rateKey = 'lunarea-api-shipping-' . hash('sha256', $email . '|' . $this->request->getIPAddress());
        if (!service('throttler')->check($rateKey, 12, 60)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak permintaan ongkir. Tunggu sebentar lalu coba lagi.', 429);
        }
        $input = $this->payload();
        $address = (new CustomerAddressModel())->owned($email, (int)($input['address_id'] ?? 0));
        if (!$address) return $this->failure('ADDRESS_NOT_FOUND', 'Pilih alamat pengiriman yang tersimpan.', 422);
        $addressData = json_decode((string)$address['address_data'], true);
        if (!is_array($addressData)) return $this->failure('INVALID_ADDRESS', 'Data alamat perlu diperbarui.', 422);
        $profile = (new PembeliModel())->getPembeli($email);
        $cart = json_decode((string)($profile['keranjang'] ?? '[]'), true);
        if (!is_array($cart) || !$cart) return $this->failure('CART_EMPTY', 'Keranjang masih kosong.', 400);
        if (count($cart) > 100) return $this->failure('CART_INVALID', 'Keranjang perlu diperbarui sebelum menghitung ongkir.', 422);

        $weight = 0.0;
        foreach ($cart as $item) {
            $id = trim((string)($item['id'] ?? ''));
            $variant = (string)($item['varian'] ?? '');
            $quantity = (int)($item['jumlah'] ?? 0);
            if ($id === '' || $quantity < 1 || $quantity > 99) {
                return $this->failure('CART_INVALID', 'Keranjang perlu diperbarui sebelum menghitung ongkir.', 422);
            }
            $product = (new BarangModel())->select('berat,stok,varian')->where(['id' => $id, 'active' => '1'])->first();
            if (!$product) return $this->failure('PRODUCT_UNAVAILABLE', 'Produk di keranjang sudah tidak tersedia.', 422);
            $variants = ProductVariantStock::variants((string)$product['varian']);
            $names = ProductVariantStock::names($variants);
            $index = array_search($variant, $names, true);
            if ($index === false) return $this->failure('VARIANT_UNAVAILABLE', 'Varian produk di keranjang sudah tidak tersedia.', 422);
            $stock = ProductVariantStock::stockForIndex($variants, (string)$product['stok'], (int)$index);
            if ($quantity > $stock) return $this->failure('INSUFFICIENT_STOCK', 'Stok produk berubah. Perbarui keranjang.', 422);
            $weight += max(0, (float)str_replace(',', '.', (string)($product['berat'] ?? 0))) * $quantity;
        }
        $db = Database::connect();
        $address = [
            'provinsi_id' => (int)($addressData['prov_id'] ?? 0), 'kabupaten_id' => (int)($addressData['kab_id'] ?? 0),
            'kecamatan_id' => (int)($addressData['kec_id'] ?? 0), 'kelurahan_id' => 0,
            'kodepos' => (string)($addressData['kodepos'] ?? ''), 'provinsi_label' => (string)($addressData['prov'] ?? ''),
            'kabupaten_label' => (string)($addressData['kab'] ?? ''), 'kecamatan_label' => (string)($addressData['kec'] ?? ''),
            'kelurahan_label' => (string)($addressData['desa'] ?? ''), 'rajaongkir_destination_id' => '',
        ];
        $province = $db->table('provinsi')->where('id', $address['provinsi_id'])->get()->getRowArray();
        $city = $db->table('kabupaten')->where(['id' => $address['kabupaten_id'], 'provinsi_id' => $address['provinsi_id']])->get()->getRowArray();
        $district = $db->table('kecamatan')->where(['id' => $address['kecamatan_id'], 'kabupaten_id' => $address['kabupaten_id']])->get()->getRowArray();
        if (!$province || !$city || !$district) return $this->failure('INVALID_ADDRESS', 'Data wilayah alamat perlu diperbarui.', 422);
        $address['provinsi_label'] = (string)$province['label'];
        $address['kabupaten_label'] = (string)$city['label'];
        $address['kecamatan_label'] = (string)$district['label'];
        try {
            $weightGrams = max(1000, (int)ceil($weight * 1000));
            $quotes = (new ShippingService())->getQuotes($email, $cart, $address, $weightGrams);
            return $this->success(['quotes' => $quotes, 'weight_grams' => $weightGrams]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API shipping quote failed: ' . get_class($e));
            return $this->failure('SHIPPING_UNAVAILABLE', 'Ongkir sementara tidak tersedia. Coba lagi nanti.', 503);
        }
    }
}
