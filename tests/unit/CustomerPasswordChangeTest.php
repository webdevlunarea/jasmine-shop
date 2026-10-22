<?php

use App\Services\Auth\CustomerPasswordChange;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomerPasswordChangeTest extends CIUnitTestCase
{
    private $connection;
    private CustomerPasswordChange $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, sandi TEXT NOT NULL, role TEXT NOT NULL, active TEXT NOT NULL, auth_provider TEXT NOT NULL DEFAULT "google")');
        $this->connection->query('CREATE TABLE customer_password_sessions (email_user TEXT PRIMARY KEY, version TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE customer_api_tokens (id INTEGER PRIMARY KEY, email_user TEXT NOT NULL, revoked_at TEXT NULL)');
        $this->connection->table('user')->insert(['email' => 'buyer@example.test', 'sandi' => password_hash('OldPass123', PASSWORD_DEFAULT), 'role' => '0', 'active' => '1']);
        $this->connection->table('customer_password_sessions')->insert(['email_user' => 'buyer@example.test', 'version' => str_repeat('a', 64)]);
        $this->connection->table('customer_api_tokens')->insertBatch([
            ['id' => 1, 'email_user' => 'buyer@example.test', 'revoked_at' => null],
            ['id' => 2, 'email_user' => 'other@example.test', 'revoked_at' => null],
        ]);
        $this->service = new CustomerPasswordChange($this->connection);
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    public function testPasswordChangeValidatesCurrentPasswordAndInvalidatesAllCustomerSessions(): void
    {
        $this->service->change('BUYER@example.test', 'OldPass123', 'NewSecure456', 'NewSecure456');
        $user = $this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray();
        $session = $this->connection->table('customer_password_sessions')->where('email_user', 'buyer@example.test')->get()->getRowArray();
        $this->assertTrue(password_verify('NewSecure456', $user['sandi']));
        $this->assertSame('email', $user['auth_provider']);
        $this->assertNotSame(str_repeat('a', 64), $session['version']);
        $this->assertSame(1, $this->connection->table('customer_api_tokens')->where('email_user', 'buyer@example.test')->where('revoked_at IS NOT NULL')->countAllResults());
        $this->assertSame(0, $this->connection->table('customer_api_tokens')->where('email_user', 'other@example.test')->where('revoked_at IS NOT NULL')->countAllResults());
    }

    public function testWrongCurrentPasswordLeavesAccountAndSessionsUnchanged(): void
    {
        try { $this->service->change('buyer@example.test', 'wrong', 'NewSecure456', 'NewSecure456'); $this->fail('Expected current password rejection'); }
        catch (\DomainException $e) { $this->assertStringContainsString('Sandi saat ini', $e->getMessage()); }
        $user = $this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray();
        $this->assertTrue(password_verify('OldPass123', $user['sandi']));
        $this->assertSame(0, $this->connection->table('customer_api_tokens')->where('email_user', 'buyer@example.test')->where('revoked_at IS NOT NULL')->countAllResults());
    }

    public function testPasswordPolicyAndSamePasswordAreEnforced(): void
    {
        foreach ([['OldPass123', 'short', 'short'], ['OldPass123', 'OldPass123', 'OldPass123']] as [$current, $next, $confirmation]) {
            try { $this->service->change('buyer@example.test', $current, $next, $confirmation); $this->fail('Expected password rejection'); }
            catch (\DomainException $e) { $this->assertNotSame('', $e->getMessage()); }
        }
        $this->assertSame(0, $this->connection->table('customer_api_tokens')->where('email_user', 'buyer@example.test')->where('revoked_at IS NOT NULL')->countAllResults());
    }
}
