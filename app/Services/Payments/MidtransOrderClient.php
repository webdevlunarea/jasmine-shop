<?php

namespace App\Services\Payments;

/** Authenticated server-to-server calls. No provider body, key or URL is logged. */
final class MidtransOrderClient
{
    private bool $sandbox;
    private string $key;
    private $transport;

    public function __construct(bool $sandbox, string $key, ?callable $transport = null)
    {
        $this->sandbox = $sandbox;
        $this->key = $key;
        $this->transport = $transport;
    }

    public function request(string $orderId, string $operation): array
    {
        if (!in_array($operation, ['status', 'cancel'], true) || $orderId === '' || strlen($orderId) > 120
            || $this->key === '' || $this->key === 'DefaultValue') {
            throw new \RuntimeException('Payment request configuration invalid');
        }
        $url = ($this->sandbox ? 'https://api.sandbox.midtrans.com/v2/' : 'https://api.midtrans.com/v2/')
            . rawurlencode($orderId) . '/' . $operation;
        $options = [
            'headers'=>['Accept'=>'application/json', 'Authorization'=>'Basic ' . base64_encode($this->key . ':')],
            'timeout'=>10, 'connect_timeout'=>3, 'http_errors'=>false, 'verify'=>true, 'allow_redirects'=>false,
        ];
        $method = $operation === 'status' ? 'GET' : 'POST';
        if ($this->transport) {
            $result = ($this->transport)($method, $url, $options);
        } else {
            $response = \Config\Services::curlrequest()->request($method, $url, $options);
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) throw new \RuntimeException('Payment provider HTTP error');
            $result = json_decode($response->getBody(), true);
        }
        if (!is_array($result) || !isset($result['status_code'])) {
            throw new \RuntimeException('Payment provider rejected request');
        }
        // A successful status lookup can report transaction code 407 (expired), not 2xx.
        // HTTP success, transaction status and cancel API success are separate concepts.
        if ($operation === 'cancel' && !str_starts_with((string)$result['status_code'], '2')) throw new \RuntimeException('Payment provider rejected cancellation');
        if ($operation === 'status' && (!isset($result['order_id'], $result['transaction_status'], $result['gross_amount'])
            || (string)$result['order_id'] !== $orderId)) throw new \RuntimeException('Payment provider status missing');
        return $result;
    }
}
