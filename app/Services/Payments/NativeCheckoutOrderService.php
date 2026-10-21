<?php

namespace App\Services\Payments;

use App\Models\CustomerAddressModel;
use App\Models\PembeliModel;
use App\Models\PemesananModel;
use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;

/** Server-authoritative order creation for the native client. */
final class NativeCheckoutOrderService
{
    private BaseConnection $db;
    private $previewFactory;
    private $chargeFactory;

    public function __construct(?BaseConnection $db = null, ?callable $previewFactory = null, ?callable $chargeFactory = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->previewFactory = $previewFactory ?? static fn() => new CheckoutPreviewService();
        $this->chargeFactory = $chargeFactory;
    }

    /** @return array{http_status:int,result:array} */
    public function create(string $email, array $input, string $idempotencyKey): array
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new DomainException('Akun pelanggan tidak valid.');
        $sandbox = PaymentSafety::isTestEmail($email, PaymentSafety::testAccountEmails());
        if (!PaymentSafety::nativeCheckoutEnabledFor($email)) {
            throw new RuntimeException('Checkout aplikasi belum diaktifkan.');
        }
        $this->assertTransactionalStorage((int)($input['voucher_claim_id'] ?? 0) > 0);
        if (strlen($idempotencyKey) < 16 || strlen($idempotencyKey) > 200) {
            throw new DomainException('Kunci checkout tidak valid. Buat permintaan checkout baru.');
        }

        $requestHash = CheckoutIdempotencyLedger::fingerprint($input);
        $candidateId = 'L' . bin2hex(random_bytes(16));
        $ledger = new CheckoutIdempotencyLedger($this->db);
        $claim = $ledger->claim($email, $idempotencyKey, $requestHash, $candidateId);

        if ($claim['result'] === 'replay') {
            $order = (new PemesananModel())->where(['id_midtrans' => $claim['order_id'], 'email_cus' => $email])->first();
            if (($claim['state'] ?? '') === 'completed' && $order) {
                return ['http_status' => 200, 'result' => ['replayed' => true, 'order' => $this->orderResult($order)]];
            }
            if (($claim['state'] ?? '') === 'failed') {
                return ['http_status' => 409, 'result' => [
                    'replayed' => true, 'state' => 'failed',
                    'message' => 'Checkout sebelumnya tidak membuat pembayaran. Perbarui ringkasan lalu buat permintaan baru.',
                ]];
            }
            if (($claim['state'] ?? '') === 'completed') throw new RuntimeException('Completed checkout order is missing');
            // Never call Midtrans a second time for a processing/ambiguous request.
            return ['http_status' => 202, 'result' => [
                'replayed' => true, 'state' => 'processing', 'order_id' => (string)$claim['order_id'],
                'message' => 'Permintaan pembayaran sedang dipastikan. Periksa status pesanan sebelum mencoba lagi.',
            ]];
        }

