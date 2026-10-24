<?php

use App\Services\Auth\CustomerRegistrationService;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomerRegistrationServiceTest extends CIUnitTestCase
{
    private $connection;
    private array $codes = [];
    private CustomerRegistrationService $registration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, sandi TEXT NOT NULL, role TEXT NOT NULL, otp TEXT NOT NULL, active TEXT NOT NULL, waktu_otp TEXT NOT NULL, auth_provider TEXT NULL, registration_method TEXT NULL, registered_at TEXT NULL)');
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, nama TEXT NOT NULL, nohp TEXT NOT NULL, alamat TEXT NOT NULL, wishlist TEXT NOT NULL, keranjang TEXT NOT NULL, transaksi TEXT NOT NULL, poin TEXT NOT NULL, tier TEXT NOT NULL, foto TEXT NOT NULL)');
        $this->connection->query('CREATE TABLE voucher (id INTEGER PRIMARY KEY, active INTEGER NOT NULL, auto_claimed INTEGER NOT NULL, jadwal TEXT NULL, durasi TEXT NULL)');
        $this->connection->query('CREATE TABLE voucher_claimed (id TEXT PRIMARY KEY, id_voucher TEXT NOT NULL, kadaluarsa TEXT NULL, email_user TEXT NOT NULL, active INTEGER NOT NULL)');
        $this->connection->table('voucher')->insert(['id' => 1, 'active' => 1, 'auto_claimed' => 1, 'jadwal' => null, 'durasi' => '+30 days']);
        $this->registration = new CustomerRegistrationService(
            $this->connection,
            function (string $email, string $code): bool { $this->codes[$email] = $code; return true; },
            static fn(string $email): bool => false
        );
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    private function create(string $email = 'buyer@example.test'): array
    {
        return $this->registration->register('Pembeli Lunarea', $email, 'securePass123', 'securePass123', '081299873465');
    }

    public function testRegistrationPersistsPendingCustomerAndVoucherThenOtpActivates(): void
    {
        $result = $this->create();
        $user = $this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray();
        $profile = $this->connection->table('pembeli')->where('email_user', 'buyer@example.test')->get()->getRowArray();
        $this->assertSame(['created' => true, 'email_sent' => true], $result);
        $this->assertSame('0', (string)$user['active']);
        $this->assertTrue(password_verify('securePass123', $user['sandi']));
        $this->assertSame('081299873465', $profile['nohp']);
        $this->assertSame(1, $this->connection->table('voucher_claimed')->where('email_user', 'buyer@example.test')->countAllResults());
        $this->assertTrue($this->registration->verify('buyer@example.test', $this->codes['buyer@example.test']));
        $verified = $this->connection->table('user')->where('email', 'buyer@example.test')->get()->getRowArray();
        $this->assertSame('1', (string)$verified['active']);
        $this->assertSame('0', (string)$verified['otp']);
        $this->assertSame('0', (string)$verified['waktu_otp']);
    }

    public function testDuplicateAndBlockedEmailAreRejectedWithoutCreatingSecondAccount(): void
    {
        $this->create();
        try { $this->create(); $this->fail('Expected duplicate email rejection'); }
        catch (\DomainException $e) { $this->assertStringContainsString('sudah terdaftar', $e->getMessage()); }
        $blocked = new CustomerRegistrationService($this->connection, null, static fn(string $email): bool => true);
        try { $blocked->register('Nama', 'blocked@example.test', 'securePass123', 'securePass123', '081299873465'); $this->fail('Expected blocked email rejection'); }
        catch (\DomainException $e) { $this->assertStringContainsString('email permanen', $e->getMessage()); }
        $this->assertSame(1, $this->connection->table('user')->countAllResults());
    }

    public function testExpiredOtpCannotActivateAndResendIssuesNewCode(): void
    {
        $this->create();
        $oldCode = $this->codes['buyer@example.test'];
        $this->connection->table('user')->where('email', 'buyer@example.test')->update(['waktu_otp' => (string)(time() - 1)]);
        try { $this->registration->verify('buyer@example.test', $oldCode); $this->fail('Expected expired OTP rejection'); }
        catch (\DomainException $e) { $this->assertStringContainsString('kedaluwarsa', $e->getMessage()); }
        $this->assertTrue($this->registration->resend('buyer@example.test'));
        $newCode = $this->codes['buyer@example.test'];
        $this->assertNotSame($oldCode, $newCode);
        $this->assertTrue($this->registration->verify('buyer@example.test', $newCode));
    }

    public function testInvalidPasswordAndPhoneAreRejectedBeforePersistence(): void
    {
        try { $this->registration->register('Nama', 'buyer@example.test', 'weak', 'weak', '081299873465'); $this->fail('Expected weak password rejection'); }
        catch (\DomainException $e) { $this->assertStringContainsString('minimal 8 karakter', $e->getMessage()); }
        try { $this->registration->register('Nama', 'buyer@example.test', 'securePass123', 'securePass123', '0123456789'); $this->fail('Expected invalid phone rejection'); }
        catch (\DomainException $e) { $this->assertStringContainsString('belum valid', $e->getMessage()); }
        $this->assertSame(0, $this->connection->table('user')->countAllResults());
    }
}
