<?php

namespace App\Services\Shipping;

/** Read-only BinderByte tracking adapter shared by web and native customer APIs. */
final class BinderByteTrackingProvider
{
    private $transport;

    public function __construct(?callable $transport = null)
    {
        $this->transport = $transport;
    }

    public static function normalizeAwb(string $awb): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(trim($awb))) ?: '';
    }

    public static function resolveCourierCode(string $courierText): string
    {
        $text = strtolower(trim(str_replace('&amp;', '&', $courierText)));
        $text = preg_replace('/\s*&\s*/', ' and ', $text) ?? $text;
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
        foreach ([
            'sicepat' => 'sicepat', 'si cepat' => 'sicepat', 'anteraja' => 'anteraja', 'anter aja' => 'anteraja',
            'jnt' => 'jnt', 'j and t' => 'jnt', 'jne' => 'jne', 'tiki' => 'tiki',
            'pos' => 'pos', 'wahana' => 'wahana', 'ninja' => 'ninja', 'lion' => 'lion', 'indah' => 'indah',
            'dakota' => 'dakota', 'sap' => 'sap', 'id express' => 'ide', 'idexpress' => 'ide',
            'shopee' => 'spx', 'spx' => 'spx',
        ] as $needle => $code) {
            if (str_contains($text, $needle)) return $code;
        }
        return '';
    }

    public function track(string $courier, string $awb): array
    {
        $courier = strtolower(trim($courier));
        $awb = self::normalizeAwb($awb);
        if (!in_array($courier, ['jne', 'jnt', 'sicepat', 'anteraja', 'tiki', 'pos', 'wahana', 'ninja', 'lion', 'indah', 'dakota', 'sap', 'ide', 'spx'], true) || $awb === '') {
            return $this->failure('Nomor resi atau kurir belum tersedia untuk pelacakan online.');
        }

        $apiKey = $this->apiKey();
        if ($apiKey === '') return $this->failure('Layanan lacak pengiriman sedang dalam penyesuaian. Silakan coba kembali nanti.');

        $baseUrl = trim((string)(getenv('BINDERBYTE_BASE_URL') ?: env('BINDERBYTE_BASE_URL') ?: 'https://api.binderbyte.com'));
        $origin = parse_url($baseUrl) ?: [];
        $host = strtolower((string)($origin['host'] ?? ''));
        if (strtolower((string)($origin['scheme'] ?? '')) !== 'https'
            || $host !== 'api.binderbyte.com'
            || isset($origin['user']) || isset($origin['pass']) || isset($origin['query']) || isset($origin['fragment'])
            || (isset($origin['port']) && (int)$origin['port'] !== 443)
            || !in_array((string)($origin['path'] ?? ''), ['', '/'], true)) {
            log_message('error', 'BinderByte tracking base URL rejected: exact HTTPS API host required.');
            return $this->failure('Layanan lacak pengiriman sedang dalam penyesuaian. Silakan coba kembali nanti.');
        }
        $url = rtrim($baseUrl, '/') . '/v1/track';
        $query = ['api_key' => $apiKey, 'courier' => $courier, 'awb' => $awb];
        try {
            if ($this->transport !== null) {
                $response = ($this->transport)($url, $query);
                $httpCode = (int)($response['status'] ?? 0);
                $decoded = $response['body'] ?? null;
            } else {
                $response = \Config\Services::curlrequest([
                    'timeout' => 20, 'connect_timeout' => 10, 'http_errors' => false,
                    'verify' => true, 'allow_redirects' => false,
                ])->get($url, ['query' => $query]);
                $httpCode = $response->getStatusCode();
                $decoded = json_decode($response->getBody(), true);
            }
        } catch (\Throwable $e) {
            log_message('error', 'BinderByte tracking request failed: {type}', ['type' => get_class($e)]);
            return $this->failure('Layanan lacak pengiriman sedang sibuk. Silakan coba kembali beberapa saat lagi.');
        }

        if (is_string($decoded)) $decoded = json_decode($decoded, true);
        if ($httpCode < 200 || $httpCode >= 300 || !is_array($decoded)
            || (isset($decoded['status']) && (int)$decoded['status'] !== 200)) {
            return $this->failure('Informasi pengiriman belum tersedia saat ini. Pastikan nomor resi benar, lalu coba kembali beberapa saat lagi.');
        }

        $data = $decoded['data'] ?? null;
        if (!is_array($data)) return $this->failure('Informasi pengiriman belum tersedia saat ini. Silakan coba kembali beberapa saat lagi.');
        return [
            'success' => true,
            'provider' => 'BinderByte',
            'summary' => is_array($data['summary'] ?? null) ? $data['summary'] : [],
            'detail' => is_array($data['detail'] ?? null) ? $data['detail'] : [],
            'history' => is_array($data['history'] ?? null) ? $data['history'] : [],
        ];
    }

    private function apiKey(): string
    {
        foreach (['BINDERBYTE_API_KEY', 'binderbyte.apiKey', 'api_key_binderbyte', 'BINDERBYTE_KEY'] as $key) {
            $value = trim((string)(getenv($key) ?: env($key) ?: ''));
            if ($value !== '') return $value;
        }
        return '';
    }

    private function failure(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
