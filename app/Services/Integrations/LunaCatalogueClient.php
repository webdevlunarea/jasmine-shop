<?php

namespace App\Services\Integrations;

final class LunaCatalogueClient
{
    private array $settings;
    private $client;

    public function __construct(?array $settings = null, $client = null)
    {
        $this->settings = $settings ?? ['url'=>(string)env('LUNA_SYSTEM_WEB_ORDER_URL', ''), 'token'=>(string)env('LUNA_SYSTEM_WEB_ORDER_TOKEN', '')];
        $this->client = $client;
    }

    public function products(?string $id = null): array
    {
        $url = $this->settings['url'];
        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])
            || isset($parts['query']) || isset($parts['fragment']) || !preg_match('~/lunarea-web-order$~D', $parts['path'] ?? '')
            || $this->settings['token'] === '') throw new \DomainException('Koneksi katalog MyLuna belum dikonfigurasi dengan benar.');
        if ($id !== null && !LunaProductIdentity::uuid($id)) throw new \DomainException('ID produk MyLuna tidak valid.');
        $url = substr($url, 0, -strlen('lunarea-web-order')) . 'lunarea-web-catalogue';
        if ($id !== null) $url .= '?product_id=' . rawurlencode($id);
        try {
            $response = ($this->client ?? service('curlrequest'))->get($url, [
                'headers'=>['Accept'=>'application/json', 'X-Luna-Webhook-Token'=>$this->settings['token']],
                'connect_timeout'=>5, 'timeout'=>15, 'verify'=>true, 'allow_redirects'=>false, 'http_errors'=>false,
            ]);
            if ($response->getStatusCode() !== 200) throw new \RuntimeException('Catalogue unavailable');
            $raw = $response->getBody();
            if (strlen($raw) > 4000000) throw new \RuntimeException('Catalogue exceeds limit');
            $body = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
            if (($body['success'] ?? null) !== true || ($body['data']['complete'] ?? null) !== true
                || !is_array($body['data']['products'] ?? null) || count($body['data']['products']) > 2000) throw new \RuntimeException('Invalid catalogue');
            $products = []; $variantIds = [];
            foreach ($body['data']['products'] as $product) {
                if (!is_array($product) || !LunaProductIdentity::uuid($product['id'] ?? null) || isset($products[$product['id']])
                    || !is_string($product['name'] ?? null) || !is_string($product['sku'] ?? null)
                    || !is_bool($product['active'] ?? null) || !is_int($product['stock'] ?? null) || $product['stock'] < 0
                    || !is_array($product['website_ids'] ?? null) || !is_array($product['variants'] ?? null)) throw new \RuntimeException('Invalid product');
                foreach ($product['website_ids'] as $websiteId) if (!is_string($websiteId) || strlen($websiteId) > 100) throw new \RuntimeException('Invalid reference');
                $names = [];
                foreach ($product['variants'] as $v) {
                    if (!is_array($v) || !LunaProductIdentity::uuid($v['id'] ?? null) || isset($variantIds[$v['id']])
                        || !is_string($v['value'] ?? null) || trim($v['value']) === '' || !is_int($v['stock'] ?? null) || $v['stock'] < 0 || isset($names[$v['value']])) throw new \RuntimeException('Invalid variant');
                    $variantIds[$v['id']] = true; $names[$v['value']] = true;
                }
                $products[$product['id']] = $product;
            }
            if ($id !== null && (count($products) !== 1 || !isset($products[$id]))) throw new \RuntimeException('Product unavailable');
            return array_values($products);
        } catch (\Throwable $e) {
            throw new \DomainException('Katalog MyLuna belum tersedia. Periksa koneksi dan versi integrasi, lalu muat ulang. Tidak ada pemetaan yang disimpan.');
        }
    }
}
