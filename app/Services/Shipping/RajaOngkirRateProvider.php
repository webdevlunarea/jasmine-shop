<?php

namespace App\Services\Shipping;

use App\Models\ShippingMethodModel;

class RajaOngkirRateProvider
{
    protected ShippingMethodModel $methodModel;

    public function __construct()
    {
        $this->methodModel = new ShippingMethodModel();
    }

    public function getQuotes(array $address, int $weightGrams): array
    {
        $method = $this->methodModel->getByCode('rajaongkir_domestic');
        if (!$method || (int)($method['is_active'] ?? 0) !== 1) {
            return [];
        }

        $apiKey = (string)(env('RAJAONGKIR_API_KEY') ?: env('KOMERCE_RAJAONGKIR_API_KEY') ?: '');
        if ($apiKey === '') {
            return [];
        }

        $settings = json_decode((string)($method['settings'] ?? '{}'), true);
        $destinationId = trim((string)($address['rajaongkir_destination_id'] ?? ''));
        $couriers = $settings['couriers'] ?? ['jne', 'jnt', 'sicepat', 'tiki', 'pos', 'sentral'];

        if ($weightGrams <= 0) {
            return [];
        }

        $courier = is_array($couriers) ? implode(':', $couriers) : (string)$couriers;

        try {
            $client = \Config\Services::curlrequest([
                'timeout' => 8,
                'connect_timeout' => 4,
                'http_errors' => false,
            ]);

            if ($destinationId === '') {
                $destinationId = $this->searchDestinationId($client, $apiKey, $address);
            }

            $originId = $this->resolveOriginId($client, $apiKey, $settings);

            if ($originId === '' || $destinationId === '') {
                return [];
            }

            $response = $client->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'headers' => [
                    'key' => $apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'origin' => $originId,
                    'destination' => $destinationId,
                    'weight' => max(1, $weightGrams),
                    'courier' => $courier,
                    'price' => 'lowest',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $payload = json_decode((string)$response->getBody(), true);
            if (!is_array($payload) || empty($payload['data']) || !is_array($payload['data'])) {
                return [];
            }

            $quotes = [];
            foreach ($payload['data'] as $item) {
                $cost = max(0, (int)($item['cost'] ?? 0));
                if ($cost <= 0) {
                    continue;
                }

                $quotes[] = [
                    'shipping_method_code' => 'rajaongkir_domestic',
                    'provider' => 'rajaongkir',
                    'courier_code' => $item['code'] ?? null,
                    'courier_name' => $item['name'] ?? ($item['code'] ?? 'Ekspedisi'),
                    'service_code' => $item['service'] ?? null,
                    'service_name' => $item['service'] ?? null,
                    'description' => $item['description'] ?? null,
                    'cost' => $cost,
                    'etd' => $item['etd'] ?? null,
                    'origin_id' => $originId,
                    'destination_id' => $destinationId,
                    'raw_response' => $item,
                ];
            }

            usort($quotes, static fn($a, $b) => ((int)$a['cost']) <=> ((int)$b['cost']));

            return $quotes;
        } catch (\Throwable $e) {
            log_message('error', 'RajaOngkir quote error: ' . $e->getMessage());
            return [];
        }
    }

    protected function resolveOriginId($client, string $apiKey, array $settings): string
    {
        $originId = trim((string)($settings['origin_id'] ?? ''));
        $numericOrigin = preg_replace('/\D+/', '', $originId);

        if ($originId !== '' && strlen($numericOrigin) >= 4) {
            return $originId;
        }

        $originLabel = trim((string)($settings['origin_label'] ?? 'Kota Semarang Jawa Tengah'));
        $originAddress = [
            'kodepos' => trim((string)($settings['origin_postal_code'] ?? $settings['origin_zip'] ?? '')),
            'kelurahan_label' => trim((string)($settings['origin_kelurahan'] ?? '')),
            'kecamatan_label' => trim((string)($settings['origin_kecamatan'] ?? '')),
            'kabupaten_label' => $originLabel ?: 'Kota Semarang Jawa Tengah',
        ];

        return $this->searchDestinationId($client, $apiKey, $originAddress);
    }

    protected function searchDestinationId($client, string $apiKey, array $address): string
    {
        $searchParts = [
            trim((string)($address['kodepos'] ?? '')),
            trim((string)($address['kelurahan_label'] ?? '')),
            trim((string)($address['kecamatan_label'] ?? '')),
            trim((string)($address['kabupaten_label'] ?? '')),
        ];

        $searchParts = array_values(array_filter($searchParts));
        $queries = [];

        if (!empty($address['kodepos'])) {
            $queries[] = (string)$address['kodepos'];
        }

        if (!empty($searchParts)) {
            $queries[] = implode(' ', array_slice($searchParts, 0, 3));
        }

        foreach (array_unique($queries) as $query) {
            try {
                $response = $client->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                    'headers' => [
                        'key' => $apiKey,
                    ],
                    'query' => [
                        'search' => $query,
                        'limit' => 10,
                        'offset' => 0,
                    ],
                ]);

                if ($response->getStatusCode() !== 200) {
                    continue;
                }

                $payload = json_decode((string)$response->getBody(), true);
                if (empty($payload['data']) || !is_array($payload['data'])) {
                    continue;
                }

                $kecamatan = strtolower(trim((string)($address['kecamatan_label'] ?? '')));
                $kodepos = trim((string)($address['kodepos'] ?? ''));

                foreach ($payload['data'] as $row) {
                    $rowDistrict = strtolower(trim((string)($row['district_name'] ?? '')));
                    $rowSubdistrict = strtolower(trim((string)($row['subdistrict_name'] ?? '')));
                    $rowZip = trim((string)($row['zip_code'] ?? ''));

                    if (($kodepos !== '' && $rowZip === $kodepos) || ($kecamatan !== '' && ($rowDistrict === $kecamatan || $rowSubdistrict === $kecamatan))) {
                        return (string)($row['id'] ?? '');
                    }
                }

                return (string)($payload['data'][0]['id'] ?? '');
            } catch (\Throwable $e) {
                log_message('error', 'RajaOngkir destination search error: ' . $e->getMessage());
            }
        }

        return '';
    }
}
