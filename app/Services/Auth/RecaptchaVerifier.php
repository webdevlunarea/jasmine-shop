<?php

namespace App\Services\Auth;

use Config\Recaptcha;

class RecaptchaVerifier
{
    private Recaptcha $config;
    private $client;

    public function __construct(?Recaptcha $config = null, $client = null)
    {
        $this->config = $config ?? config(Recaptcha::class);
        $this->client = $client;
    }

    /** Returns a customer-facing error, or null. No database writes or token logging. */
    public function error($token): ?string
    {
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
}
