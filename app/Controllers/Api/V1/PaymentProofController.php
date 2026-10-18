<?php

namespace App\Controllers\Api\V1;

use App\Services\Payments\ManualPaymentProof;
use Config\Database;

final class PaymentProofController extends ApiController
{
    public function upload(string $orderId)
    {
        $email = $this->authEmail();
        $throttler = service('throttler');
        if (!$throttler->check('lunarea-api-proof-customer-' . hash('sha256', $email), 5, 3600)
            || !$throttler->check('lunarea-api-proof-order-' . hash('sha256', $email . '|' . $orderId), 3, 3600)) {
            return $this->failure('RATE_LIMITED', 'Terlalu banyak pengiriman bukti. Coba lagi nanti.', 429);
        }
        $file = $this->request->getFile('proof');
        if (!$file || !$file->isValid() || $file->hasMoved() || $file->getSize() > 5 * 1024 * 1024) {
            return $this->failure('INVALID_PAYMENT_PROOF', 'Pilih gambar bukti pembayaran yang valid (maksimal 5 MB).', 422);
        }
        $image = @file_get_contents($file->getTempName());
        if (!is_string($image) || $image === '') return $this->failure('INVALID_PAYMENT_PROOF', 'Bukti pembayaran belum dapat dibaca.', 422);
        try {
            $saved = (new ManualPaymentProof(Database::connect()))->save($orderId, $email, $image);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API payment proof upload failed: ' . get_class($e));
            return $this->failure('PAYMENT_PROOF_UNAVAILABLE', 'Bukti pembayaran belum dapat disimpan. Coba lagi nanti.', 503);
        }
        if (!$saved) return $this->failure('PAYMENT_PROOF_NOT_ACCEPTED', 'Pesanan tidak tersedia untuk menerima bukti pembayaran. Periksa status pesanan.', 409);
        return $this->success(['submitted' => true, 'message' => 'Bukti pembayaran berhasil dikirim dan menunggu pemeriksaan.'], [], 201);
    }
}
