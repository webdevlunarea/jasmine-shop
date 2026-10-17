<?php

use App\Services\Auth\FacebookCustomers;
use CodeIgniter\Test\CIUnitTestCase;

final class FacebookCustomersTest extends CIUnitTestCase
{
    private $connection;
    private $migration;
    private FacebookCustomers $customers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, sandi TEXT, role TEXT, active TEXT, otp TEXT, waktu_otp TEXT, auth_provider TEXT)');
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, nama TEXT, nohp TEXT, alamat TEXT, wishlist TEXT, keranjang TEXT, transaksi TEXT, poin TEXT, tier TEXT, foto TEXT)');
        require_once APPPATH . 'Database/Migrations/2026-10-17-000001_CreateFacebookIdentities.php';
        $this->migration = new \App\Database\Migrations\CreateFacebookIdentities(\Config\Database::forge($this->connection));
        $this->migration->up();
        $this->customers = new FacebookCustomers($this->connection);
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    private function profile(): array { return ['id' => '987', 'email' => 'Buyer@example.test', 'name' => 'Pembeli']; }

    public function testNewAccountAndRepeatLoginUseStableFacebookIdentity(): void
    {
        $user = $this->customers->resolve($this->profile());
        $this->assertSame('buyer@example.test', $user['email']);
        $this->assertSame('0', $user['role']);
        $this->assertSame('1', $user['active']);
        $this->assertSame('facebook', $user['auth_provider']);
        $this->assertNotEmpty(password_get_info($user['sandi'])['algo']);
        $again = $this->customers->resolve(['id' => '987']);
        $this->assertSame($user['email'], $again['email']);
        $this->assertSame(1, $this->connection->table('user')->countAllResults());
        $buyer = $this->connection->table('pembeli')->get()->getRowArray();
        $this->assertSame('[]', $buyer['keranjang']);
        $this->assertSame('bronze', json_decode($buyer['tier'], true)['label']);
    }

    /** @dataProvider existingAccounts */
    public function testEmailCollisionNeverLinksOrActivatesExistingAccount(string $role, string $active): void
    {
        $this->connection->table('user')->insert(['email' => 'buyer@example.test', 'role' => $role, 'active' => $active]);
        try {
            $this->customers->resolve($this->profile());
            $this->fail('Expected an email collision');
        } catch (\DomainException $e) {
            $this->assertSame(0, $this->connection->table('facebook_identities')->countAllResults());
            $this->assertSame(0, $this->connection->table('pembeli')->countAllResults());
            $this->assertSame($active, $this->connection->table('user')->get()->getRowArray()['active']);
        }
    }
    public static function existingAccounts(): array { return [['0', '1'], ['0', '0'], ['1', '1']]; }

    public function testMissingEmailDoesNotCreateFakeAccount(): void
    {
        $this->expectException(\DomainException::class);
        $this->customers->resolve(['id' => '123']);
    }

    public function testInactiveLinkedAccountStaysInactive(): void
    {
        $this->customers->resolve($this->profile());
        $this->connection->table('user')->where('email', 'buyer@example.test')->update(['active' => '0']);
        $this->expectException(\DomainException::class);
        $this->customers->resolve(['id' => '987']);
    }

    public function testTransactionRollsBackPartialAccount(): void
    {
        $this->connection->table('pembeli')->insert(['email_user' => 'buyer@example.test']);
        try { $this->customers->resolve($this->profile()); $this->fail('Expected insert failure'); }
        catch (\RuntimeException $e) {
            $this->assertInstanceOf(\App\Services\Auth\FacebookAccountException::class, $e);
            $this->assertSame('buyer_insert', $e->stage());
            $this->assertSame(0, $this->connection->table('user')->countAllResults());
            $this->assertSame(0, $this->connection->table('facebook_identities')->countAllResults());
        }
    }

    public function testRollbackProtectsPopulatedIdentityMapping(): void
    {
        $this->customers->resolve($this->profile());
        $this->expectException(\RuntimeException::class);
        $this->migration->down();
    }

    private function existingCustomer(string $email = 'buyer@example.test', string $active = '1', string $role = '0'): void
    {
        $this->connection->table('user')->insert(['email' => $email, 'role' => $role, 'active' => $active, 'auth_provider' => 'google', 'sandi' => 'original-hash']);
        $this->connection->table('pembeli')->insert(['email_user' => $email, 'keranjang' => '[{"id":123}]', 'poin' => '[{"nominal":50}]', 'transaksi' => '["OLD-ORDER"]']);
    }

    public function testExplicitLinkAndLaterLoginPreserveOriginalAccountAndCommerceData(): void
    {
        $this->existingCustomer();
        $beforeUser = $this->connection->table('user')->get()->getRowArray();
        $beforeBuyer = $this->connection->table('pembeli')->get()->getRowArray();
        $linked = $this->customers->link($this->profile(), 'buyer@example.test');
        $this->assertSame($beforeUser, $linked);
        $this->assertTrue($this->customers->isLinked('buyer@example.test'));
        $this->assertSame($beforeUser, $this->customers->resolve(['id' => '987']));
        $this->assertSame($beforeUser, $this->connection->table('user')->get()->getRowArray());
        $this->assertSame($beforeBuyer, $this->connection->table('pembeli')->get()->getRowArray());
        $this->customers->link($this->profile(), 'buyer@example.test');
        $this->assertSame(1, $this->connection->table('facebook_identities')->countAllResults());
        $this->assertSame(1, $this->connection->table('user')->countAllResults());
    }

    public function testFacebookAlreadyOwnedByAnotherAccountNeverMoves(): void
    {
        $this->customers->resolve($this->profile());
        $this->existingCustomer('other@example.test');
        try {
            $this->customers->link(['id' => '987', 'email' => 'other@example.test'], 'other@example.test');
            $this->fail('Expected linked identity collision');
        } catch (\DomainException $e) {
            $this->assertSame('buyer@example.test', $this->connection->table('facebook_identities')->get()->getRowArray()['email_user']);
            $this->assertFalse($this->customers->isLinked('other@example.test'));
        }
    }

    public function testExistingLinkCannotBeReplacedWithAnotherFacebook(): void
    {
        $this->existingCustomer();
        $this->customers->link($this->profile(), 'buyer@example.test');
        try {
            $this->customers->link(['id' => '988', 'email' => 'buyer@example.test'], 'buyer@example.test');
            $this->fail('Expected local account collision');
        } catch (\DomainException $e) {
            $this->assertSame('987', $this->connection->table('facebook_identities')->get()->getRowArray()['facebook_id']);
            $this->assertSame(1, $this->connection->table('facebook_identities')->countAllResults());
        }
    }

    /** @dataProvider invalidLinkAccounts */
    public function testLinkRejectsInactiveAdminAndDifferentOrMissingEmail(string $active, string $role, array $profile): void
    {
        $this->existingCustomer('buyer@example.test', $active, $role);
        try { $this->customers->link($profile, 'buyer@example.test'); $this->fail('Expected invalid link'); }
        catch (\DomainException $e) { $this->assertSame(0, $this->connection->table('facebook_identities')->countAllResults()); }
    }
    public static function invalidLinkAccounts(): array
    {
        return [
            ['0', '0', ['id' => '987', 'email' => 'buyer@example.test']],
            ['1', '1', ['id' => '987', 'email' => 'buyer@example.test']],
            ['1', '0', ['id' => '987', 'email' => 'other@example.test']],
            ['1', '0', ['id' => '987']],
        ];
    }
}
