<?php

namespace App\Services\Payments;

final class ManualPaymentProof
{
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function save(string $orderId, string $ownerEmail, string $image): bool
    {
        $info = @getimagesizefromstring($image);
        if (strlen($image) > 5 * 1024 * 1024 || !in_array($info['mime'] ?? '', ['image/jpeg','image/png','image/webp'], true)) return false;
        if (!$this->db->transBegin()) throw new \RuntimeException('Proof transaction could not start');
        try {
            $order = PaymentSafety::lockedOrder($this->db, $orderId);
            $payment = $order ? (json_decode($order['data_mid'], true) ?: []) : [];
            if (!$order || $order['email_cus'] !== $ownerEmail || $order['status'] !== 'Menunggu Pembayaran Rekening'
                || ($payment['payment_type'] ?? '') !== 'rekening' || !empty($order['bukti_bayar']) || PaymentExpiry::isDue($payment)) {
                $this->db->transRollback(); return false;
            }
            $builder = $this->db->table('pemesanan')->where('id_midtrans', $orderId)
                ->where('status', 'Menunggu Pembayaran Rekening')
                ->groupStart()->where('bukti_bayar', null)->orWhere('bukti_bayar', '')->groupEnd();
            $updated = $builder->update(['bukti_bayar'=>$image]);
            if (!$updated || $this->db->affectedRows() !== 1 || !$this->db->transStatus()) throw new \RuntimeException('Proof update failed');
            if (!$this->db->transCommit()) throw new \RuntimeException('Proof transaction commit failed');
            return true;
        } catch (\Throwable $error) { if ((int)$this->db->transDepth > 0) $this->db->transRollback(); throw $error; }
    }
}
