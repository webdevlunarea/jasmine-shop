<?php

use App\Services\Auth\CustomerApiTokens;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomerApiTokensTest extends CIUnitTestCase
{
    private $connection;
    private CustomerApiTokens $tokens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, sandi TEXT NOT NULL, role TEXT NOT NULL, active TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE customer_password_sessions (email_user TEXT PRIMARY KEY, version TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE customer_api_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT NOT NULL, family_id TEXT NOT NULL,
            token_hash TEXT NOT NULL UNIQUE, token_type TEXT NOT NULL, password_version TEXT NOT NULL,
            device_name TEXT NULL, expires_at TEXT NOT NULL, revoked_at TEXT NULL,
            replaced_by_hash TEXT NULL, last_used_at TEXT NULL, created_at TEXT NOT NULL
        )');
        $this->connection->table('user')->insert(['email' => 'buyer@example.test', 'sandi' => 'hash', 'role' => '0', 'active' => '1']);
        $this->connection->table('customer_password_sessions')->insert(['email_user' => 'buyer@example.test', 'version' => str_repeat('a', 64)]);
        $this->tokens = new CustomerApiTokens($this->connection);
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    public function testIssuedTokensAreOpaqueHashedAndAuthenticateOnlyActiveCustomer(): void
    {
        $pair = $this->tokens->issue('BUYER@example.test', 'Pixel');
        $rows = $this->connection->table('customer_api_tokens')->get()->getResultArray();
        $this->assertSame(2, count($rows));
        $this->assertSame(['access', 'refresh'], array_column($rows, 'token_type'));
        foreach ($rows as $row) {
            $plain = $row['token_type'] === 'access' ? $pair['access_token'] : $pair['refresh_token'];
            $this->assertSame(hash('sha256', $plain), $row['token_hash']);
            $this->assertNotSame($plain, $row['token_hash']);
        }
        $this->assertSame('buyer@example.test', $this->tokens->authenticate($pair['access_token'])['email']);
        $this->assertNull($this->tokens->authenticate($pair['refresh_token']));
    }

    public function testRefreshRotatesPairAndReuseRevokesTheWholeFamily(): void
    {
        $first = $this->tokens->issue('buyer@example.test');
        $rotated = $this->tokens->rotate($first['refresh_token']);
        $this->assertNotNull($rotated);
        $this->assertNotSame($first['access_token'], $rotated['access_token']);
        $this->assertNotNull($this->tokens->authenticate($rotated['access_token']));
        $this->assertNull($this->tokens->rotate($first['refresh_token']));
        $this->assertNull($this->tokens->authenticate($rotated['access_token']));
        $this->assertSame(4, $this->connection->table('customer_api_tokens')->where('revoked_at IS NOT NULL')->countAllResults());
    }

    public function testPasswordVersionChangeRevokesOldTokenFamily(): void
    {
        $pair = $this->tokens->issue('buyer@example.test');
        $this->connection->table('customer_password_sessions')->where('email_user', 'buyer@example.test')->update(['version' => str_repeat('b', 64)]);
        $this->assertNull($this->tokens->authenticate($pair['access_token']));
        $this->assertSame(2, $this->connection->table('customer_api_tokens')->where('revoked_at IS NOT NULL')->countAllResults());
    }

    public function testRevokingOtherSessionsKeepsCurrentDeviceSessionActive(): void
    {
        $current = $this->tokens->issue('buyer@example.test', 'Perangkat ini');
        $other = $this->tokens->issue('buyer@example.test', 'Perangkat lain');
        $previousVersion = (new \App\Services\Auth\PasswordSessionVersions($this->connection))->current('buyer@example.test');
        $otherAccess = $this->connection->table('customer_api_tokens')
            ->where('token_hash', hash('sha256', $other['access_token']))->get()->getRowArray();

        $this->assertSame(1, $this->tokens->revokeOtherSessionsForAccess($current['access_token']));
        $versions = new \App\Services\Auth\PasswordSessionVersions($this->connection);
        $this->assertNotSame($previousVersion, $versions->current('buyer@example.test'));
        $this->assertFalse($versions->matches('buyer@example.test', $previousVersion));
        $this->assertNotNull($this->tokens->authenticate($current['access_token']));
        $this->assertNull($this->tokens->authenticate($other['access_token']));
        $this->assertSame(2, $this->connection->table('customer_api_tokens')
            ->where('family_id', $otherAccess['family_id'])
            ->where('revoked_at IS NOT NULL')->countAllResults());
    }

    public function testRevokingOtherSessionsAlsoInvalidatesBrowserSessionsWithoutOtherAppDevices(): void
    {
        $current = $this->tokens->issue('buyer@example.test', 'Perangkat ini');
        $previousVersion = (new \App\Services\Auth\PasswordSessionVersions($this->connection))->current('buyer@example.test');

        $this->assertSame(0, $this->tokens->revokeOtherSessionsForAccess($current['access_token']));
        $versions = new \App\Services\Auth\PasswordSessionVersions($this->connection);
        $this->assertFalse($versions->matches('buyer@example.test', $previousVersion));
        $this->assertNotNull($this->tokens->authenticate($current['access_token']));
    }

    public function testInactiveAndNonCustomerAccountsCannotReceiveTokens(): void
    {
        foreach ([['inactive@example.test', '0', '0'], ['admin@example.test', '1', '1']] as [$email, $role, $active]) {
            $this->connection->table('user')->insert(['email' => $email, 'sandi' => 'hash', 'role' => $role, 'active' => $active]);
            $this->connection->table('customer_password_sessions')->insert(['email_user' => $email, 'version' => '']);
            try { $this->tokens->issue($email); $this->fail('Expected ineligible user to be rejected'); }
            catch (RuntimeException $e) { $this->assertNotSame('', $e->getMessage()); }
        }
        $this->assertSame(0, $this->connection->table('customer_api_tokens')->countAllResults());
    }
}
