<?php

use App\Models\UserModel;
use App\Services\Auth\CustomerUsernames;
use App\Services\Auth\UsernamePolicy;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomerUsernamesTest extends CIUnitTestCase
{
    private $connection;
    private $migration;
    private CustomerUsernames $usernames;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, sandi TEXT, role TEXT, active TEXT, auth_provider TEXT)');
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, keranjang TEXT, transaksi TEXT, poin TEXT)');
        foreach (['one', 'two'] as $owner) {
            $this->connection->table('user')->insert(['email' => $owner . '@example.test', 'sandi' => 'original-hash', 'role' => '0', 'active' => '1', 'auth_provider' => 'google']);
            $this->connection->table('pembeli')->insert(['email_user' => $owner . '@example.test', 'keranjang' => '[123]', 'transaksi' => '["ORDER"]', 'poin' => '[50]']);
        }
        require_once APPPATH . 'Database/Migrations/2026-10-17-000002_CreateCustomerUsernames.php';
        $this->migration = new \App\Database\Migrations\CreateCustomerUsernames(\Config\Database::forge($this->connection));
        $this->migration->up();
        require_once APPPATH . 'Database/Migrations/2026-10-18-000002_AddUsernameChangeCooldown.php';
        $cooldownMigration = new \App\Database\Migrations\AddUsernameChangeCooldown(\Config\Database::forge($this->connection));
        $cooldownMigration->up();
        $this->usernames = new CustomerUsernames($this->connection);
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    public function testSaveNormalizesAndFindsTheExistingCanonicalAccount(): void
    {
        $beforeUser = $this->connection->table('user')->where('email', 'one@example.test')->get()->getRowArray();
        $beforeBuyer = $this->connection->table('pembeli')->where('email_user', 'one@example.test')->get()->getRowArray();
        $this->assertNull($this->usernames->current('one@example.test'));
        $this->assertSame('pembeli_01', $this->usernames->save('one@example.test', ' PeMbElI_01 '));
        $this->assertSame('pembeli_01', $this->usernames->current('one@example.test'));
        $this->assertSame($beforeUser, (new UserModel($this->connection))->getUserByUsername('PEMBELI_01'));
        $this->assertSame($beforeBuyer, $this->connection->table('pembeli')->where('email_user', 'one@example.test')->get()->getRowArray());
        $this->assertSame('pembeli_01', $this->usernames->save('one@example.test', 'pembeli_01'));
        $this->assertSame(1, $this->connection->table('customer_usernames')->countAllResults());
    }

    public function testSameUsernameWithDifferentCaseIsRejectedForAnotherOwner(): void
    {
        $this->usernames->save('one@example.test', 'pembeli_01');
        try { $this->usernames->save('two@example.test', 'PEMBELI_01'); $this->fail('Expected collision'); }
        catch (\DomainException $e) {
            $this->assertStringContainsString('sudah dipakai', $e->getMessage());
            $this->assertNull($this->usernames->current('two@example.test'));
            $this->assertSame('one@example.test', (new UserModel($this->connection))->getUserByUsername('pembeli_01')['email']);
        }
    }

    public function testChangingUsernamePreservesAccountAndReleasesOldAlias(): void
    {
        $this->usernames->save('one@example.test', 'nama_lama');
        try { $this->usernames->save('one@example.test', 'nama_baru'); $this->fail('Expected cooldown'); }
        catch (\DomainException $e) { $this->assertStringContainsString('setiap 6 bulan', $e->getMessage()); }
        $this->connection->table('customer_usernames')->where('email_user', 'one@example.test')
            ->update(['changed_at' => gmdate('Y-m-d H:i:s', strtotime('-7 months'))]);
        $this->usernames->save('one@example.test', 'nama_baru');
        $model = new UserModel($this->connection);
        $this->assertNull($model->getUserByUsername('nama_lama'));
        $this->assertSame('one@example.test', $model->getUserByUsername('nama_baru')['email']);
        $this->usernames->save('two@example.test', 'nama_lama');
        $this->assertSame('two@example.test', $model->getUserByUsername('nama_lama')['email']);
        $this->assertSame(2, $this->connection->table('user')->countAllResults());
    }

    /** @dataProvider invalidNames */
    public function testInvalidOrReservedNamesNeverCreateMappings($value): void
    {
        $this->assertNull(UsernamePolicy::normalize($value));
        try { $this->usernames->save('one@example.test', $value); $this->fail('Expected invalid username'); }
        catch (\DomainException $e) { $this->assertSame(0, $this->connection->table('customer_usernames')->countAllResults()); }
    }
    public static function invalidNames(): array
    {
        return [[null], [[]], [''], ['ab'], [str_repeat('a', 31)], ['1nama'], ['nama.orang'], ['nama orang'], ['user@example.test'], ['Admin'], ['tamu'], ['pembelï']];
    }

    public function testAdminAndInactiveAccountsCannotClaimUsernames(): void
    {
        foreach ([['1', '1'], ['0', '0']] as [$role, $active]) {
            $this->connection->table('user')->where('email', 'one@example.test')->update(['role' => $role, 'active' => $active]);
            try { $this->usernames->save('one@example.test', 'pembeli_01'); $this->fail('Expected invalid account'); }
            catch (\DomainException $e) { $this->assertNull($this->usernames->current('one@example.test')); }
        }
    }

    public function testDatabasePrimaryKeyRejectsCompetingClaimWithoutPrecheck(): void
    {
        $this->usernames->save('one@example.test', 'pembeli_01');
        try {
            $this->connection->table('customer_usernames')->insert(['username' => 'pembeli_01', 'email_user' => 'two@example.test']);
            $this->fail('Expected unique key violation');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            $this->assertSame(1, $this->connection->table('customer_usernames')->countAllResults());
        }
    }

    public function testDatabaseUniqueOwnerPreventsTwoAliasesForOneAccount(): void
    {
        $this->usernames->save('one@example.test', 'pembeli_01');
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $this->connection->table('customer_usernames')->insert(['username' => 'nama_lain', 'email_user' => 'one@example.test']);
    }

    public function testMigrationRollbackProtectsClaimedNames(): void
    {
        $this->usernames->save('one@example.test', 'pembeli_01');
        $this->expectException(\RuntimeException::class);
        $this->migration->down();
    }

    public function testEmailLookupSurvivesBeforeUsernameSchemaIsInstalled(): void
    {
        $this->migration->down();
        $model = new UserModel($this->connection);
        $this->assertSame('one@example.test', $model->getUser('one@example.test')['email']);
        $this->assertNull($model->getUserByUsername('pembeli_01'));
    }
}