        try {
            if ((new PemesananModel())->getPemesananPending($email)) {
                throw new DomainException('Masih ada pesanan menunggu pembayaran. Selesaikan atau tunggu pesanan tersebut diperbarui.');
            }
            $previewService = ($this->previewFactory)();
            $initial = $previewService->preview($email, $input);
            $manualTransfer = ($initial['payment_method'] ?? '') === 'rekening';
            $addressId = filter_var($input['address_id'] ?? null, FILTER_VALIDATE_INT);
            $address = $addressId ? (new CustomerAddressModel())->owned($email, (int)$addressId) : null;
            $buyer = (new PembeliModel())->getPembeli($email);
            if (!$address || !$buyer) throw new DomainException('Alamat atau profil pelanggan tidak tersedia.');
            $addressData = json_decode((string)$address['address_data'], true);
            if (!is_array($addressData)) throw new DomainException('Alamat pengiriman perlu diperbarui.');
            $name = trim((string)($address['recipient_name'] ?? $buyer['nama'] ?? ''));
            $phone = trim((string)($address['recipient_phone'] ?? $buyer['nohp'] ?? ''));
            if ($name === '' || $phone === '') throw new DomainException('Nama penerima dan nomor telepon wajib tersedia.');
            $addressFingerprint = CheckoutIdempotencyLedger::fingerprint([
                'id' => (int)$address['id'], 'name' => (string)($address['recipient_name'] ?? ''),
                'phone' => (string)($address['recipient_phone'] ?? ''), 'address_data' => $addressData,
            ]);

            $payload = $manualTransfer ? null : (new MidtransCheckoutRequest())->build(
                $initial, $candidateId, $email, $name, $phone,
                isset($input['card_token']) ? (string)$input['card_token'] : null
            );
            $serverKey = $manualTransfer ? '' : trim((string)env($sandbox ? 'MIDTRANS_SANDBOX_KEY' : 'MIDTRANS_PRODUCTION_KEY', ''));
            if (!$manualTransfer && ($serverKey === '' || $serverKey === 'DefaultValue')) throw new RuntimeException('Payment configuration unavailable');

            $items = array_map(static fn(array $item) => [
                'id' => (string)$item['product_id'],
                'name' => substr((string)$item['name'] . ' (' . ucfirst((string)$item['variant']) . ')', 0, 50),
                'value' => (int)$item['unit_price'], 'quantity' => (int)$item['quantity'],
            ], $initial['items']);
            $addressLabel = implode(', ', array_filter([
                trim((string)($addressData['add'] ?? '')), trim((string)($addressData['alamat'] ?? '')),
                trim((string)($addressData['desa'] ?? '')), trim((string)($addressData['kec'] ?? '')),
                trim((string)($addressData['kab'] ?? '')), trim((string)($addressData['prov'] ?? '')),
                trim((string)($addressData['kodepos'] ?? '')),
            ]));
            $shippingText = 'kosong';
            $shipping = $initial['shipping'] ?? null;
            if (is_array($shipping)) {
                $shippingText = substr(trim((string)$shipping['courier'] . ' - ' . (string)$shipping['service'] . ' - Rp ' . number_format((int)$shipping['cost'], 0, ',', '.')), 0, 50);
            }
            $note = trim((string)($input['note'] ?? ''));
            if (mb_strlen($note) > 100) throw new DomainException('Catatan pesanan maksimal 100 karakter.');

            $orderData = [
                'email_cus' => $email, 'nama_pen' => mb_substr($name, 0, 255), 'hp_pen' => mb_substr($phone, 0, 15),
                'alamat_pen' => mb_substr($addressLabel, 0, 255), 'resi' => 'Menunggu pengiriman',
                'id_midtrans' => $candidateId, 'items' => json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'status' => $manualTransfer ? 'Menunggu Pembayaran Rekening' : 'Menunggu Pembayaran', 'kurir' => $shippingText, 'note' => $note,
                'diskonVoucher' => (int)$initial['voucher_discount'],
                'idVoucher' => (int)$initial['voucher_id'], 'cashback' => (int)$initial['cashback_after_order'],
                'pakai_poin' => (int)$initial['points_applied'],
            ];
        } catch (\Throwable $e) {
            $ledger->failBeforeCharge($email, $idempotencyKey, $candidateId);
            throw $e;
        }

