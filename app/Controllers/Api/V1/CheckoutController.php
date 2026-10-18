<?php

namespace App\Controllers\Api\V1;

use App\Services\Payments\CheckoutPreviewService;
use App\Services\Payments\NativeCheckoutOrderService;
use DomainException;

final class CheckoutController extends ApiController
{
    public function preview()
    {
        try {
            $result = (new CheckoutPreviewService())->preview($this->authEmail(), $this->payload());
            return $this->success(['checkout' => $result]);
        } catch (DomainException $e) {
            return $this->failure('CHECKOUT_VALIDATION_FAILED', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API checkout preview failed: ' . get_class($e));
            return $this->failure('CHECKOUT_PREVIEW_UNAVAILABLE', 'Ringkasan pembayaran sementara tidak tersedia. Coba lagi.', 503);
        }
    }

    public function createOrder()
    {
        $throttler = service('throttler');
        $email = $this->authEmail();
        if (!$throttler->check('lunarea-api-checkout-' . hash('sha256', $email), 5, 60)) {
            return $this->failure('RATE_LIMITED', 'Checkout terlalu sering diminta. Coba lagi sebentar.', 429);
        }
        try {
            $key = trim($this->request->getHeaderLine('Idempotency-Key'));
            $result = (new NativeCheckoutOrderService())->create($email, $this->payload(), $key);
            if ((int)$result['http_status'] === 409) {
                return $this->failure('CHECKOUT_RETRY_REQUIRED', (string)($result['result']['message'] ?? 'Gunakan permintaan checkout baru.'), 409);
            }
            return $this->success(['checkout' => $result['result']], [], (int)$result['http_status']);
        } catch (DomainException $e) {
            return $this->failure('CHECKOUT_VALIDATION_FAILED', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API order creation deferred: ' . get_class($e));
            return $this->failure('CHECKOUT_UNAVAILABLE', 'Checkout belum dapat diproses. Periksa status pesanan sebelum mencoba lagi.', 503);
        }
    }
}
