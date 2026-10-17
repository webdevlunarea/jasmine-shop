<?php

namespace App\Services\Auth;

use Config\Facebook;

class FacebookOAuth
{
    private Facebook $config;

    public function __construct(?Facebook $config = null)
    {
        $this->config = $config ?? config(Facebook::class);
    }

    public function ready(): bool { return $this->config->ready(); }

    public static function redirectTarget($value): string
    {
        if (!is_string($value)) return '';
        $value = trim($value);
        $decoded = rawurldecode($value);
        if ($value === '' || $value[0] !== '/' || substr($decoded, 0, 2) === '//'
            || preg_match('/[\\\\\x00-\x20\x7f]/', $decoded)) return '';
        return $value;
    }

    /** Keep the final browser redirect inside the application's base path, including XAMPP installs. */
    public static function applicationPath($value, string $baseURL): string
    {
        $target = self::redirectTarget($value) ?: '/';
        $basePath = rtrim((string)parse_url($baseURL, PHP_URL_PATH), '/');
        if ($basePath === '' || $target === $basePath || strpos($target, $basePath . '/') === 0
            || strpos($target, $basePath . '?') === 0 || strpos($target, $basePath . '#') === 0) return $target;
        return $basePath . $target;
    }

    public function authorizationUrl(string $state): string
    {
        return 'https://www.facebook.com/' . $this->config->graphVersion . '/dialog/oauth?' . http_build_query([
            'client_id' => $this->config->appId,
            'redirect_uri' => $this->config->redirectUri,
            'response_type' => 'code', 'scope' => 'public_profile,email', 'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function profile(string $code): array
    {
        $base = 'https://graph.facebook.com/' . $this->config->graphVersion;
        $token = $this->requestJson($base . '/oauth/access_token', [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $this->config->appId, 'client_secret' => $this->config->appSecret,
                'redirect_uri' => $this->config->redirectUri, 'code' => $code,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $accessToken = $token['access_token'] ?? null;
        if (!is_string($accessToken) || $accessToken === '') throw new \RuntimeException('Facebook token exchange failed');
        $profile = $this->requestJson($base . '/me?' . http_build_query([
            'fields' => 'id,name,email',
            'appsecret_proof' => hash_hmac('sha256', $accessToken, $this->config->appSecret),
        ]), [CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken]]);
        if (!is_string($profile['id'] ?? null) || !preg_match('/^\d{1,64}$/D', $profile['id'])) {
            throw new \RuntimeException('Facebook profile invalid');
        }
        return $profile;
    }

    protected function requestJson(string $url, array $options): array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, $options + [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYHOST => 2, CURLOPT_SSL_VERIFYPEER => true, CURLOPT_FOLLOWLOCATION => false,
        ]);
        try {
            $raw = curl_exec($curl);
            $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $body = json_decode(is_string($raw) ? $raw : '', true);
            if ($raw === false || $status < 200 || $status >= 300 || !is_array($body) || isset($body['error'])) {
                // Never include provider bodies, request URLs, codes, tokens, or secrets in errors/logs.
                throw new \RuntimeException('Facebook request failed');
            }
            return $body;
        } finally {
            curl_close($curl);
        }
    }
}