        $transactionData = null;
        try {
            $transactionData = (new InventoryOrderReservation($this->db))->reserveAndPersist(
                $initial['items'],
                function (BaseConnection $db) use ($email, $input, $initial, $candidateId, $orderData, $payload, $sandbox, $addressFingerprint, $addressId, $manualTransfer): array {
                    $addressBuilder = $db->table('customer_addresses')->where(['id' => (int)$addressId, 'email_user' => $email]);
                    $addressSql = $addressBuilder->getCompiledSelect();
                    $lockedAddress = $db->query($addressSql . ($db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
                    $lockedAddressData = $lockedAddress ? json_decode((string)$lockedAddress['address_data'], true) : null;
                    if (!$lockedAddress || !is_array($lockedAddressData)) throw new DomainException('Alamat pengiriman berubah. Perbarui checkout.');
                    if (!hash_equals($addressFingerprint, CheckoutIdempotencyLedger::fingerprint([
                        'id' => (int)$lockedAddress['id'], 'name' => (string)($lockedAddress['recipient_name'] ?? ''),
                        'phone' => (string)($lockedAddress['recipient_phone'] ?? ''), 'address_data' => $lockedAddressData,
                    ]))) throw new DomainException('Alamat pengiriman berubah. Perbarui checkout.');
                    // Re-check while holding the buyer row lock. The earlier
                    // preflight alone races when web and Android submit together.
                    CustomerPendingOrderGuard::lockAndAssertNoPending($db, $email);
                    if (!$sandbox) {
                        $reservation = (new CustomerCheckoutReservation($db))->reserve(
                            $email, (int)$initial['voucher_claim_id'], (int)$initial['points_applied'],
                            (string)$initial['cart_fingerprint'], false
                        );
                        $orderData['idVoucher'] = (int)$reservation['voucher_id'];
                    }
                    $payment = [
                        'order_id' => $candidateId, 'gross_amount' => (int)$initial['total'],
                        'payment_type' => $manualTransfer ? 'rekening' : (string)$payload['payment_type'],
                        'transaction_status' => $manualTransfer ? 'pending-rekening' : 'pending',
                        '_app_payment_environment' => $sandbox ? 'sandbox' : 'production',
                        '_app_inventory_reserved' => !$sandbox, '_app_payment_effects_applied' => false,
                    ];
                    if ($manualTransfer) {
                        $payment['transaction_time'] = (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
                        $payment['expiry_time'] = (new \DateTimeImmutable('+1 hour', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
                        // Match the current website's manual transfer destination.
                        $payment['va_numbers'] = [['bank' => 'bri', 'va_number' => '003401001574304']];
                        $payment['account_name'] = 'Catur Bhakti Mandiri';
                    }
                    $orderData['data_mid'] = json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    if (!$db->table('pemesanan')->insert($orderData) || $db->transStatus() === false) {
                        throw new RuntimeException('Order persistence failed');
                    }

                    $quoteToken = trim((string)($input['shipping_quote_token'] ?? ''));
                    if ($quoteToken !== '') {
                        if (!$db->tableExists('shipping_quotes') || !$db->tableExists('order_shipments')) {
                            throw new RuntimeException('Shipping storage unavailable');
                        }
                        $builder = $db->table('shipping_quotes')->where(['token' => $quoteToken, 'email_user' => $email, 'used_at' => null]);
                        $sql = $builder->getCompiledSelect();
                        $quote = $db->query($sql . ($db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
                        if (!$quote || strtotime((string)$quote['expires_at']) < time()) {
                            throw new DomainException('Pilihan pengiriman berubah atau kedaluwarsa. Buat kutipan baru.');
                        }
                        $marked = $db->table('shipping_quotes')->where(['id' => $quote['id'], 'used_at' => null])
                            ->update(['used_at' => date('Y-m-d H:i:s')]);
                        if (!$marked || $db->affectedRows() !== 1) throw new DomainException('Pilihan pengiriman sudah dipakai. Buat kutipan baru.');
                        $shipment = [
                            'id_midtrans' => $candidateId, 'shipping_quote_token' => $quoteToken,
                            'shipping_method_code' => (string)$quote['shipping_method_code'], 'provider' => (string)$quote['provider'],
                            'courier_code' => $quote['courier_code'], 'courier_name' => $quote['courier_name'],
                            'service_code' => $quote['service_code'], 'service_name' => $quote['service_name'],
                            'shipping_cost' => (int)$quote['cost'], 'etd' => $quote['etd'],
                            'tracking_number' => null, 'shipping_status' => 'Menunggu pengiriman',
                            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
                        ];
                        if (!$db->table('order_shipments')->insert($shipment)) throw new RuntimeException('Shipment persistence failed');
                    }
                    return ['id_midtrans' => $candidateId, 'email_cus' => $email, 'status' => $manualTransfer ? 'Menunggu Pembayaran Rekening' : 'Menunggu Pembayaran', 'data_mid' => $orderData['data_mid']];
                },
                function () use ($previewService, $email, $input, $initial): void {
                    $fresh = $previewService->preview($email, $input);
                    if (!hash_equals($this->checkoutSnapshotHash($initial), $this->checkoutSnapshotHash($fresh))) {
                        throw new DomainException('Rincian checkout berubah. Perbarui ringkasan sebelum membayar.');
                    }
                },
                !$sandbox
            );
        } catch (\Throwable $e) {
            $ledger->failBeforeCharge($email, $idempotencyKey, $candidateId);
            throw $e;
        }

        if ($manualTransfer) {
            try {
                if (!$this->db->transBegin()) throw new RuntimeException('Manual checkout completion transaction could not start');
                $this->clearCartIfUnchanged($email, (string)$initial['cart_fingerprint']);
                if (!$ledger->complete($email, $idempotencyKey, $candidateId) || $this->db->transStatus() === false) {
                    throw new RuntimeException('Manual checkout completion persistence deferred');
                }
                if (!$this->db->transCommit()) throw new RuntimeException('Manual checkout completion commit deferred');
                return ['http_status' => 201, 'result' => ['replayed' => false, 'order' => $this->orderResult($transactionData)]];
            } catch (\Throwable $e) {
                if ((int)$this->db->transDepth > 0) $this->db->transRollback();
                log_message('error', 'Native manual checkout completion deferred: {type}', ['type' => get_class($e)]);
                return ['http_status' => 202, 'result' => [
                    'replayed' => false, 'state' => 'processing', 'order_id' => $candidateId,
                    'message' => 'Pesanan transfer manual tersimpan dan sedang dipastikan. Periksa Pesanan Saya sebelum mencoba lagi.',
                ]];
            }
        }

        try {
            $client = $this->chargeFactory
                ? ($this->chargeFactory)($sandbox, $serverKey)
                : new MidtransChargeClient($sandbox, $serverKey);
            $gateway = $client->charge($payload);
            $status = PaymentSafety::notificationStatus($gateway);
            if (!in_array($status, ['Menunggu Pembayaran', 'Proses'], true)) {
                throw new RuntimeException('Payment provider returned a non-actionable status');
            }
            $savedPayment = json_decode((string)$transactionData['data_mid'], true) ?: [];
            $savedPayment = array_merge($savedPayment, $gateway, [
                '_app_payment_environment' => $sandbox ? 'sandbox' : 'production',
                '_app_inventory_reserved' => !$sandbox,
                '_app_payment_effects_applied' => false,
            ]);
            if (!$this->db->transBegin()) throw new RuntimeException('Checkout completion transaction could not start');
            $saved = $this->db->table('pemesanan')->where('id_midtrans', $candidateId)->update([
                'status' => $status,
                'data_mid' => json_encode($savedPayment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            if (!$saved || $this->db->transStatus() === false) throw new RuntimeException('Payment response persistence deferred');
            if (in_array($status, ['Menunggu Pembayaran', 'Proses'], true)) {
                $this->clearCartIfUnchanged($email, (string)$initial['cart_fingerprint']);
            }
            if (!$ledger->complete($email, $idempotencyKey, $candidateId)) throw new RuntimeException('Checkout completion claim deferred');
            if ($this->db->transStatus() === false) throw new RuntimeException('Checkout completion persistence deferred');
            if (!$this->db->transCommit()) throw new RuntimeException('Checkout completion transaction commit deferred');
            $transactionData['status'] = $status;
            $transactionData['data_mid'] = json_encode($savedPayment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return ['http_status' => 201, 'result' => ['replayed' => false, 'order' => $this->orderResult($transactionData)]];
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            // A timeout or invalid response can be ambiguous: never call charge again
            // with this key. The durable order remains queryable and reconciliation-safe.
            log_message('error', 'Native checkout charge outcome deferred: {type}', ['type' => get_class($e)]);
            return ['http_status' => 202, 'result' => [
                'replayed' => false, 'state' => 'processing', 'order_id' => $candidateId,
                'message' => 'Pesanan tersimpan dan status pembayaran sedang dipastikan. Periksa status pesanan sebelum mencoba lagi.',
            ]];
        }
    }

    private function clearCartIfUnchanged(string $email, string $expectedFingerprint): void
    {
        $builder = $this->db->table('pembeli')->where('email_user', $email);
        $sql = $builder->getCompiledSelect();
        $buyer = $this->db->query($sql . ($this->db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
        $cart = $buyer ? json_decode((string)($buyer['keranjang'] ?? '[]'), true) : null;
        if (!is_array($cart) || !hash_equals($expectedFingerprint, CheckoutIdempotencyLedger::fingerprint($cart))) return;
        if (!$this->db->table('pembeli')->where('email_user', $email)->update(['keranjang' => '[]'])) {
            throw new RuntimeException('Cart clearing deferred');
        }
    }

    private function assertTransactionalStorage(bool $needsVoucher): void
    {
        $required = ['barang', 'pembeli', 'pemesanan', 'customer_addresses', 'shipping_quotes', 'order_shipments', 'customer_checkout_requests'];
        if ($needsVoucher) array_push($required, 'voucher_claimed', 'voucher');
        foreach ($required as $table) {
            if (!$this->db->tableExists($table)) throw new RuntimeException('Checkout storage unavailable');
        }
        if ($this->db->DBDriver !== 'MySQLi') return;
        $placeholders = implode(',', array_fill(0, count($required), '?'));
        $rows = $this->db->query(
            'SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (' . $placeholders . ')',
            $required
        )->getResultArray();
        if (count($rows) !== count($required)) throw new RuntimeException('Checkout schema is incomplete');
        foreach ($rows as $row) {
            if (strtolower((string)($row['ENGINE'] ?? '')) !== 'innodb') {
                throw new RuntimeException('Checkout requires transactional storage');
            }
        }
        $columns = [
            'barang' => ['id', 'active', 'stok', 'varian'],
            'pembeli' => ['email_user', 'nama', 'nohp', 'keranjang', 'poin'],
            'pemesanan' => ['email_cus', 'nama_pen', 'hp_pen', 'alamat_pen', 'resi', 'id_midtrans', 'items', 'status', 'kurir', 'data_mid', 'note', 'diskonVoucher', 'idVoucher', 'cashback', 'pakai_poin'],
            'customer_addresses' => ['id', 'email_user', 'recipient_name', 'recipient_phone', 'address_data'],
            'shipping_quotes' => ['id', 'token', 'email_user', 'shipping_method_code', 'provider', 'courier_code', 'courier_name', 'service_code', 'service_name', 'cost', 'etd', 'expires_at', 'used_at'],
            'order_shipments' => ['id_midtrans', 'shipping_quote_token', 'shipping_method_code', 'provider', 'courier_code', 'courier_name', 'service_code', 'service_name', 'shipping_cost', 'etd', 'tracking_number', 'shipping_status'],
            'customer_checkout_requests' => ['email_user', 'idempotency_key_hash', 'request_hash', 'order_id', 'state', 'updated_at'],
        ];
        if ($needsVoucher) {
            $columns['voucher_claimed'] = ['id', 'id_voucher', 'email_user', 'active', 'kadaluarsa'];
            $columns['voucher'] = ['id', 'active', 'jadwal'];
        }
        $expected = [];
        foreach ($columns as $table => $names) foreach ($names as $name) $expected[$table . '.' . $name] = false;
        $columnRows = $this->db->query(
            'SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN (' . $placeholders . ')',
            $required
        )->getResultArray();
        foreach ($columnRows as $column) {
            $key = (string)$column['TABLE_NAME'] . '.' . (string)$column['COLUMN_NAME'];
            if (array_key_exists($key, $expected)) $expected[$key] = true;
        }
        if (in_array(false, $expected, true)) throw new RuntimeException('Checkout schema does not match the required contract');
    }

    private function checkoutSnapshotHash(array $checkout): string
    {
        $items = array_map(static fn(array $item) => [
            'product_id' => (string)$item['product_id'], 'variant' => (string)$item['variant'],
            'quantity' => (int)$item['quantity'], 'unit_price' => (int)$item['unit_price'],
        ], $checkout['items'] ?? []);
        return CheckoutIdempotencyLedger::fingerprint([
            'cart_fingerprint' => (string)($checkout['cart_fingerprint'] ?? ''), 'items' => $items,
            'payment_method' => (string)($checkout['payment_method'] ?? ''),
            'subtotal' => (int)($checkout['subtotal'] ?? -1), 'voucher_discount' => (int)($checkout['voucher_discount'] ?? -1),
            'preorder_discount' => (int)($checkout['preorder_discount'] ?? -1), 'voucher_claim_id' => (int)($checkout['voucher_claim_id'] ?? 0),
            'voucher_id' => (int)($checkout['voucher_id'] ?? 0), 'points_applied' => (int)($checkout['points_applied'] ?? -1),
            'glass_service' => $checkout['glass_service'] ?? null, 'shipping' => $checkout['shipping'] ?? null,
            'admin_fee' => (int)($checkout['admin_fee'] ?? -1), 'total' => (int)($checkout['total'] ?? -1),
        ]);
    }

    private function orderResult(array $order): array
    {
        $payment = json_decode((string)($order['data_mid'] ?? '{}'), true) ?: [];
        return [
            'id' => (string)$order['id_midtrans'], 'status' => (string)$order['status'],
            'amount' => max(0, (int)($payment['gross_amount'] ?? 0)),
            'payment' => [
                'type' => (string)($payment['payment_type'] ?? ''),
                'transaction_status' => (string)($payment['transaction_status'] ?? ''),
                'account_name' => $payment['account_name'] ?? null,
                'expiry_time' => $payment['expiry_time'] ?? null,
                'va_numbers' => $payment['va_numbers'] ?? [],
                'permata_va_number' => $payment['permata_va_number'] ?? null,
                'payment_code' => $payment['payment_code'] ?? null,
                'bill_key' => $payment['bill_key'] ?? null,
                'biller_code' => $payment['biller_code'] ?? null,
                'qr_string' => $payment['qr_string'] ?? null,
                'actions' => $payment['actions'] ?? [],
            ],
        ];
    }
}
