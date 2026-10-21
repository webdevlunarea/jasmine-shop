<?php

namespace Tests\Unit;

use App\Services\Payments\CustomerPendingOrderGuard;
use Config\Database;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CustomerPendingOrderGuardTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->db->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY)');
        $this->db->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT, email_cus TEXT, status TEXT)');
        $this->db->table('pembeli')->insert(['email_user' => 'buyer@example.test']);
    }

    protected function tearDown(): void
    {
        $this->db->close();
    }

    public function testNewCheckoutIsAllowedWhenBuyerHasNoPendingOrder(): void
    {
        $this->db->transBegin();
        CustomerPendingOrderGuard::lockAndAssertNoPending($this->db, 'buyer@example.test');
        $this->db->transRollback();
        self::assertTrue(true);
    }

    public function testPendingOrderBlocksAnotherCheckoutForSameBuyer(): void
    {
        $this->db->table('pemesanan')->insert([
            'id_midtrans' => 'ORDER-1', 'email_cus' => 'buyer@example.test', 'status' => 'Menunggu Pembayaran',
        ]);
        $this->db->transBegin();
        try {
            CustomerPendingOrderGuard::lockAndAssertNoPending($this->db, 'buyer@example.test');
            self::fail('A second web/native checkout must be blocked while payment is pending.');
        } catch (DomainException $error) {
            self::assertStringContainsString('menunggu pembayaran', strtolower($error->getMessage()));
        } finally {
            $this->db->transRollback();
        }
    }
}
