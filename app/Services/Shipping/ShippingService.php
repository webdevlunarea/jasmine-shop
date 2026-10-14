<?php

namespace App\Services\Shipping;

use App\Models\ShippingMethodModel;
use App\Models\ShippingQuoteModel;

class ShippingService
{
    protected ShippingMethodModel $methodModel;
    protected ShippingQuoteModel $quoteModel;
    protected InternalDeliveryProvider $internalProvider;
    protected RajaOngkirRateProvider $rajaOngkirProvider;

    public function __construct()
    {
        $this->methodModel = new ShippingMethodModel();
        $this->quoteModel = new ShippingQuoteModel();
        $this->internalProvider = new InternalDeliveryProvider();
        $this->rajaOngkirProvider = new RajaOngkirRateProvider();
    }

    public function getQuotes(string $email, array $cart, array $address, int $weightGrams): array
    {
        $cartHash = $this->cartHash($cart, $weightGrams);
        $addressHash = $this->addressHash($address);
        $quotes = [];

        foreach ($this->internalProvider->getQuotes($address) as $quote) {
            $quotes[] = $this->persistQuote($email, $quote, $address, $weightGrams, $cartHash, $addressHash);
        }

        foreach ($this->rajaOngkirProvider->getQuotes($address, $weightGrams) as $quote) {
            $quotes[] = $this->persistQuote($email, $quote, $address, $weightGrams, $cartHash, $addressHash);
        }

        return $quotes;
    }

    public function validateQuoteToken(string $token, string $email, array $cart, array $address, int $weightGrams): ?array
    {
        if ($token === '') {
            return null;
        }

        return $this->quoteModel->findValidToken(
            $token,
            $email,
            $this->cartHash($cart, $weightGrams),
            $this->addressHash($address)
        );
    }

    public function cartHash(array $cart, int $weightGrams): string
    {
        $normalized = [];
        foreach ($cart as $item) {
            $normalized[] = [
                'id' => (string)($item['id'] ?? ''),
                'varian' => (string)($item['varian'] ?? ''),
                'jumlah' => (int)($item['jumlah'] ?? 0),
            ];
        }

        usort($normalized, static fn($a, $b) => strcmp($a['id'] . $a['varian'], $b['id'] . $b['varian']));

        return hash('sha256', json_encode($normalized) . '|' . $weightGrams);
    }

    public function addressHash(array $address): string
    {
        return hash('sha256', implode('|', [
            (int)($address['provinsi_id'] ?? 0),
            (int)($address['kabupaten_id'] ?? 0),
            (int)($address['kecamatan_id'] ?? 0),
            (int)($address['kelurahan_id'] ?? 0),
            trim((string)($address['kodepos'] ?? '')),
        ]));
    }

    protected function persistQuote(string $email, array $quote, array $address, int $weightGrams, string $cartHash, string $addressHash): array
    {
        $token = bin2hex(random_bytes(24));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $data = [
            'token' => $token,
            'email_user' => $email,
            'shipping_method_code' => (string)($quote['shipping_method_code'] ?? ''),
            'provider' => (string)($quote['provider'] ?? ''),
            'courier_code' => $quote['courier_code'] ?? null,
            'courier_name' => $quote['courier_name'] ?? null,
            'service_code' => $quote['service_code'] ?? null,
            'service_name' => $quote['service_name'] ?? null,
            'description' => $quote['description'] ?? null,
            'cost' => max(0, (int)($quote['cost'] ?? 0)),
            'etd' => $quote['etd'] ?? null,
            'origin_id' => $quote['origin_id'] ?? null,
            'destination_id' => $quote['destination_id'] ?? null,
            'provinsi_id' => (int)($address['provinsi_id'] ?? 0) ?: null,
            'kabupaten_id' => (int)($address['kabupaten_id'] ?? 0) ?: null,
            'kecamatan_id' => (int)($address['kecamatan_id'] ?? 0) ?: null,
            'kelurahan_id' => (int)($address['kelurahan_id'] ?? 0) ?: null,
            'kodepos' => $address['kodepos'] ?? null,
            'weight_grams' => $weightGrams,
            'cart_hash' => $cartHash,
            'address_hash' => $addressHash,
            'raw_response' => isset($quote['raw_response']) ? json_encode($quote['raw_response']) : null,
            'expires_at' => $expiresAt,
        ];

        $this->quoteModel->insert($data);

        $quote['token'] = $token;
        $quote['expires_at'] = $expiresAt;

        return $quote;
    }
}
