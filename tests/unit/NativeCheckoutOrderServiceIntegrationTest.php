<?php

namespace Tests\Unit;

use App\Services\Payments\CheckoutIdempotencyLedger;
use App\Services\Payments\NativeCheckoutOrderService;
use Config\Database;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the full local order/stock/quote/idempotency/payment lifecycle with
 * an isolated in-memory SQLite database and a fake provider. No live charge.
 */
final class NativeCheckoutOrderServiceIntegrationTest extends TestCase
{
    private $db;
    private bool $hadEnv = false;
    private $oldEnv;
    private bool $hadServer = false;
    private $oldServer;
    private string $prefix = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->hadEnv = array_key_exists('LUNAREA_ANDROID_CHECKOUT_ENABLED', $_ENV);
        $this->oldEnv = $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] ?? null;
        $this->hadServer = array_key_exists('LUNAREA_ANDROID_CHECKOUT_ENABLED', $_SERVER);
        $this->oldServer = $_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED'] ?? null;
        $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = 'true';
        $_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = 'true';

        $this->db = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->setSharedTestConnection($this->db);
        foreach (['customer_checkout_requests', 'order_shipments', 'shipping_quotes', 'shipping_methods', 'customer_addresses', 'pemesanan', 'pembeli', 'barang'] as $table) {
            $this->db->query('DROP TABLE IF EXISTS ' . $this->prefix . $table);
        }
        $this->createSchema();
        $this->seedCheckout();
    }

    protected function tearDown(): void
    {
        if ($this->hadEnv) $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = $this->oldEnv;
        else unset($_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED']);
        if ($this->hadServer) $_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = $this->oldServer;
        else unset($_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED']);
        foreach (['customer_checkout_requests', 'order_shipments', 'shipping_quotes', 'shipping_methods', 'customer_addresses', 'pemesanan', 'pembeli', 'barang'] as $table) {
            $this->db->query('DROP TABLE IF EXISTS ' . $this->prefix . $table);
        }
        $this->setSharedTestConnection(null);
        $this->db->close();
        parent::tearDown();
    }

    public function testAcceptedPaymentPersistsOrderShipmentAndStockOnceAndSameKeyReplays(): void
    {
        $email = 'android-buyer@example.test';
        $input = ['address_id' => 1, 'shipping_quote_token' => 'quote-token-123', 'payment_method' => 'bca'];
        $preview = $this->preview($email);
        $charges = 0;
        $service = new NativeCheckoutOrderService(
            $this->db,
            static fn() => $preview,
            static function () use (&$charges) {
                $charges++;
                return new class {
                    public function charge(array $payload): array
                    {
                        return [
                            'order_id' => $payload['transaction_details']['order_id'],
                            'gross_amount' => $payload['transaction_details']['gross_amount'],
                            'payment_type' => 'bank_transfer', 'transaction_status' => 'pending',
                            'transaction_time' => '2026-10-18 12:00:00',
                            'va_numbers' => [['bank' => 'bca', 'va_number' => '1234567890']],
                            'expiry_time' => '2026-10-18 13:00:00',
                        ];
                    }
                };
            }
        );
        $key = 'android-integration-test-key';

        $created = $service->create($email, $input, $key);
        $replayed = $service->create($email, $input, $key);

        self::assertSame(201, $created['http_status']);
        self::assertSame('Menunggu Pembayaran', $created['result']['order']['status']);
        self::assertSame(1100, $created['result']['order']['amount']);
        self::assertSame(200, $replayed['http_status']);
        self::assertTrue($replayed['result']['replayed']);
        self::assertSame($created['result']['order']['id'], $replayed['result']['order']['id']);
        self::assertSame(1, $charges, 'An idempotent replay must not charge again.');
        self::assertSame('3', (string)$this->db->table('barang')->where('id', 'P1')->get()->getRowArray()['stok']);
        self::assertSame('[]', (string)$this->db->table('pembeli')->where('email_user', $email)->get()->getRowArray()['keranjang']);
        self::assertSame(1, $this->db->table('pemesanan')->countAllResults());
        self::assertSame(1, $this->db->table('order_shipments')->countAllResults());
        self::assertNotNull($this->db->table('shipping_quotes')->where('token', 'quote-token-123')->get()->getRowArray()['used_at']);
        self::assertSame('completed', $this->db->table('customer_checkout_requests')->get()->getRowArray()['state']);
    }

    public function testCheckoutRequiresGenderBeforeAnyPaymentCharge(): void
    {
        $this->db->table('pembeli')->where('email_user', 'android-buyer@example.test')->update(['gender' => null]);
        $charges = 0;
        $service = new NativeCheckoutOrderService(
            $this->db,
            fn() => $this->preview('android-buyer@example.test'),
            static function () use (&$charges) { $charges++; throw new \RuntimeException('Must not charge without gender.'); }
        );

        try {
            $service->create('android-buyer@example.test', [
                'address_id' => 1, 'shipping_quote_token' => 'quote-token-123', 'payment_method' => 'bca',
            ], 'gender-required-checkout-key');
            self::fail('Checkout must require the customer gender profile.');
        } catch (\DomainException $error) {
            self::assertSame('Pilih jenis kelamin di profil Anda sebelum melanjutkan pembelian.', $error->getMessage());
        }

        self::assertSame(0, $charges);
        self::assertSame(0, $this->db->table('pemesanan')->countAllResults());
        self::assertSame('5', (string)$this->db->table('barang')->where('id', 'P1')->get()->getRowArray()['stok']);
    }

    public function testAllowlistedSandboxAccountDoesNotReserveOrReduceProductStock(): void
    {
        $email = 'uuua5021@gmail.com';
        $this->db->table('pembeli')->where('email_user', 'android-buyer@example.test')->update(['email_user' => $email]);
        $this->db->table('customer_addresses')->where('email_user', 'android-buyer@example.test')->update(['email_user' => $email]);
        $this->db->table('shipping_quotes')->where('token', 'quote-token-123')->update(['email_user' => $email]);
        $preview = $this->preview($email);
        $service = new NativeCheckoutOrderService(
            $this->db,
            static fn() => $preview,
            static fn() => new class {
                public function charge(array $payload): array
                {
                    return [
                        'order_id' => $payload['transaction_details']['order_id'],
                        'gross_amount' => $payload['transaction_details']['gross_amount'],
                        'payment_type' => 'bank_transfer', 'transaction_status' => 'pending',
                        'va_numbers' => [['bank' => 'bca', 'va_number' => '9876543210']],
                    ];
                }
            }
        );

        $oldSandboxKey = getenv('MIDTRANS_SANDBOX_KEY');
        putenv('MIDTRANS_SANDBOX_KEY=isolated-test-key');
        try {
            $result = $service->create($email, [
                'address_id' => 1, 'shipping_quote_token' => 'quote-token-123', 'payment_method' => 'bca',
            ], 'android-sandbox-stock-test-key');
        } finally {
            putenv($oldSandboxKey === false ? 'MIDTRANS_SANDBOX_KEY' : 'MIDTRANS_SANDBOX_KEY=' . $oldSandboxKey);
        }

        self::assertSame(201, $result['http_status']);
        self::assertSame('5', (string)$this->db->table('barang')->where('id', 'P1')->get()->getRowArray()['stok']);
        self::assertSame('[]', (string)$this->db->table('pembeli')->where('email_user', $email)->get()->getRowArray()['keranjang']);
        $order = $this->db->table('pemesanan')->where('email_cus', $email)->get()->getRowArray();
        $payment = json_decode((string)$order['data_mid'], true);
        self::assertSame('sandbox', $payment['_app_payment_environment']);
        self::assertFalse($payment['_app_inventory_reserved']);
    }

    public function testManualTransferCreatesOrderAndInstructionsWithoutCallingMidtrans(): void
    {
        $email = 'android-buyer@example.test';
        $preview = $this->preview($email, 'rekening');
        $charges = 0;
        $service = new NativeCheckoutOrderService(
            $this->db,
            static fn() => $preview,
            static function () use (&$charges) {
                $charges++;
                throw new \RuntimeException('Manual transfer must never call the gateway.');
            }
        );

        $created = $service->create($email, [
            'address_id' => 1, 'shipping_quote_token' => 'quote-token-123', 'payment_method' => 'rekening',
        ], 'android-manual-transfer-test-key');
        $replayed = $service->create($email, [
            'address_id' => 1, 'shipping_quote_token' => 'quote-token-123', 'payment_method' => 'rekening',
        ], 'android-manual-transfer-test-key');

        self::assertSame(201, $created['http_status']);
        self::assertSame('Menunggu Pembayaran Rekening', $created['result']['order']['status']);
        self::assertSame('rekening', $created['result']['order']['payment']['type']);
        self::assertSame('003401001574304', $created['result']['order']['payment']['va_numbers'][0]['va_number']);
        self::assertSame('Catur Bhakti Mandiri', $created['result']['order']['payment']['account_name']);
        self::assertSame(0, $charges);
        self::assertSame(200, $replayed['http_status']);
        self::assertTrue($replayed['result']['replayed']);
        self::assertSame(1, $this->db->table('pemesanan')->countAllResults());
        self::assertSame('completed', $this->db->table('customer_checkout_requests')->get()->getRowArray()['state']);
        self::assertSame('[]', (string)$this->db->table('pembeli')->where('email_user', $email)->get()->getRowArray()['keranjang']);
    }

    public function testExpiredQuoteRollsBackStockAndPersistsNoOrderOrCharge(): void
    {
        $this->db->table('shipping_quotes')->where('token', 'quote-token-123')->update([
            'expires_at' => date('Y-m-d H:i:s', time() - 60),
        ]);
        $preview = $this->preview('android-buyer@example.test');
        $charges = 0;
        $service = new NativeCheckoutOrderService(
            $this->db,
            static fn() => $preview,
            static function () use (&$charges) {
                $charges++;
                throw new \RuntimeException('Provider should not be called.');
            }
        );

        try {
            $service->create('android-buyer@example.test', [
                'address_id' => 1, 'shipping_quote_token' => 'quote-token-123', 'payment_method' => 'bca',
            ], 'expired-quote-test-key');
            self::fail('An expired quote must stop checkout.');
        } catch (\DomainException $error) {
            self::assertSame('Pilihan pengiriman berubah atau kedaluwarsa. Buat kutipan baru.', $error->getMessage());
        }

        self::assertSame(0, $charges);
        self::assertSame('5', (string)$this->db->table('barang')->where('id', 'P1')->get()->getRowArray()['stok']);
        self::assertSame(0, $this->db->table('pemesanan')->countAllResults());
        self::assertSame(0, $this->db->table('order_shipments')->countAllResults());
        self::assertNull($this->db->table('shipping_quotes')->where('token', 'quote-token-123')->get()->getRowArray()['used_at']);
    }

    public function testAmbiguousProviderResultIsNotChargedAgainOnIdempotentReplay(): void
    {
        $email = 'android-buyer@example.test';
        $input = ['address_id' => 1, 'shipping_quote_token' => 'quote-token-123', 'payment_method' => 'bca'];
        $preview = $this->preview($email);
        $charges = 0;
        $service = new NativeCheckoutOrderService(
            $this->db,
            static fn() => $preview,
            static function () use (&$charges) {
                $charges++;
                return new class {
                    public function charge(array $payload): array
                    {
                        throw new \RuntimeException('Simulated provider timeout after request dispatch.');
                    }
                };
            }
        );
        $key = 'android-ambiguous-checkout-key';

        $first = $service->create($email, $input, $key);
        $replay = $service->create($email, $input, $key);

        self::assertSame(202, $first['http_status']);
        self::assertSame(202, $replay['http_status']);
        self::assertSame($first['result']['order_id'], $replay['result']['order_id']);
        self::assertSame(1, $charges, 'An ambiguous provider result must never be charged again on replay.');
        self::assertSame(1, $this->db->table('pemesanan')->countAllResults());
        self::assertSame('3', (string)$this->db->table('barang')->where('id', 'P1')->get()->getRowArray()['stok']);
        self::assertSame('processing', $this->db->table('customer_checkout_requests')->get()->getRowArray()['state']);
    }

    private function preview(string $email, string $method = 'bca'): object
    {
        $cart = [['id' => 'P1', 'varian' => 'Default', 'jumlah' => 2]];
        $cartFingerprint = CheckoutIdempotencyLedger::fingerprint($cart);
        return new class($email, $cartFingerprint, $method) {
            private string $email;
            private string $fingerprint;
            private string $method;
            public function __construct(string $email, string $fingerprint, string $method) { $this->email = $email; $this->fingerprint = $fingerprint; $this->method = $method; }
            public function preview(string $email, array $input): array
            {
                if ($email !== $this->email) throw new \RuntimeException('Unexpected customer.');
                return [
                    'items' => [[
                        'product_id' => 'P1', 'name' => 'Kursi Uji', 'variant' => 'Default',
                        'quantity' => 2, 'unit_price' => 500, 'line_total' => 1000,
                    ]],
                    'cart_fingerprint' => $this->fingerprint,
                    'shipping' => ['courier' => 'JNE', 'service' => 'REG', 'cost' => 100],
                    'voucher_discount' => 0, 'voucher_id' => 0, 'voucher_claim_id' => 0,
                    'cashback_after_order' => 0, 'points_applied' => 0, 'total' => 1100,
                    'payment_method' => $this->method,
                    'midtrans_item_details' => [
                        ['id' => 'P1', 'name' => 'Kursi Uji (Default)', 'price' => 500, 'quantity' => 2],
                        ['id' => 'Ongkir', 'name' => 'Ongkir JNE', 'price' => 100, 'quantity' => 1],
                    ],
                ];
            }
        };
    }

    private function createSchema(): void
    {
        $p = $this->prefix;
        $this->db->query('CREATE TABLE ' . $p . 'barang (id TEXT PRIMARY KEY, active INTEGER NOT NULL, stok TEXT NOT NULL, varian TEXT NOT NULL)');
        $this->db->query('CREATE TABLE ' . $p . 'pembeli (email_user TEXT PRIMARY KEY, nama TEXT NOT NULL, nohp TEXT NOT NULL, gender TEXT NULL, gender_changed_at TEXT NULL, keranjang TEXT NOT NULL, poin TEXT NOT NULL)');
        $this->db->query('CREATE TABLE ' . $p . 'pemesanan (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_cus TEXT NOT NULL, nama_pen TEXT NOT NULL, hp_pen TEXT NOT NULL,
            alamat_pen TEXT NOT NULL, resi TEXT NOT NULL, id_midtrans TEXT NOT NULL UNIQUE, items TEXT NOT NULL,
            status TEXT NOT NULL, kurir TEXT NOT NULL, data_mid TEXT NOT NULL, note TEXT NOT NULL,
            diskonVoucher INTEGER NOT NULL, idVoucher INTEGER NOT NULL, cashback INTEGER NOT NULL, pakai_poin INTEGER NOT NULL
        )');
        $this->db->query('CREATE TABLE ' . $p . 'customer_addresses (
            id INTEGER PRIMARY KEY, email_user TEXT NOT NULL, recipient_name TEXT NOT NULL, recipient_phone TEXT NOT NULL,
            address_data TEXT NOT NULL, created_at TEXT, updated_at TEXT
        )');
        $this->db->query('CREATE TABLE ' . $p . 'shipping_methods (id INTEGER PRIMARY KEY, code TEXT UNIQUE, is_active INTEGER NOT NULL, settings TEXT)');
        $this->db->query('CREATE TABLE ' . $p . 'shipping_quotes (
            id INTEGER PRIMARY KEY AUTOINCREMENT, token TEXT UNIQUE, email_user TEXT NOT NULL, shipping_method_code TEXT NOT NULL,
            provider TEXT NOT NULL, courier_code TEXT, courier_name TEXT, service_code TEXT, service_name TEXT,
            cost INTEGER NOT NULL, etd TEXT, expires_at TEXT NOT NULL, used_at TEXT
        )');
        $this->db->query('CREATE TABLE ' . $p . 'order_shipments (
            id INTEGER PRIMARY KEY AUTOINCREMENT, id_midtrans TEXT NOT NULL, shipping_quote_token TEXT NOT NULL,
            shipping_method_code TEXT NOT NULL, provider TEXT NOT NULL, courier_code TEXT, courier_name TEXT,
            service_code TEXT, service_name TEXT, shipping_cost INTEGER NOT NULL, etd TEXT, tracking_number TEXT,
            shipping_status TEXT NOT NULL, created_at TEXT, updated_at TEXT
        )');
        $this->db->query('CREATE TABLE ' . $p . 'customer_checkout_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, idempotency_key_hash TEXT NOT NULL,
            request_hash TEXT NOT NULL, order_id TEXT NOT NULL UNIQUE, state TEXT NOT NULL,
            created_at TEXT NOT NULL, updated_at TEXT NOT NULL, UNIQUE(email_user, idempotency_key_hash)
        )');
    }

    private function seedCheckout(): void
    {
        $email = 'android-buyer@example.test';
        $address = ['add' => 'Jalan Uji 1', 'desa' => 'Tembalang', 'kec' => 'Tembalang', 'kab' => 'Semarang', 'prov' => 'Jawa Tengah', 'kodepos' => '50275', 'prov_id' => 1, 'kab_id' => 1, 'kec_id' => 1];
        $this->db->table('barang')->insert(['id' => 'P1', 'active' => 1, 'stok' => '5', 'varian' => '["Default"]']);
        $this->db->table('pembeli')->insert(['email_user' => $email, 'nama' => 'Pembeli Uji', 'nohp' => '081234567890', 'gender' => 'pria', 'gender_changed_at' => '2026-01-01 00:00:00', 'keranjang' => json_encode([['id' => 'P1', 'varian' => 'Default', 'jumlah' => 2]]), 'poin' => '[]']);
        $this->db->table('customer_addresses')->insert(['id' => 1, 'email_user' => $email, 'recipient_name' => 'Penerima Uji', 'recipient_phone' => '081234567890', 'address_data' => json_encode($address)]);
        $this->db->table('shipping_methods')->insert(['id' => 1, 'code' => 'rajaongkir', 'is_active' => 1, 'settings' => '{}']);
        $this->db->table('shipping_quotes')->insert([
            'token' => 'quote-token-123', 'email_user' => $email, 'shipping_method_code' => 'rajaongkir',
            'provider' => 'rajaongkir', 'courier_code' => 'jne', 'courier_name' => 'JNE',
            'service_code' => 'REG', 'service_name' => 'Reguler', 'cost' => 100,
            'expires_at' => date('Y-m-d H:i:s', time() + 1800), 'used_at' => null,
        ]);
    }

    private function setSharedTestConnection($connection): void
    {
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $property->setAccessible(true);
        $instances = $property->getValue();
        if ($connection === null) {
            unset($instances['tests']);
        } else {
            $instances['tests'] = $connection;
        }
        $property->setValue(null, $instances);
    }
}
