<?php

namespace App\Services\Shipping;

use App\Models\ShippingMethodModel;

class RajaOngkirRateProvider
{
    protected ShippingMethodModel $methodModel;
    private string $lastStatus = 'not_checked';

    public function __construct(?ShippingMethodModel $methodModel = null)
    {
        $this->methodModel = $methodModel ?? new ShippingMethodModel();
    }

    protected function cacheGet(string $key) { return \Config\Services::cache()->get($key); }

    protected function cacheSave(string $key, $value, int $ttl): void
    {
        \Config\Services::cache()->save($key, $value, $ttl);
    }

    private function cacheKey(string $scope, string $apiKey, array $facts): string
    {
        return 'lunarea-rajaongkir-' . $scope . '-' . hash('sha256', json_encode([
            'api_key_fingerprint' => hash('sha256', $apiKey),
            'facts' => $facts,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function remember(string $key, int $ttl, callable $loader)
    {
        $cached = $this->cacheGet($key);
        if (is_array($cached) && array_key_exists('value', $cached)) return $cached['value'];
        $value = $loader();
        $this->cacheSave($key, ['value' => $value], $ttl);
        return $value;
    }

    public function getQuotes(array $address, int $weightGrams): array
    {
        $this->lastStatus = 'not_checked';
        $method = $this->methodModel->getByCode('rajaongkir_domestic');
        if (!$method || (int)($method['is_active'] ?? 0) !== 1) {
            $this->lastStatus = 'disabled';
            return [];
        }

        $apiKey = (string)(env('RAJAONGKIR_API_KEY') ?: env('KOMERCE_RAJAONGKIR_API_KEY') ?: '');
        if ($apiKey === '') {
            $this->lastStatus = 'missing_api_key';
            return [];
        }

        $settings = json_decode((string)($method['settings'] ?? '{}'), true);
        $destinationId = trim((string)($address['rajaongkir_destination_id'] ?? ''));
        $couriers = $settings['couriers'] ?? ['jne', 'jnt', 'sicepat', 'tiki', 'pos', 'sentral'];

        if ($weightGrams <= 0) {
            $this->lastStatus = 'invalid_weight';
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
                $this->lastStatus = 'location_unavailable';
                log_message('warning', 'RajaOngkir quote skipped: origin_id or destination_id is empty. origin=' . $originId . ' destination=' . $destinationId);
                return [];
            }

            $cacheKey = $this->cacheKey('rates', $apiKey, [
                'origin_id' => $originId,
                'destination_id' => $destinationId,
                'weight_grams' => max(1, $weightGrams),
                'couriers' => $courier,
                'price' => 'lowest',
                'method_settings' => hash('sha256', (string)($method['settings'] ?? '{}')),
            ]);
            // Briefly cache identical quotes to prevent address refreshes and retries
            // from consuming the provider's free-tier request quota.
            $payload = $this->remember($cacheKey, 60, fn() => $this->requestRatePayload(
                $client, $apiKey, $originId, $destinationId, $weightGrams, $courier
            ));
            if (!is_array($payload) || !isset($payload['data']) || !is_array($payload['data'])) {
                $this->lastStatus = 'provider_error';
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

            $this->lastStatus = $quotes ? 'available' : 'no_rates';

            return $quotes;
        } catch (\Throwable $e) {
            $this->lastStatus = 'provider_error';
            log_message('error', 'RajaOngkir quote error: ' . get_class($e));
            return [];
        }
    }

    /** Diagnostic for the server-side quote endpoint; never contains credentials or raw provider data. */
    public function lastStatus(): ?string
    {
        return $this->lastStatus;
    }

    protected function requestRatePayload($client, string $apiKey, string $originId, string $destinationId, int $weightGrams, string $courier): ?array
    {
        $response = $client->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
            'headers' => ['key' => $apiKey, 'Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'origin' => $originId, 'destination' => $destinationId,
                'weight' => max(1, $weightGrams), 'courier' => $courier, 'price' => 'lowest',
            ],
        ]);
        if ($response->getStatusCode() !== 200) return null;
        $payload = json_decode((string)$response->getBody(), true);
        return is_array($payload) && isset($payload['data']) && is_array($payload['data']) ? $payload : null;
    }

    protected function resolveOriginId($client, string $apiKey, array $settings): string
    {
        $originId = trim((string)($settings['origin_id'] ?? ''));
        $numericOrigin = preg_replace('/\D+/', '', $originId);

        if ($originId !== '' && strlen($numericOrigin) >= 4) {
            return $originId;
        }

        $originKecamatan = trim((string)($settings['origin_kecamatan'] ?? ''));
        $originKelurahan = trim((string)($settings['origin_kelurahan'] ?? ''));
        $originPostalCode = trim((string)($settings['origin_postal_code'] ?? $settings['origin_zip'] ?? ''));

        // Hindari auto-search origin yang terlalu umum seperti "Kota Semarang".
        // RajaOngkir memakai ID destination level kecamatan/kelurahan; jika origin terlalu umum,
        // hasil pertama API bisa salah dan tarif checkout terlihat sangat mahal.
        if ($originKecamatan === '' && $originPostalCode === '') {
            log_message('warning', 'RajaOngkir origin belum spesifik. Isi Origin ID RajaOngkir atau kecamatan/kodepos origin di admin.');
            return '';
        }

        $originLabel = trim((string)($settings['origin_label'] ?? 'Kota Semarang Jawa Tengah'));
        $originAddress = [
            'kodepos' => $originPostalCode,
            'kelurahan_label' => $originKelurahan,
            'kecamatan_label' => $originKecamatan,
            'kabupaten_label' => $originLabel ?: 'Kota Semarang Jawa Tengah',
            'provinsi_label' => trim((string)($settings['origin_provinsi'] ?? 'Jawa Tengah')),
        ];

        return $this->searchDestinationId($client, $apiKey, $originAddress);
    }

    protected function searchDestinationId($client, string $apiKey, array $address): string
    {
        $kodepos = trim((string)($address['kodepos'] ?? ''));
        $kelurahan = trim((string)($address['kelurahan_label'] ?? ''));
        $kecamatan = trim((string)($address['kecamatan_label'] ?? ''));
        $kabupaten = trim((string)($address['kabupaten_label'] ?? ''));
        $provinsi = trim((string)($address['provinsi_label'] ?? ''));

        $cacheKey = $this->cacheKey('destination', $apiKey, [
            'postal_code' => $kodepos,
            'kelurahan' => $this->normalizeLocationName($kelurahan),
            'kecamatan' => $this->normalizeLocationName($kecamatan),
            'kabupaten' => $this->normalizeLocationName($kabupaten),
            'provinsi' => $this->normalizeLocationName($provinsi),
        ]);
        $cachedId = $this->cacheGet($cacheKey);
        if (is_string($cachedId)) return $cachedId;

        $queries = [];

        if ($kelurahan !== '' && $kecamatan !== '') {
            $queries[] = $kelurahan . ' ' . $kecamatan;
        }

        if ($kecamatan !== '' && $kabupaten !== '') {
            $queries[] = $kecamatan . ' ' . $kabupaten;
        }

        if ($kodepos !== '') {
            $queries[] = $kodepos;
        }

        if ($kecamatan !== '') {
            $queries[] = $kecamatan;
        }

        if ($kabupaten !== '' && ($kecamatan !== '' || $kodepos !== '')) {
            $queries[] = $kabupaten;
        }

        $queries = array_values(array_unique(array_filter($queries)));
        if (empty($queries)) {
            return '';
        }

        $bestId = '';
        $bestScore = 0;
        $bestLabel = '';
        $needsSpecificMatch = ($kecamatan !== '' || $kodepos !== '');
        $minimumScore = $needsSpecificMatch ? 3 : 2;

        foreach ($queries as $query) {
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

                foreach ($payload['data'] as $row) {
                    $score = $this->scoreDestinationRow($row, [
                        'kodepos' => $kodepos,
                        'kelurahan_label' => $kelurahan,
                        'kecamatan_label' => $kecamatan,
                        'kabupaten_label' => $kabupaten,
                        'provinsi_label' => $provinsi,
                    ]);

                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestId = (string)($row['id'] ?? '');
                        $bestLabel = (string)($row['label'] ?? (($row['district_name'] ?? '') . ', ' . ($row['city_name'] ?? '')));
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'RajaOngkir destination search error: ' . $e->getMessage());
            }
        }

        if ($bestId !== '' && $bestScore >= $minimumScore) {
            log_message('info', 'RajaOngkir destination matched id=' . $bestId . ' score=' . $bestScore . ' label=' . $bestLabel);
            $this->cacheSave($cacheKey, $bestId, 86400);
            return $bestId;
        }

        log_message('warning', 'RajaOngkir destination match failed. score=' . $bestScore . ' kecamatan=' . $kecamatan . ' kodepos=' . $kodepos . ' kabupaten=' . $kabupaten);
        // Cache misses briefly so repeated retries do not fan out to several search calls.
        $this->cacheSave($cacheKey, '', 300);
        return '';
    }

    protected function scoreDestinationRow(array $row, array $address): int
    {
        $score = 0;
        $kodepos = trim((string)($address['kodepos'] ?? ''));
        $kelurahan = $this->normalizeLocationName((string)($address['kelurahan_label'] ?? ''));
        $kecamatan = $this->normalizeLocationName((string)($address['kecamatan_label'] ?? ''));
        $kabupaten = $this->normalizeLocationName((string)($address['kabupaten_label'] ?? ''));
        $provinsi = $this->normalizeLocationName((string)($address['provinsi_label'] ?? ''));

        $rowZip = trim((string)($row['zip_code'] ?? ''));
        $rowSubdistrict = $this->normalizeLocationName((string)($row['subdistrict_name'] ?? ''));
        $rowDistrict = $this->normalizeLocationName((string)($row['district_name'] ?? ''));
        $rowCity = $this->normalizeLocationName((string)($row['city_name'] ?? ''));
        $rowProvince = $this->normalizeLocationName((string)($row['province_name'] ?? ''));
        $rowLabel = $this->normalizeLocationName((string)($row['label'] ?? ''));

        if ($kodepos !== '' && $rowZip === $kodepos) {
            $score += 4;
        }

        if ($kecamatan !== '') {
            if ($rowDistrict === $kecamatan || $rowSubdistrict === $kecamatan) {
                $score += 5;
            } elseif ($this->containsEither($rowDistrict, $kecamatan) || $this->containsEither($rowSubdistrict, $kecamatan) || $this->containsEither($rowLabel, $kecamatan)) {
                $score += 3;
            }
        }

        if ($kelurahan !== '') {
            if ($rowSubdistrict === $kelurahan) {
                $score += 3;
            } elseif ($this->containsEither($rowSubdistrict, $kelurahan) || $this->containsEither($rowLabel, $kelurahan)) {
                $score += 1;
            }
        }

        if ($kabupaten !== '') {
            if ($rowCity === $kabupaten) {
                $score += 2;
            } elseif ($this->containsEither($rowCity, $kabupaten) || $this->containsEither($rowLabel, $kabupaten)) {
                $score += 1;
            }
        }

        if ($provinsi !== '' && ($rowProvince === $provinsi || $this->containsEither($rowProvince, $provinsi) || $this->containsEither($rowLabel, $provinsi))) {
            $score += 1;
        }

        return $score;
    }

    protected function normalizeLocationName(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['kabupaten', 'kab.', 'kab ', 'kota', 'kec.', 'kecamatan', 'kel.', 'kelurahan'], '', $value);
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? '';
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    protected function containsEither(string $haystack, string $needle): bool
    {
        if ($haystack === '' || $needle === '') {
            return false;
        }

        return str_contains($haystack, $needle) || str_contains($needle, $haystack);
    }
}

