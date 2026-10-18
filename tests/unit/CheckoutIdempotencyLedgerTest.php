<?php

namespace Tests\Unit;

use App\Services\Payments\CheckoutIdempotencyLedger;
use Config\Database;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CheckoutIdempotencyLedgerTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $forge = Database::forge($this->db);
        $forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'idempotency_key_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'request_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'order_id' => ['type' => 'VARCHAR', 'constraint' => 100],
            'state' => ['type' => 'VARCHAR', 'constraint' => 16],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $forge->addKey('id', true);
        $forge->addUniqueKey(['email_user', 'idempotency_key_hash']);
        $forge->addUniqueKey('order_id');
        $forge->createTable('customer_checkout_requests');
        $this->db->query('CREATE TABLE pemesanan (id_midtrans TEXT PRIMARY KEY)');
    }

    protected function tearDown(): void
    {
        $this->db->close();
        parent::tearDown();
    }

    public function testClaimReplayAndMismatchedKeyReuse(): void
    {
        $ledger = new CheckoutIdempotencyLedger($this->db);
        $request = ['address_id' => 9, 'payment_method' => 'bca'];
        $hash = CheckoutIdempotencyLedger::fingerprint($request);
        $first = $ledger->claim('Buyer@example.test', 'client-key-123456789', $hash, 'LANDROID0001');
        $replay = $ledger->claim('buyer@example.test', 'client-key-123456789', $hash, 'LANDROIDOTHER');

        self::assertSame('claimed', $first['result']);
        self::assertSame('replay', $replay['result']);
        self::assertSame('processing', $replay['state']);
        self::assertSame('LANDROID0001', $replay['order_id']);

        $this->expectException(DomainException::class);
        $ledger->claim('buyer@example.test', 'client-key-123456789', CheckoutIdempotencyLedger::fingerprint(['address_id' => 10]), 'LANDROID0002');
    }

    public function testOnlyProcessingClaimCanTransitionOnce(): void
    {
        $ledger = new CheckoutIdempotencyLedger($this->db);
        $key = 'client-key-123456789';
        $ledger->claim('buyer@example.test', $key, CheckoutIdempotencyLedger::fingerprint(['cart' => [1]]), 'LANDROID0001');

        self::assertTrue($ledger->complete('buyer@example.test', $key, 'LANDROID0001'));
        self::assertFalse($ledger->complete('buyer@example.test', $key, 'LANDROID0001'));
        self::assertSame('completed', $ledger->claim('buyer@example.test', $key, CheckoutIdempotencyLedger::fingerprint(['cart' => [1]]), 'LANDROID0002')['state']);
    }

    public function testFingerprintIgnoresObjectKeyOrderButPreservesListOrder(): void
    {
        self::assertSame(
            CheckoutIdempotencyLedger::fingerprint(['options' => ['a' => 1, 'b' => 2], 'items' => [1, 2]]),
            CheckoutIdempotencyLedger::fingerprint(['items' => [1, 2], 'options' => ['b' => 2, 'a' => 1]])
        );
        self::assertNotSame(
            CheckoutIdempotencyLedger::fingerprint(['items' => [1, 2]]),
            CheckoutIdempotencyLedger::fingerprint(['items' => [2, 1]])
        );
    }

    public function testStaleOrphanProcessingClaimBecomesRetryableOnlyWhenNoOrderExists(): void
    {
        $ledger = new CheckoutIdempotencyLedger($this->db);
        $key = 'client-key-123456789';
        $hash = CheckoutIdempotencyLedger::fingerprint(['cart' => [1]]);
        $ledger->claim('buyer@example.test', $key, $hash, 'LANDROID0001');
        $this->db->table('customer_checkout_requests')->where('order_id', 'LANDROID0001')->update([
            'updated_at' => gmdate('Y-m-d H:i:s', time() - 1200),
        ]);

        $replay = $ledger->claim('buyer@example.test', $key, $hash, 'LANDROID0002');
        self::assertSame('replay', $replay['result']);
        self::assertSame('failed', $replay['state']);
        self::assertSame('LANDROID0001', $replay['order_id']);

        $ledger->claim('buyer@example.test', 'client-key-987654321', $hash, 'LANDROID0003');
        $this->db->table('pemesanan')->insert(['id_midtrans' => 'LANDROID0003']);
        $this->db->table('customer_checkout_requests')->where('order_id', 'LANDROID0003')->update([
            'updated_at' => gmdate('Y-m-d H:i:s', time() - 1200),
        ]);
        $activeOrder = $ledger->claim('buyer@example.test', 'client-key-987654321', $hash, 'LANDROID0004');
        self::assertSame('processing', $activeOrder['state']);
    }
}
