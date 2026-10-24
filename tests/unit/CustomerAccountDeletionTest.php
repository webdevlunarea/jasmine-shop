<?php

use App\Services\Auth\CustomerAccountDeletion;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class CustomerAccountDeletionTest extends CIUnitTestCase
{
    private BaseConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->connection->query('CREATE TABLE customer_account_deletion_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, status TEXT NOT NULL,
            requested_at TEXT NOT NULL, processed_at TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL
        )');
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, role TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, nama TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE customer_addresses (id INTEGER PRIMARY KEY, email_user TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, email_cus TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE android_referral_rewards (
            id INTEGER PRIMARY KEY, order_id TEXT NOT NULL, referrer_email TEXT NOT NULL, invitee_email TEXT NOT NULL
        )');
        $now = gmdate('Y-m-d H:i:s');
        $this->connection->table('customer_account_deletion_requests')->insert([
            'email_user' => 'buyer@example.test', 'status' => 'pending', 'requested_at' => $now,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $this->connection->table('user')->insert(['email' => 'buyer@example.test', 'role' => '0']);
        $this->connection->table('pembeli')->insert(['email_user' => 'buyer@example.test', 'nama' => 'Buyer']);
        $this->connection->table('customer_addresses')->insert(['email_user' => 'buyer@example.test']);
    }

    protected function tearDown(): void
    {
        $this->connection->close();
        parent::tearDown();
    }

    private function service(): CustomerAccountDeletion
    {
        return new CustomerAccountDeletion($this->connection);
    }

    public function testEligibleDeletionRemovesLoginAndProfileAndCompletesRequest(): void
    {
        $this->service()->startReview(1);
        $this->service()->deleteEligibleRequest(1);

        $this->assertNull($this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray());
        $this->assertNull($this->connection->table('pembeli')->where('email_user', 'buyer@example.test')->get()->getRowArray());
        $this->assertSame(0, $this->connection->table('customer_addresses')->where('email_user', 'buyer@example.test')->countAllResults());
        $request = $this->connection->table('customer_account_deletion_requests')->where('id', 1)->get()->getRowArray();
        $this->assertSame('completed', $request['status']);
        $this->assertNotEmpty($request['processed_at']);
    }

    public function testProtectedOrderHistoryBlocksDeletionAndRollsBack(): void
    {
        $this->service()->startReview(1);
        $this->connection->table('pemesanan')->insert(['email_cus' => 'buyer@example.test']);

        try {
            $this->service()->deleteEligibleRequest(1);
            $this->fail('Deletion must be blocked while a retained order record exists.');
        } catch (DomainException $exception) {
            $this->assertStringContainsString('riwayat transaksi', $exception->getMessage());
        }

        $this->assertNotNull($this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray());
        $this->assertNotNull($this->connection->table('pembeli')->where('email_user', 'buyer@example.test')->get()->getRowArray());
        $this->assertSame('processing', $this->connection->table('customer_account_deletion_requests')->where('id', 1)->get()->getRowArray()['status']);
    }

    public function testReviewMovesPendingRequestToProcessingOnce(): void
    {
        $this->service()->startReview(1);
        $this->assertSame('processing', $this->connection->table('customer_account_deletion_requests')->where('id', 1)->get()->getRowArray()['status']);

        try {
            $this->service()->startReview(1);
            $this->fail('A request already under review must not be started twice.');
        } catch (DomainException) {
            $this->assertSame('processing', $this->connection->table('customer_account_deletion_requests')->where('id', 1)->get()->getRowArray()['status']);
        }
    }

    public function testReferralRewardHistoryBlocksDeletion(): void
    {
        $this->service()->startReview(1);
        $this->connection->table('android_referral_rewards')->insert([
            'order_id' => 'test-order', 'referrer_email' => 'buyer@example.test', 'invitee_email' => 'other@example.test',
        ]);

        try {
            $this->service()->deleteEligibleRequest(1);
            $this->fail('Referral reward evidence must be retained for review.');
        } catch (DomainException $exception) {
            $this->assertStringContainsString('riwayat transaksi', $exception->getMessage());
        }

        $this->assertNotNull($this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray());
        $this->assertSame('processing', $this->connection->table('customer_account_deletion_requests')->where('id', 1)->get()->getRowArray()['status']);
    }

    public function testDeletionMatchesLegacyEmailCaseAndWhitespace(): void
    {
        $this->service()->startReview(1);
        $this->connection->table('customer_account_deletion_requests')->where('id', 1)->update([
            'email_user' => ' Buyer@Example.Test ',
        ]);
        $this->connection->table('user')->where('email', 'buyer@example.test')->update([
            'email' => 'Buyer@Example.Test',
        ]);
        $this->connection->table('pembeli')->where('email_user', 'buyer@example.test')->update([
            'email_user' => 'Buyer@Example.Test',
        ]);
        $this->connection->table('customer_addresses')->where('email_user', 'buyer@example.test')->update([
            'email_user' => 'Buyer@Example.Test',
        ]);

        $this->service()->deleteEligibleRequest(1);

        $this->assertSame(0, $this->connection->table('user')->countAllResults());
        $this->assertSame(0, $this->connection->table('pembeli')->countAllResults());
        $this->assertSame(0, $this->connection->table('customer_addresses')->countAllResults());
    }

    public function testPendingRequestCannotSkipReview(): void
    {
        try {
            $this->service()->deleteEligibleRequest(1);
            $this->fail('A pending request must not bypass the review step.');
        } catch (DomainException) {
            $this->assertNotNull($this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray());
            $this->assertSame('pending', $this->connection->table('customer_account_deletion_requests')->where('id', 1)->get()->getRowArray()['status']);
        }
    }

    public function testStaffAccountCannotBeDeletedByCustomerRequest(): void
    {
        $this->service()->startReview(1);
        $this->connection->table('user')->where('email', 'buyer@example.test')->update(['role' => '1']);

        try {
            $this->service()->deleteEligibleRequest(1);
            $this->fail('The customer deletion flow must not delete staff accounts.');
        } catch (DomainException) {
            $this->assertNotNull($this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray());
            $this->assertSame('processing', $this->connection->table('customer_account_deletion_requests')->where('id', 1)->get()->getRowArray()['status']);
        }
    }
}
