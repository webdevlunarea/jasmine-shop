<?php

namespace App\Services\Integrations;

use App\Services\Payments\PaymentSafety;
use CodeIgniter\Database\BaseConnection;

/** Operational acknowledgement only: never changes payment, totals, stock or AWB. */
class LunaFulfillment
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function record(string $orderId, array $event, array $testEmails): void
    {
        $stage = $event['stage'] ?? '';
        if (!preg_match('/^L[A-Za-z0-9_-]{1,49}$/D', $orderId) || !in_array($stage, ['received', 'processed'], true)) {
            throw new \DomainException('Konfirmasi proses tidak valid.');
        }
        $saleId = (string)($event['sale_id'] ?? '');
        if ($saleId === '' || strlen($saleId) > 100 || preg_match('/[^A-Za-z0-9_-]/', $saleId)) throw new \DomainException('Identitas proses tidak valid.');
        $processedAt = $event['processed_at'] ?? null;
        if ($stage === 'processed') {
            if (!is_string($processedAt) || !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d{1,3})?(?:Z|[+-]\d{2}:\d{2})$/D', $processedAt)) throw new \DomainException('Waktu proses tidak valid.');
            $time = new \DateTimeImmutable($processedAt);
            $errors = \DateTimeImmutable::getLastErrors();
            if ((is_array($errors) && ($errors['warning_count'] || $errors['error_count'])) || $time->getTimestamp() > time() + 300) throw new \DomainException('Waktu proses tidak valid.');
            $processedAt = $time->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
        }
        $this->db->transBegin();
        try {
            $order = PaymentSafety::lockedOrder($this->db, $orderId);
            if (!$order || PaymentSafety::environment($order, $testEmails, 'email_cus') !== 'production'
                || !in_array($order['status'], ['Proses', 'Dikirim', 'Selesai'], true)) throw new \DomainException('Pesanan produksi yang sudah dibayar tidak ditemukan.');
            $mid = json_decode((string)$order['data_mid'], true);
            if (!is_array($mid)) throw new \DomainException('Data pesanan perlu diperiksa.');
            $old = $mid['_app_luna_fulfillment'] ?? [];
            if (!empty($old['sale_id']) && $old['sale_id'] !== $saleId) throw new \DomainException('Identitas pesanan pusat berbeda.');
            // Replays and late receipt acknowledgements never regress an already processed order.
            if (($old['stage'] ?? '') === 'processed') {
                if ($stage === 'processed' && $old['processed_at'] !== $processedAt) throw new \DomainException('Waktu proses berbeda dari konfirmasi sebelumnya.');
            } else {
                $mid['_app_luna_fulfillment'] = ['stage'=>$stage, 'sale_id'=>$saleId, 'processed_at'=>$processedAt];
                $this->db->table('pemesanan')->where('id_midtrans', $orderId)->update(['data_mid'=>json_encode($mid)]);
                if ($stage === 'processed' && $this->db->tableExists('customer_push_notifications')
                    && filter_var((string)($order['email_cus'] ?? ''), FILTER_VALIDATE_EMAIL)) {
                    $queued = (new \App\Services\Notifications\CustomerPushNotifications($this->db))->enqueue(
                        (string)$order['email_cus'], 'order_processing', 'Pesanan sedang diproses',
                        'Pesanan ' . $orderId . ' sudah diteruskan untuk diproses.',
                        ['order_id' => $orderId, 'status' => 'Proses'],
                        'processing:' . $orderId . ':' . $saleId . ':' . $processedAt
                    );
                    if (!$queued) throw new \RuntimeException('Order processing notification was not recorded.');
                }
            }
            if (!$this->db->transStatus()) throw new \RuntimeException('Fulfillment acknowledgement failed');
            $this->db->transCommit();
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }

    public static function label($mid): string
    {
        if (is_string($mid)) $mid = json_decode($mid, true);
        $stage = is_array($mid) ? ($mid['_app_luna_fulfillment']['stage'] ?? '') : '';
        return ['received'=>'Pesanan diterima pusat dan menunggu diproses', 'processed'=>'Pesanan selesai diproses pusat; menunggu pengiriman'][$stage] ?? 'Pesanan sedang ditinjau';
    }
}
