<?php

namespace App\Services\Notifications;

use Config\Services;
use RuntimeException;

/** Minimal FCM HTTP v1 sender using a service-account JSON kept outside source control. */
final class FirebaseMessaging implements FirebaseMessageSender
{
    private function accessToken(): string
    {
        $project = trim((string)env('FIREBASE_PROJECT_ID', ''));
        if ($project === '') throw new RuntimeException('FCM project is not configured');
        $cache = Services::cache();
        $cacheKey = 'lunarea-fcm-oauth-token-' . hash('sha256', $project);
        $cached = $cache->get($cacheKey);
        if (is_string($cached) && $cached !== '') return $cached;
        $path = trim((string)env('FIREBASE_SERVICE_ACCOUNT_PATH', ''));
        if ($path === '' || !is_file($path) || !is_readable($path)) throw new RuntimeException('FCM credentials unavailable');
        $credentials = json_decode((string)file_get_contents($path), true);
        if (!is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key']) || empty($credentials['token_uri'])) throw new RuntimeException('FCM credentials invalid');
        $tokenUri = (string)$credentials['token_uri'];
        $tokenUriParts = parse_url($tokenUri);
        if (!is_array($tokenUriParts) || strtolower((string)($tokenUriParts['scheme'] ?? '')) !== 'https'
            || strtolower((string)($tokenUriParts['host'] ?? '')) !== 'oauth2.googleapis.com'
            || ($tokenUriParts['path'] ?? '') !== '/token' || isset($tokenUriParts['user']) || isset($tokenUriParts['pass'])) {
            throw new RuntimeException('FCM token endpoint is invalid');
        }
        $now = time();
        $header = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $this->base64url(json_encode([
            'iss' => $credentials['client_email'], 'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUri, 'iat' => $now, 'exp' => $now + 3600,
        ]));
        $unsigned = $header . '.' . $claims;
        $key = openssl_pkey_get_private($credentials['private_key']);
        if (!$key || !openssl_sign($unsigned, $signature, $key, OPENSSL_ALGO_SHA256)) throw new RuntimeException('FCM JWT signing failed');
        $jwt = $unsigned . '.' . $this->base64url($signature);
        $response = Services::curlrequest()->post($tokenUri, [
            'form_params' => ['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt],
            'timeout' => 10, 'connect_timeout' => 4, 'http_errors' => false,
            'verify' => true, 'allow_redirects' => false,
        ]);
        $data = json_decode((string)$response->getBody(), true);
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300 || empty($data['access_token'])) throw new RuntimeException('FCM OAuth token request failed');
        $cache->save($cacheKey, $data['access_token'], max(60, (int)($data['expires_in'] ?? 3600) - 120));
        return $data['access_token'];
    }

    public function send(string $deviceToken, array $notification, array $data = []): array
    {
        $project = trim((string)env('FIREBASE_PROJECT_ID', ''));
        if ($project === '') throw new RuntimeException('FCM project is not configured');
        $payloadData = [];
        foreach ($data as $key => $value) $payloadData[(string)$key] = is_scalar($value) ? (string)$value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $type = strtolower((string)($payloadData['type'] ?? ''));
        $notificationTag = (string)($payloadData['notification_id'] ?? $payloadData['campaign_id'] ?? $payloadData['order_id'] ?? '');
        $channelId = match (true) {
            str_starts_with($type, 'broadcast_'), $type === 'back_in_stock' => 'lunarea_promotions_v1',
            in_array($type, ['admin_test', 'account_security', 'account_update'], true) => 'lunarea_account_v1',
            default => 'lunarea_order_updates',
        };
        $response = Services::curlrequest()->post('https://fcm.googleapis.com/v1/projects/' . rawurlencode($project) . '/messages:send', [
            'headers' => ['Authorization' => 'Bearer ' . $this->accessToken(), 'Content-Type' => 'application/json'],
            'json' => ['message' => [
                'token' => $deviceToken,
                'notification' => $notification,
                'data' => $payloadData,
                'android' => [
                    'priority' => 'HIGH',
                    'notification' => [
                        'channel_id' => $channelId,
                        'icon' => 'ic_notification_small',
                        'color' => '#03AC0E',
                        'visibility' => 'PRIVATE',
                        ...($notificationTag !== '' ? ['tag' => substr($notificationTag, 0, 128)] : []),
                    ],
                ],
            ]],
            'timeout' => 10, 'connect_timeout' => 4, 'http_errors' => false,
            'verify' => true, 'allow_redirects' => false,
        ]);
        $status = $response->getStatusCode();
        $result = json_decode((string)$response->getBody(), true) ?: [];
        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'error_code' => $result['error']['details'][0]['errorCode'] ?? ($result['error']['status'] ?? null)];
    }

    private function base64url(string $value): string { return rtrim(strtr(base64_encode($value), '+/', '-_'), '='); }
}
