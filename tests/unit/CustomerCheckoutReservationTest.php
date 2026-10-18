<?php

namespace Tests\Unit;

use App\Services\Payments\CustomerCheckoutReservation;
use Config\Database;
use DomainException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CustomerCheckoutReservationTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->db->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, poin TEXT NOT NULL, keranjang TEXT NOT NULL)');
        $this->db->query('CREATE TABLE voucher (id INTEGER PRIMARY KEY, active INTEGER NOT NULL, jadwal TEXT NOT NULL)');
        $this->db->query('CREATE TABLE voucher_claimed (id INTEGER PRIMARY KEY, id_voucher INTEGER NOT NULL, email_user TEXT NOT NULL, active INTEGER NOT NULL, kadaluarsa TEXT NOT NULL)');
    }

    protected function tearDown(): void
    {
        $this->db->close();
        parent::tearDown();
    }

    public function testConsumesPointsAndClaimsVoucherInsideCallerTransaction(): void
    {
        $this->buyer();
        $this->voucher('2099-12-31');
        $this->db->transBegin();
        $result = (new CustomerCheckoutReservation($this->db))->reserve('buyer@example.test', 7, 35);
        $this->db->transCommit();

        self::assertSame(35, $result['points_used']);
        self::assertSame(70, $result['voucher_id']);
        self::assertSame([
            ['nominal' => 0, 'active' => false, 'kadaluarsa' => '2099-12-31'],
            ['nominal' => 10, 'active' => true, 'kadaluarsa' => '2099-12-31'],
        ], json_decode($this->buyerPoints(), true));
        self::assertSame('0', (string)$this->db->table('voucher_claimed')->where('id', 7)->get()->getRowArray()['active']);
    }

    public function testInsufficientPointsAndExpiredVoucherAreRejected(): void
    {
        self::expectException(DomainException::class);
        CustomerCheckoutReservation::consumePoints([['nominal' => 10, 'active' => true]], 11);
    }

    public function testExpiredVoucherFailureCanRollBackPriorPointReservation(): void
    {
        $this->buyer();
        $this->voucher('2020-01-01');
        $this->db->transBegin();
        try {
            (new CustomerCheckoutReservation($this->db))->reserve('buyer@example.test', 7, 20);
            self::fail('Expired voucher should be rejected.');
        } catch (DomainException $e) {
            $this->db->transRollback();
            self::assertSame('Voucher tidak tersedia atau sudah kedaluwarsa.', $e->getMessage());
        }
        self::assertSame(45, array_sum(array_map(static fn($p) => !empty($p['active']) ? (int)$p['nominal'] : 0, json_decode($this->buyerPoints(), true))));
        self::assertSame('1', (string)$this->db->table('voucher_claimed')->where('id', 7)->get()->getRowArray()['active']);
    }

    public function testRequiresCallerTransaction(): void
    {
        $this->buyer();
        $this->expectException(RuntimeException::class);
        (new CustomerCheckoutReservation($this->db))->reserve('buyer@example.test', 0, 0);
    }

    public function testRejectsChangedCartAndClearsMatchingCartAtomically(): void
    {
        $this->buyer();
        $cart = [['id' => 'P1', 'varian' => 'Oak', 'jumlah' => 2]];
        $this->db->table('pembeli')->where('email_user', 'buyer@example.test')->update(['keranjang' => json_encode($cart)]);
        $this->db->transBegin();
        try {
            (new CustomerCheckoutReservation($this->db))->reserve('buyer@example.test', 0, 0, str_repeat('a', 64));
            self::fail('A stale cart fingerprint must be rejected.');
        } catch (DomainException $e) {
            $this->db->transRollback();
            self::assertStringContainsString('Keranjang berubah', $e->getMessage());
        }

        $this->db->transBegin();
        (new CustomerCheckoutReservation($this->db))->reserve(
            'buyer@example.test', 0, 0,
            \App\Services\Payments\CheckoutIdempotencyLedger::fingerprint($cart)
        );
        $this->db->transCommit();
        self::assertSame('[]', (string)$this->db->table('pembeli')->where('email_user', 'buyer@example.test')->get()->getRowArray()['keranjang']);
    }

    public function testCanReserveCustomerStateWithoutClearingCartBeforeGatewayAcceptance(): void
    {
        $this->buyer();
        $cart = [['id' => 'P2', 'varian' => 'Walnut', 'jumlah' => 1]];
        $this->db->table('pembeli')->where('email_user', 'buyer@example.test')->update(['keranjang' => json_encode($cart)]);
        $this->db->transBegin();
        (new CustomerCheckoutReservation($this->db))->reserve(
            'buyer@example.test', 0, 0,
            \App\Services\Payments\CheckoutIdempotencyLedger::fingerprint($cart), false
        );
        $this->db->transCommit();
        self::assertSame(json_encode($cart), (string)$this->db->table('pembeli')->where('email_user', 'buyer@example.test')->get()->getRowArray()['keranjang']);
    }

    private function buyer(): void
    {
        $points = [
            ['nominal' => 30, 'active' => true, 'kadaluarsa' => '2099-12-31'],
            ['nominal' => 15, 'active' => true, 'kadaluarsa' => '2099-12-31'],
        ];
        $this->db->table('pembeli')->insert(['email_user' => 'buyer@example.test', 'poin' => json_encode($points), 'keranjang' => '[]']);
    }

    private function voucher(string $expiry): void
    {
        $this->db->table('voucher')->insert(['id' => 70, 'active' => 1, 'jadwal' => '']);
        $this->db->table('voucher_claimed')->insert(['id' => 7, 'id_voucher' => 70, 'email_user' => 'buyer@example.test', 'active' => 1, 'kadaluarsa' => $expiry]);
    }

    private function buyerPoints(): string
    {
        return (string)$this->db->table('pembeli')->where('email_user', 'buyer@example.test')->get()->getRowArray()['poin'];
    }
}
