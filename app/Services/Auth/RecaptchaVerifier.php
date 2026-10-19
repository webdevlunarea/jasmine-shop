<?php

namespace App\Services\Auth;

use Config\Recaptcha;

class RecaptchaVerifier
{
    private Recaptcha $config;
    private $client;
    private $googleClientFactory;

    public function __construct(?Recaptcha $config = null, $client = null, $googleClientFactory = null)
    {
        $this->config = $config ?? config(Recaptcha::class);
        $this->client = $client;
        $this->googleClientFactory = $googleClientFactory;
    }

    /** Returns a customer-facing error, or null. No database writes or token logging. */
    public function error($token, string $platform = 'web', string $expectedAction = ''): ?string
    {
        if ($platform === 'android') return $this->androidError($token, $expectedAction);
        if (!$this->config->enabled) return null;
        if (trim($this->config->siteKey) === '' || trim($this->config->secretKey) === '' || trim($this->config->allowedHostnames) === '') {
            log_message('error', 'Authentication CAPTCHA is enabled but configuration is incomplete.');
            return 'Verifikasi keamanan sedang tidak tersedia. Silakan coba beberapa saat lagi.';
        }
        if (!is_string($token) || trim($token) === '' || strlen($token) > 8192) {
            return 'Silakan selesaikan verifikasi keamanan terlebih dahulu.';
        }
        try {
            $client = $this->client ?? \Config\Services::curlrequest([], null, null, false);
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => ['secret' => $this->config->secretKey, 'response' => $token],
                'connect_timeout' => 3, 'timeout' => 8, 'verify' => true,
                'http_errors' => false, 'allow_redirects' => false,
            ]);
            if ($response->getStatusCode() !== 200) throw new \RuntimeException('Verification HTTP failure');
            $body = json_decode($response->getBody(), true);
            if (!is_array($body)) throw new \RuntimeException('Invalid verification response');
            $hosts = array_filter(array_map('trim', explode(',', strtolower($this->config->allowedHostnames))));
            $hostname = $body['hostname'] ?? null;
            $timestamp = isset($body['challenge_ts']) && is_string($body['challenge_ts']) ? strtotime($body['challenge_ts']) : false;
            // Reject provider errors including quota failures, even if success is reported true.
            if (($body['success'] ?? false) !== true || !empty($body['error-codes']) || !empty($body['error']) || !empty($body['errors'])
                || !is_string($hostname) || !in_array(strtolower($hostname), $hosts, true)
                || $timestamp === false || $timestamp < time() - 120 || $timestamp > time() + 30) {
                return 'Verifikasi keamanan belum berhasil atau sudah kedaluwarsa. Silakan ulangi.';
            }
            return null;
        } catch (\Throwable $error) {
            // Exception messages may contain request options; never log them (secret/token).
            log_message('error', 'Authentication CAPTCHA verification service failed.');
            return 'Verifikasi keamanan sedang terganggu. Silakan coba beberapa saat lagi.';
        }
    }

    /** Assess native Android tokens server-side; never send service credentials to clients. */
    private function androidError($token, string $expectedAction): ?string
    {
        if (!$this->config->androidEnabled) return null;
        if (trim($this->config->androidProjectId) === '' || trim($this->config->androidSiteKey) === ''
            || trim($this->config->androidPackageName) === '' || trim($this->config->androidServiceAccountPath) === '') {
            log_message('error', 'Android reCAPTCHA is enabled but configuration is incomplete.');
            return 'Verifikasi keamanan sedang tidak tersedia. Silakan coba beberapa saat lagi.';
        }
        if (!is_string($token) || trim($token) === '' || strlen($token) > 8192
            || !preg_match('/^[a-z][a-z0-9_]{1,99}$/', $expectedAction)) {
            return 'Silakan ulangi verifikasi keamanan.';
        }
        try {
            $path = $this->config->androidServiceAccountPath;
            if (!is_file($path) || !is_readable($path) || !class_exists(\Google\Client::class)) {
                throw new \RuntimeException('Android reCAPTCHA credentials unavailable');
            }
            $google = $this->googleClientFactory ? ($this->googleClientFactory)() : new \Google\Client();
            $google->setAuthConfig($path);
            $google->setScopes(['https://www.googleapis.com/auth/cloud-platform']);
            $access = $google->fetchAccessTokenWithAssertion();
            $accessToken = is_array($access) ? ($access['access_token'] ?? '') : '';
            if (!is_string($accessToken) || $accessToken === '') throw new \RuntimeException('Google access token unavailable');

            $http = $this->client ?? \Config\Services::curlrequest([], null, null, false);
            $request = service('request');
            $agent = $request->getUserAgent();
            $userAgent = is_object($agent) && method_exists($agent, 'getAgentString')
                ? (string) $agent->getAgentString()
                : (string) $agent;
            $response = $http->post(
                'https://recaptchaenterprise.googleapis.com/v1/projects/' . rawurlencode($this->config->androidProjectId) . '/assessments',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                        'Accept' => 'application/json',
                    ],
                    'json' => ['event' => [
                        'token' => $token,
                        'siteKey' => $this->config->androidSiteKey,
                        'expectedAction' => $expectedAction,
                        'userAgent' => substr($userAgent, 0, 512),
                        'userIpAddress' => (string) $request->getIPAddress(),
                    ]],
                    'connect_timeout' => 3, 'timeout' => 8, 'verify' => true,
                    'http_errors' => false, 'allow_redirects' => false,
                ]
            );
            if ($response->getStatusCode() !== 200) throw new \RuntimeException('Assessment request rejected');
            $body = json_decode((string) $response->getBody(), true);
            $properties = is_array($body) ? ($body['tokenProperties'] ?? null) : null;
            $score = $body['riskAnalysis']['score'] ?? null;
            if (!is_array($properties) || ($properties['valid'] ?? false) !== true
                || ($properties['action'] ?? null) !== $expectedAction
                || ($properties['androidPackageName'] ?? null) !== $this->config->androidPackageName
                || !is_numeric($score) || (float) $score < $this->config->androidMinimumScore) {
                return 'Verifikasi keamanan belum berhasil. Silakan coba lagi.';
            }
            return null;
        } catch (\Throwable $error) {
            // Never log credentials, access tokens, CAPTCHA tokens, or provider response bodies.
            log_message('error', 'Android reCAPTCHA assessment failed: ' . get_class($error));
            return 'Verifikasi keamanan sedang terganggu. Silakan coba beberapa saat lagi.';
        }
    }
}
