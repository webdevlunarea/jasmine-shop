<?php

namespace App\Services\Payments;

/** Server-to-server charge client with certificate verification and strict response validation. */
final class MidtransChargeClient
{
    private bool $sandbox;
    private string $key;
    private $transport;

    public function __construct(bool $sandbox, string $key, ?callable $transport = null)
    {
        $this->sandbox = $sandbox;
        $this->key = trim($key);
        $this->transport = $transport;
    }

    public function charge(array $payload): array
    {
        if ($this->key === '' || $this->key === 'DefaultValue'
            || empty($payload['transaction_details']['order_id'])
            || !is_numeric($payload['transaction_details']['gross_amount'] ?? null)
            || (int)$payload['transaction_details']['gross_amount'] <= 0) {
            throw new \RuntimeException('Payment request configuration invalid');
        }

        $url = $this->sandbox ? 'https://api.sandbox.midtrans.com/v2/charge' : 'https://api.midtrans.com/v2/charge';
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->key . ':'),
            ],
            'json' => $payload,
            'timeout' => 30,
            'connect_timeout' => 5,
            'http_errors' => false,
            'verify' => true,
            'allow_redirects' => false,
        ];

        if ($this->transport !== null) {
            $response = ($this->transport)('POST', $url, $options);
            if (!is_array($response) || !isset($response['http_status'], $response['body'])) {
                throw new \RuntimeException('Payment provider response invalid');
            }
            $httpStatus = (int)$response['http_status'];
            $body = is_array($response['body']) ? $response['body'] : json_decode((string)$response['body'], true);
        } else {
            $response = \Config\Services::curlrequest()->request('POST', $url, $options);
            $httpStatus = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
        }

        if ($httpStatus < 200 || $httpStatus >= 300 || !is_array($body)
            || !isset($body['status_code'], $body['order_id'])
            || (string)$body['order_id'] !== (string)$payload['transaction_details']['order_id']
            || !str_starts_with((string)$body['status_code'], '2')) {
            throw new \RuntimeException('Payment provider rejected charge request');
        }
        return $body;
    }
}
