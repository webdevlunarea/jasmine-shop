<?php

namespace App\Controllers\Api\V1;

use App\Models\OrderShipmentModel;
use App\Models\PemesananModel;
use App\Services\Api\ApiPagination;
use App\Services\Api\CustomerTrackingSnapshot;
use App\Services\Payments\CustomerOrderPaymentStatus;
use App\Services\Shipping\BinderByteTrackingProvider;
use Config\Database;

class OrderController extends ApiController
{
    public function index()
    {
        $pagination = ApiPagination::resolve($this->request->getGet('page'), $this->request->getGet('per_page'), 10, 30);
        if ($pagination === null) return $this->failure('VALIDATION_ERROR', 'Nomor halaman atau jumlah data per halaman tidak valid.', 422);
        $page = $pagination['page'];
        $perPage = $pagination['per_page'];
        $model = new PemesananModel();
        $total = $model->where('email_cus', $this->authEmail())->countAllResults();
        $rows = $model->where('email_cus', $this->authEmail())->orderBy('id', 'DESC')->findAll($perPage, $pagination['offset']);
        return $this->success(['orders' => array_map(fn($row) => $this->summary($row), $rows)], [
            'page' => $page, 'per_page' => $perPage, 'total' => $total, 'pages' => (int)ceil($total / $perPage),
        ]);
    }

    public function show(string $id)
    {
        $order = (new PemesananModel())->where(['id_midtrans' => $id, 'email_cus' => $this->authEmail()])->first();
        if (!$order) return $this->failure('ORDER_NOT_FOUND', 'Pesanan tidak ditemukan.', 404);
        $items = json_decode((string)($order['items'] ?? '[]'), true);
        $payment = json_decode((string)($order['data_mid'] ?? '{}'), true);
        return $this->success(['order' => $this->summary($order) + [
            'items' => is_array($items) ? array_map(static fn($item) => [
                'product_id' => (string)($item['id'] ?? ''), 'name' => (string)($item['name'] ?? ''),
                'unit_price' => max(0, (int)($item['value'] ?? $item['price'] ?? 0)), 'quantity' => max(1, (int)($item['quantity'] ?? 1)),
            ], $items) : [],
            'payment' => [
                'type' => (string)($payment['payment_type'] ?? ''),
                'status' => (string)($payment['transaction_status'] ?? ''),
                'gross_amount' => max(0, (int)($payment['gross_amount'] ?? 0)),
                'expiry_time' => $payment['expiry_time'] ?? null,
                'va_numbers' => $this->safeVaNumbers($payment),
            ],
        ]]);
    }

    public function tracking(string $id)
    {
        $email = $this->authEmail();
        $order = $this->findOwnedOrder($id, $email);
        if (!$order) return $this->failure('ORDER_NOT_FOUND', 'Pesanan tidak ditemukan.', 404);
        $shipment = $this->findShipment($id);
        $tracking = CustomerTrackingSnapshot::make($order, $shipment);
        $tracking['live_tracking'] = null;
        if (($shipment['shipping_method_code'] ?? '') !== 'internal_delivery'
            && !empty($tracking['tracking_number']) && !empty($tracking['courier'])) {
            if ($this->allowLiveTrackingRequest($email, $id)) {
                $courierCode = BinderByteTrackingProvider::resolveCourierCode((string)($shipment['courier_code'] ?? ''));
                if ($courierCode === '') $courierCode = BinderByteTrackingProvider::resolveCourierCode((string)$tracking['courier']);
                $tracking['live_tracking'] = $this->binderByteProvider()->track($courierCode, (string)$tracking['tracking_number']);
            } else {
                $tracking['live_tracking'] = [
                    'success' => false,
                    'message' => 'Pemeriksaan status langsung terlalu sering. Coba kembali sebentar lagi.',
                ];
            }
        }
        return $this->success(['tracking' => $tracking]);
    }

    protected function findOwnedOrder(string $id, string $email): ?array
    {
        return (new PemesananModel())->where(['id_midtrans' => $id, 'email_cus' => $email])->first();
    }

    protected function findShipment(string $id): ?array
    {
        if (!Database::connect()->tableExists('order_shipments')) return null;
        return (new OrderShipmentModel())->where('id_midtrans', $id)->orderBy('id', 'DESC')->first();
    }

    protected function allowLiveTrackingRequest(string $email, string $id): bool
    {
        $throttler = service('throttler');
        $customerKey = 'lunarea-api-live-tracking-customer-' . hash('sha256', $email);
        $orderKey = 'lunarea-api-live-tracking-order-' . hash('sha256', $email . '|' . $id);
        return $throttler->check($customerKey, 12, 60) && $throttler->check($orderKey, 4, 60);
    }

    protected function binderByteProvider(): BinderByteTrackingProvider
    {
        return new BinderByteTrackingProvider();
    }

    public function paymentStatus(string $id)
    {
        $email = $this->authEmail();
        $order = (new PemesananModel())->where(['id_midtrans' => $id, 'email_cus' => $email])->first();
        if (!$order) return $this->failure('ORDER_NOT_FOUND', 'Pesanan tidak ditemukan.', 404);
        $throttler = service('throttler');
        $customerKey = 'lunarea-api-payment-customer-' . hash('sha256', $email);
        $orderKey = 'lunarea-api-payment-order-' . hash('sha256', $email . '|' . $id);
        if (!$throttler->check($customerKey, 20, 60) || !$throttler->check($orderKey, 4, 60)) {
            return $this->failure('RATE_LIMITED', 'Pemeriksaan pembayaran terlalu sering. Coba lagi sebentar.', 429);
        }
        try {
            return $this->success(['payment' => (new CustomerOrderPaymentStatus())->get($order)]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API payment status check failed: ' . get_class($e));
            return $this->failure('PAYMENT_STATUS_UNAVAILABLE', 'Status pembayaran sementara tidak dapat diperiksa.', 503);
        }
    }

    private function summary(array $order): array
    {
        $payment = json_decode((string)($order['data_mid'] ?? '{}'), true) ?: [];
        return [
            'id' => (string)$order['id_midtrans'], 'status' => (string)$order['status'],
            'created_at' => $payment['transaction_time'] ?? null,
            'amount' => max(0, (int)($payment['gross_amount'] ?? 0)),
            'courier' => (string)($order['kurir'] ?? ''), 'tracking_number' => (string)($order['resi'] ?? ''),
        ];
    }

    private function safeVaNumbers(array $payment): array
    {
        $result = [];
        foreach (($payment['va_numbers'] ?? []) as $entry) {
            if (!is_array($entry)) continue;
            $result[] = ['bank' => (string)($entry['bank'] ?? ''), 'number' => (string)($entry['va_number'] ?? '')];
        }
        if (!empty($payment['permata_va_number'])) $result[] = ['bank' => 'permata', 'number' => (string)$payment['permata_va_number']];
        return $result;
    }
}
