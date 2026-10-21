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
    private array $subsidyPolicies = [];
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct(?\CodeIgniter\Database\BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->methodModel = new ShippingMethodModel($this->db);
        $this->quoteModel = new ShippingQuoteModel($this->db);
        $this->internalProvider = new InternalDeliveryProvider();
        $this->rajaOngkirProvider = new RajaOngkirRateProvider();
    }

    public function getQuotes(string $email, array $cart, array $address, int $weightGrams): array
    {
        return $this->getQuotesDetailed($email, $cart, $address, $weightGrams)['quotes'];
    }

    /** @return array{quotes:array,external_status:string} */
    public function getQuotesDetailed(string $email, array $cart, array $address, int $weightGrams): array
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

        $externalStatus = $this->rajaOngkirProvider->lastStatus() ?? 'not_checked';

        usort($quotes, static fn($a, $b) => ((int)($a['cost'] ?? 0)) <=> ((int)($b['cost'] ?? 0)));

        return ['quotes' => $quotes, 'external_status' => $externalStatus];
    }

    public function validateQuoteToken(string $token, string $email, array $cart, array $address, int $weightGrams): ?array
    {
        if ($token === '') {
            return null;
        }

        $quote = $this->quoteModel->findValidToken(
            $token,
            $email,
            $this->cartHash($cart, $weightGrams),
            $this->addressHash($address)
        );
        if (!$quote) return null;
        $method = $this->methodModel->getByCode((string)$quote['shipping_method_code']);
        if (!$method || !(int)$method['is_active']) return null;
        if ($quote['shipping_method_code'] === 'internal_delivery') {
            return $this->internalProvider->getQuotes($address) && (int)$quote['cost'] === 0 ? $quote : null;
        }
        $policy = $this->runtimeSubsidyPolicy($method);
        $snapshot = ShippingSubsidyPolicy::snapshot($quote);
        if (!$snapshot) return empty($policy['enabled']) ? $quote : null;
        $current = ShippingSubsidyPolicy::calculate((int)$snapshot['original_cost'], $policy, (string)($quote['courier_code'] ?? ''));
        if ($current['policy_hash'] !== ($snapshot['policy_hash'] ?? '')
            || $current['subsidy_amount'] !== (int)($snapshot['subsidy_amount'] ?? -1)
            || $current['payable_cost'] !== (int)$quote['cost']) return null;
        return $quote;
    }

    private function runtimeSubsidyPolicy(array $method): array
    {
        $settings = json_decode((string)($method['settings'] ?? '{}'), true) ?: [];
        try {
            $policy = ShippingSubsidyPolicy::normalize($settings['subsidy'] ?? []);
            if (!ShippingSubsidyPolicy::storageReady($this->db)) $policy['enabled'] = false;
            return $policy;
        } catch (\DomainException $e) {
            log_message('error', 'Shipping subsidy configuration invalid; using original rates.');
            return ShippingSubsidyPolicy::normalize([]);
        }
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
        if (($quote['shipping_method_code'] ?? '') !== 'internal_delivery') {
            $code = (string)$quote['shipping_method_code'];
            if (!isset($this->subsidyPolicies[$code])) {
                $this->subsidyPolicies[$code] = $this->runtimeSubsidyPolicy($this->methodModel->getByCode($code) ?: []);
            }
            $snapshot = ShippingSubsidyPolicy::calculate((int)$quote['cost'], $this->subsidyPolicies[$code], (string)($quote['courier_code'] ?? ''));
            $quote['raw_response'] = ['provider_response' => $quote['raw_response'] ?? null, 'shipping_pricing' => $snapshot];
            $quote['cost'] = $snapshot['payable_cost'];
        }

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

        if (!$this->quoteModel->insert($data)) throw new \RuntimeException('Shipping quote could not be stored.');

        $quote['token'] = $token;
        $quote['expires_at'] = $expiresAt;

        // Customer-facing contract: never return provider raw data or subsidy accounting.
        return array_intersect_key($quote, array_flip([
            'token', 'expires_at', 'cost', 'courier_code', 'courier_name', 'service_code', 'service_name', 'description', 'etd',
        ]));
    }
}
