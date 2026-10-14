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
                log_message('warning', 'RajaOngkir quote skipped: origin_id or destination_id is empty. origin=' . $originId . ' destination=' . $destinationId);
                return [];
            }

            log_message('info', 'RajaOngkir quote request origin=' . $originId . ' destination=' . $destinationId . ' weight=' . max(1, $weightGrams) . ' courier=' . $courier);

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
            return $bestId;
        }

        log_message('warning', 'RajaOngkir destination match failed. score=' . $bestScore . ' kecamatan=' . $kecamatan . ' kodepos=' . $kodepos . ' kabupaten=' . $kabupaten);
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

