<?php

use App\Services\Payments\CheckoutIdempotencyLedger;
use App\Services\Payments\NativeCheckoutOrderService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;

/** Opt-in checkout transaction test on a fresh, isolated MySQL 8 schema with fake Midtrans. */
final class NativeCheckoutOrderMysqlStagingTest extends CIUnitTestCase
{
    private ?BaseConnection $auditDb = null;
    private bool $hadCheckoutFlag = false;
    private $oldCheckoutFlag;
    private bool $hadProductionKey = false;
    private $oldProductionKey;
    private array $tables = [
        'barang', 'pembeli', 'pemesanan', 'customer_addresses', 'shipping_methods',
        'shipping_quotes', 'order_shipments', 'customer_checkout_requests',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $path = getenv('LUNA_ANDROID_CHECKOUT_STAGING_CONNECTION');
        if (!$path) $this->markTestSkipped('Opt-in isolated MySQL checkout staging only.');

        $config = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (($config['host'] ?? null) !== '127.0.0.1'
            || ($config['android_checkout'] ?? null) !== 'android_checkout_audit'
            || ($config['user'] ?? null) !== 'shipping_audit'
            || !is_int($config['port'] ?? null)
            || $config['port'] < 1024 || $config['port'] === 3306
            || empty($config['password'])) {
            throw new RuntimeException('Non-isolated Android checkout database rejected.');
        }

        $this->auditDb = \Config\Database::connect([
            'DSN' => '', 'hostname' => $config['host'], 'port' => $config['port'],
            'username' => $config['user'], 'password' => $config['password'],
            'database' => $config['android_checkout'], 'DBDriver' => 'MySQLi', 'DBPrefix' => '',
            'pConnect' => false, 'DBDebug' => true, 'charset' => 'utf8mb4',
            'DBCollat' => 'utf8mb4_unicode_ci', 'strictOn' => true,
            'failover' => [], 'encrypt' => false,
        ], false);

        $identity = $this->auditDb->query('SELECT DATABASE() AS db, @@port AS port, VERSION() AS version')->getRowArray();
        if (($identity['db'] ?? null) !== 'android_checkout_audit'
            || (int)($identity['port'] ?? 0) !== $config['port']
            || !str_starts_with((string)($identity['version'] ?? ''), '8.')) {
            throw new RuntimeException('Android checkout staging identity mismatch.');
        }
        $existing = $this->auditDb->query(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()'
        )->getResultArray();
        if ($existing) throw new RuntimeException('Checkout audit schema must be empty before tests.');

        $this->setSharedTestConnection($this->auditDb);
        $this->hadCheckoutFlag = array_key_exists('LUNAREA_ANDROID_CHECKOUT_ENABLED', $_ENV);
        $this->oldCheckoutFlag = $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] ?? null;
        $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = $_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = 'true';
        $this->hadProductionKey = array_key_exists('MIDTRANS_PRODUCTION_KEY', $_ENV);
        $this->oldProductionKey = $_ENV['MIDTRANS_PRODUCTION_KEY'] ?? null;
        $_ENV['MIDTRANS_PRODUCTION_KEY'] = $_SERVER['MIDTRANS_PRODUCTION_KEY'] = 'isolated-checkout-test-key';

