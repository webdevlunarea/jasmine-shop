<?php

namespace App\Services\Integrations;

use App\Services\Payments\PaymentSafety;

/** Re-send stored order snapshots only. Never changes payment, loyalty, orders or stock locally. */
class LunaOrderSync
{
    private $client;
    private array $settings;
    public function __construct(?array $settings = null, $client = null)
    {
        $this->settings = $settings ?? [
            'enabled' => strtolower((string)env('LUNA_SYSTEM_WEB_ORDER_ENABLED', 'false')) === 'true',
            'url' => (string)env('LUNA_SYSTEM_WEB_ORDER_URL', ''),
            'token' => (string)env('LUNA_SYSTEM_WEB_ORDER_TOKEN', ''),
        ];
        $this->client = $client;
    }

    public static function validTracking($value): bool
    {
        if (!is_string($value)) return false;
        $value = trim($value);
        return $value !== '' && strlen($value) <= 120 && !preg_match('/[<>\[\]\x00-\x1F\x7F]/', $value)
            && !preg_match('/menunggu|^(kosong|none|null|n\/a|-)$/i', $value);
    }

    public static function payload(array $order, array $testEmails, string $authProvider = 'email', ?array $pricing = null): array
    {
        if (PaymentSafety::environment($order, $testEmails, 'email_cus') !== 'production') throw new \DomainException('Pesanan uji coba tidak dikirim ke MyLuna produksi.');
        if (($order['status'] ?? '') !== 'Proses') throw new \DomainException('Sinkron pesanan hanya tersedia saat status Proses dan pembayaran sudah dikonfirmasi.');
        $id = (string)($order['id_midtrans'] ?? '');
        if (!preg_match('/^L[A-Za-z0-9_-]{1,49}$/D', $id)) throw new \DomainException('Nomor pesanan Lunarea tidak valid.');
        $items = is_string($order['items'] ?? null) ? json_decode($order['items'], true) : ($order['items'] ?? null);
        $mid = is_string($order['data_mid'] ?? null) ? json_decode($order['data_mid'], true) : ($order['data_mid'] ?? null);
        if (!is_array($items) || !$items || !is_array($mid)) throw new \DomainException('Snapshot rincian pesanan belum lengkap. Periksa data pesanan sebelum sinkron ulang.');
        $rawTime = $mid['transaction_time'] ?? null;
        if (!is_string($rawTime) || !preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:[+-]\d{2}:\d{2}|Z)?$/D', $rawTime)) {
            throw new \DomainException('Waktu transaksi asli tidak tersedia. Periksa pesanan; sistem tidak menggantinya dengan waktu sekarang.');
        }
        $time = new \DateTimeImmutable($rawTime, new \DateTimeZone('Asia/Jakarta'));
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if (is_array($dateErrors) && ($dateErrors['warning_count'] || $dateErrors['error_count'])) throw new \DomainException('Waktu transaksi tersimpan tidak valid.');
        if (!is_numeric($mid['gross_amount'] ?? null) || (float)$mid['gross_amount'] <= 0) throw new \DomainException('Nominal pembayaran tersimpan tidak valid.');
        $shipment = $order['_shipping_snapshot'] ?? [];
        $internal = ($order['_shipping_method_code'] ?? '') === 'internal_delivery';
        $courier = $internal ? 'Jasa Kirim Toko' : (string)($shipment['courier_name'] ?? $order['kurir'] ?? '');
        return [
            'order_id'=>$id, 'payment_environment'=>'production', 'is_test'=>false, 'status'=>'Proses',
            'customer_id'=>(string)($order['email_cus'] ?? ''), 'customer_email'=>(string)($order['email_cus'] ?? ''),
            'customer_name'=>(string)($order['nama_pen'] ?? ''), 'customer_phone'=>(string)($order['hp_pen'] ?? ''),
            'customer_auth_provider'=>$authProvider ?: 'email', 'is_guest'=>false,
            'email_cus'=>(string)($order['email_cus'] ?? ''), 'nama_pen'=>(string)($order['nama_pen'] ?? ''),
            'hp_pen'=>(string)($order['hp_pen'] ?? ''), 'alamat_pen'=>$order['alamat_pen'] ?? '',
            'kurir'=>(string)($order['kurir'] ?? ''), 'note'=>(string)($order['note'] ?? ''),
            'courier'=>$courier, 'service_name'=>(string)($shipment['service_name'] ?? $shipment['service_code'] ?? ''),
            'shipping_cost'=>isset($shipment['shipping_cost']) ? (int)$shipment['shipping_cost'] : null, 'tracking_number'=>self::validTracking($order['resi'] ?? null) ? trim($order['resi']) : '',
            'shipping_method_code'=>($order['_shipping_method_code'] ?? 'legacy'), 'shipping_status'=>'Disiapkan',
            'shipping_revision'=>$shipment['updated_at'] ?? null,
            'items'=>$items, 'data_mid'=>$mid,
            'transaction_time'=>$time->format(DATE_ATOM), 'website_transaction_time'=>$rawTime,
            'shipping_pricing'=>$pricing,
        ];
    }

    public function send(array $payload): array
    {
        $failure = static fn(string $message): array => ['success'=>false, 'message'=>$message];
        if (($this->settings['enabled'] ?? false) !== true) return $failure('Integrasi pesanan MyLuna belum diaktifkan di server Lunarea.');
        $url = (string)($this->settings['url'] ?? '');
        if (!$url || empty($this->settings['token'])) return $failure('URL atau token integrasi MyLuna belum lengkap di server Lunarea.');
        if (strtolower((string)parse_url($url, PHP_URL_SCHEME)) !== 'https' || parse_url($url, PHP_URL_USER) !== null) return $failure('URL integrasi MyLuna harus menggunakan HTTPS tanpa credential di URL.');
        try {
            $client = $this->client ?? \Config\Services::curlrequest([], null, null, false);
            $response = $client->post($url, [
                'json'=>$payload, 'headers'=>['Accept'=>'application/json', 'X-Luna-Webhook-Token'=>$this->settings['token']],
                'connect_timeout'=>5, 'timeout'=>20, 'verify'=>true, 'allow_redirects'=>false, 'http_errors'=>false,
            ]);
            $status = $response->getStatusCode();
            $body = json_decode($response->getBody(), true);
            if ($status < 200 || $status >= 300 || empty($body['success']) || !empty($body['data']['skipped'])
                || ($body['data']['saleNumber'] ?? '') !== $payload['order_id']
                || ($body['data']['tracking_number'] ?? '') !== $payload['tracking_number']) {
                log_message('error', 'Sinkron order {order} ke MyLuna ditolak. HTTP {code}.', ['order'=>$payload['order_id'], 'code'=>$status]);
                if ($status === 401 || $status === 403) return $failure('Koneksi MyLuna ditolak. Periksa kecocokan token integrasi kedua server.');
                if ($status === 409) return $failure('MyLuna menolak perubahan: pesanan mungkin sudah diproses/dibatalkan, atau revisi resi lebih lama. Periksa pesanan di MyLuna; jangan menganggap resi terbaru sudah tersinkron.');
                if ($status === 400 || $status === 422) return $failure('MyLuna menolak rincian pesanan. Periksa stok, varian, dan konfigurasi pengguna integrasi pada log MyLuna.');
                return $failure('MyLuna belum mengonfirmasi penerimaan pesanan. Periksa koneksi, URL integrasi, dan log MyLuna sebelum mencoba lagi.');
            }
            if (($payload['shipping_pricing']['subsidy_amount'] ?? 0) > 0 && empty($body['data']['shipping_pricing_recorded'])) return $failure('Pesanan diterima, tetapi pencatatan subsidi ongkir belum dikonfirmasi. Periksa versi backend MyLuna.');
            log_message('info', 'Sinkron order {order} ke MyLuna berhasil.', ['order'=>$payload['order_id']]);
            return ['success'=>true, 'fulfillment'=>$body['data']['fulfillment'] ?? null, 'message'=>'Pesanan '.$payload['order_id'].' dikonfirmasi tersedia di MyLuna. Cari di Penjualan, platform WEBSITE. Pesanan yang sudah ada tidak dibuat ulang.'];
        } catch (\Throwable $error) {
            log_message('error', 'Sinkron order {order} ke MyLuna mengalami gangguan transport.', ['order'=>$payload['order_id']]);
            return $failure('Koneksi MyLuna terganggu atau melewati batas waktu. Pesanan mungkin sudah diterima; periksa nomor pesanan di MyLuna sebelum mencoba lagi.');
        }
    }
}