        $this->createSchema();
        $this->seedCheckout();
    }

    protected function tearDown(): void
    {
        if ($this->auditDb) {
            foreach (array_reverse($this->tables) as $table) {
                if ($this->auditDb->tableExists($table)) $this->auditDb->query('DROP TABLE `' . $table . '`');
            }
            $this->auditDb->close();
        }
        $this->setSharedTestConnection(null);
        if ($this->hadCheckoutFlag) $_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED'] = $this->oldCheckoutFlag;
        else unset($_ENV['LUNAREA_ANDROID_CHECKOUT_ENABLED']);
        unset($_SERVER['LUNAREA_ANDROID_CHECKOUT_ENABLED']);
        if ($this->hadProductionKey) $_ENV['MIDTRANS_PRODUCTION_KEY'] = $this->oldProductionKey;
        else unset($_ENV['MIDTRANS_PRODUCTION_KEY']);
        unset($_SERVER['MIDTRANS_PRODUCTION_KEY']);
        parent::tearDown();
    }

    public function testMysqlInnoDbCheckoutPersistsOrderAndReplaysWithoutSecondCharge(): void
    {
        $email = 'android-mysql-buyer@example.test';
        $input = ['address_id' => 1, 'shipping_quote_token' => 'mysql-quote-token-01', 'payment_method' => 'bca'];
        $preview = $this->preview($email);
        $charges = 0;
        $service = $this->service($preview, $charges);

        $created = $service->create($email, $input, 'mysql-checkout-idempotency-01');
        $replayed = $service->create($email, $input, 'mysql-checkout-idempotency-01');

        $this->assertSame(201, $created['http_status']);
        $this->assertSame('Menunggu Pembayaran', $created['result']['order']['status']);
        $this->assertSame(1100, $created['result']['order']['amount']);
        $this->assertSame(200, $replayed['http_status']);
        $this->assertTrue($replayed['result']['replayed']);
        $this->assertSame($created['result']['order']['id'], $replayed['result']['order']['id']);
        $this->assertSame(1, $charges, 'MySQL idempotent replay must not contact Midtrans a second time.');
        $this->assertSame('3', (string)$this->auditDb->table('barang')->where('id', 'P1')->get()->getRowArray()['stok']);
        $this->assertSame('[]', (string)$this->auditDb->table('pembeli')->where('email_user', $email)->get()->getRowArray()['keranjang']);
        $this->assertSame(1, $this->auditDb->table('pemesanan')->countAllResults());
        $this->assertSame(1, $this->auditDb->table('order_shipments')->countAllResults());
        $this->assertNotNull($this->auditDb->table('shipping_quotes')->where('token', 'mysql-quote-token-01')->get()->getRowArray()['used_at']);
        $this->assertSame('completed', $this->auditDb->table('customer_checkout_requests')->get()->getRowArray()['state']);
    }

    public function testExpiredMysqlQuoteRollsBackReservedStockAndNeverCharges(): void
    {
        $this->auditDb->table('shipping_quotes')->where('token', 'mysql-quote-token-01')->update([
            'expires_at' => gmdate('Y-m-d H:i:s', time() - 60),
        ]);
        $email = 'android-mysql-buyer@example.test';
        $charges = 0;
        $service = $this->service($this->preview($email), $charges);

        try {
            $service->create($email, [
                'address_id' => 1, 'shipping_quote_token' => 'mysql-quote-token-01', 'payment_method' => 'bca',
            ], 'mysql-checkout-expired-quote-01');
            $this->fail('Expired shipping quote must abort checkout.');
        } catch (DomainException $error) {
            $this->assertSame('Pilihan pengiriman berubah atau kedaluwarsa. Buat kutipan baru.', $error->getMessage());
        }

        $this->assertSame(0, $charges);
        $this->assertSame('5', (string)$this->auditDb->table('barang')->where('id', 'P1')->get()->getRowArray()['stok']);
        $this->assertSame(0, $this->auditDb->table('pemesanan')->countAllResults());
        $this->assertSame(0, $this->auditDb->table('order_shipments')->countAllResults());
        $this->assertNull($this->auditDb->table('shipping_quotes')->where('token', 'mysql-quote-token-01')->get()->getRowArray()['used_at']);
        $this->assertSame('failed', $this->auditDb->table('customer_checkout_requests')->get()->getRowArray()['state']);
    }

    private function service(object $preview, int &$charges): NativeCheckoutOrderService
    {
        return new NativeCheckoutOrderService(
            $this->auditDb,
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
    }

    private function preview(string $email): object
    {
        $cart = [['id' => 'P1', 'varian' => 'Default', 'jumlah' => 2]];
        $fingerprint = CheckoutIdempotencyLedger::fingerprint($cart);
        return new class($email, $fingerprint) {
            public function __construct(private string $email, private string $fingerprint) {}
            public function preview(string $email, array $input): array
            {
                if ($email !== $this->email) throw new RuntimeException('Unexpected staging customer.');
                return [
                    'items' => [[
                        'product_id' => 'P1', 'name' => 'Produk Fixture', 'variant' => 'Default',
                        'quantity' => 2, 'unit_price' => 500, 'line_total' => 1000,
                    ]],
                    'cart_fingerprint' => $this->fingerprint,
                    'shipping' => ['courier' => 'JNE', 'service' => 'REG', 'cost' => 100],
                    'voucher_discount' => 0, 'voucher_id' => 0, 'voucher_claim_id' => 0,
                    'cashback_after_order' => 0, 'points_applied' => 0, 'total' => 1100,
                    'payment_method' => 'bca',
                    'midtrans_item_details' => [
                        ['id' => 'P1', 'name' => 'Produk Fixture (Default)', 'price' => 500, 'quantity' => 2],
                        ['id' => 'Ongkir', 'name' => 'Ongkir JNE', 'price' => 100, 'quantity' => 1],
                    ],
                ];
            }
        };
    }

    private function createSchema(): void
    {
        $ddl = [
            'CREATE TABLE barang (id VARCHAR(30) PRIMARY KEY, active TINYINT NOT NULL, stok VARCHAR(255) NOT NULL, varian TEXT NOT NULL) ENGINE=InnoDB',
            'CREATE TABLE pembeli (email_user VARCHAR(254) PRIMARY KEY, nama VARCHAR(120) NOT NULL, nohp VARCHAR(20) NOT NULL, gender ENUM(\'pria\',\'wanita\') NULL DEFAULT NULL, gender_changed_at DATETIME NULL DEFAULT NULL, keranjang LONGTEXT NOT NULL, poin LONGTEXT NOT NULL) ENGINE=InnoDB',
            'CREATE TABLE pemesanan (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, email_cus VARCHAR(254) NOT NULL, nama_pen VARCHAR(255) NOT NULL, hp_pen VARCHAR(15) NOT NULL, alamat_pen VARCHAR(255) NOT NULL, resi VARCHAR(50) NOT NULL, id_midtrans VARCHAR(100) NOT NULL UNIQUE, items LONGTEXT NOT NULL, status VARCHAR(50) NOT NULL, kurir VARCHAR(50) NOT NULL, data_mid LONGTEXT NOT NULL, note VARCHAR(100) NOT NULL, diskonVoucher INT NOT NULL, idVoucher INT NOT NULL, cashback INT NOT NULL, pakai_poin INT NOT NULL, KEY ix_pending_customer (email_cus,status)) ENGINE=InnoDB',
            'CREATE TABLE customer_addresses (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, email_user VARCHAR(254) NOT NULL, recipient_name VARCHAR(255) NOT NULL, recipient_phone VARCHAR(30) NOT NULL, address_data LONGTEXT NOT NULL, is_default TINYINT NOT NULL DEFAULT 0, created_at DATETIME NULL, updated_at DATETIME NULL, KEY ix_address_owner (email_user,id)) ENGINE=InnoDB',
            'CREATE TABLE shipping_methods (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, code VARCHAR(50) NOT NULL UNIQUE, is_active TINYINT NOT NULL, settings LONGTEXT NULL) ENGINE=InnoDB',
            'CREATE TABLE shipping_quotes (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, token VARCHAR(100) NOT NULL UNIQUE, email_user VARCHAR(254) NOT NULL, shipping_method_code VARCHAR(50) NOT NULL, provider VARCHAR(50) NOT NULL, courier_code VARCHAR(50) NULL, courier_name VARCHAR(100) NULL, service_code VARCHAR(50) NULL, service_name VARCHAR(100) NULL, cost BIGINT NOT NULL, etd VARCHAR(100) NULL, expires_at DATETIME NOT NULL, used_at DATETIME NULL, KEY ix_quote_owner (email_user,used_at)) ENGINE=InnoDB',
            'CREATE TABLE order_shipments (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, id_midtrans VARCHAR(100) NOT NULL, shipping_quote_token VARCHAR(100) NOT NULL, shipping_method_code VARCHAR(50) NOT NULL, provider VARCHAR(50) NOT NULL, courier_code VARCHAR(50) NULL, courier_name VARCHAR(100) NULL, service_code VARCHAR(50) NULL, service_name VARCHAR(100) NULL, shipping_cost BIGINT NOT NULL, etd VARCHAR(100) NULL, tracking_number VARCHAR(100) NULL, shipping_status VARCHAR(50) NOT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, KEY ix_shipment_order (id_midtrans)) ENGINE=InnoDB',
            'CREATE TABLE customer_checkout_requests (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, email_user VARCHAR(254) NOT NULL, idempotency_key_hash CHAR(64) NOT NULL, request_hash CHAR(64) NOT NULL, order_id VARCHAR(100) NOT NULL UNIQUE, state VARCHAR(16) NOT NULL DEFAULT \'processing\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE KEY uq_customer_key (email_user,idempotency_key_hash), KEY ix_checkout_state (state,updated_at)) ENGINE=InnoDB',
        ];
        foreach ($ddl as $statement) $this->auditDb->query($statement);
    }

    private function seedCheckout(): void
    {
        $email = 'android-mysql-buyer@example.test';
        $address = ['add' => 'Fixture address', 'desa' => 'Tembalang', 'kec' => 'Tembalang', 'kab' => 'Semarang', 'prov' => 'Jawa Tengah', 'kodepos' => '50275'];
        $this->auditDb->table('barang')->insert(['id' => 'P1', 'active' => 1, 'stok' => '5', 'varian' => '["Default"]']);
        $this->auditDb->table('pembeli')->insert(['email_user' => $email, 'nama' => 'Fixture Buyer', 'nohp' => '081234567890', 'gender' => 'pria', 'gender_changed_at' => null, 'keranjang' => json_encode([['id' => 'P1', 'varian' => 'Default', 'jumlah' => 2]]), 'poin' => '[]']);
        $this->auditDb->table('customer_addresses')->insert(['id' => 1, 'email_user' => $email, 'recipient_name' => 'Fixture Receiver', 'recipient_phone' => '081234567890', 'address_data' => json_encode($address)]);
        $this->auditDb->table('shipping_methods')->insert(['id' => 1, 'code' => 'rajaongkir', 'is_active' => 1, 'settings' => '{}']);
        $this->auditDb->table('shipping_quotes')->insert([
            'token' => 'mysql-quote-token-01', 'email_user' => $email, 'shipping_method_code' => 'rajaongkir',
            'provider' => 'rajaongkir', 'courier_code' => 'jne', 'courier_name' => 'JNE',
            'service_code' => 'REG', 'service_name' => 'Reguler', 'cost' => 100,
            'expires_at' => gmdate('Y-m-d H:i:s', time() + 1800), 'used_at' => null,
        ]);
    }

    private function setSharedTestConnection(?BaseConnection $connection): void
    {
        $property = new ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $property->setAccessible(true);
        $instances = $property->getValue();
        if ($connection === null) unset($instances['tests']);
        else $instances['tests'] = $connection;
        $property->setValue(null, $instances);
    }
}
