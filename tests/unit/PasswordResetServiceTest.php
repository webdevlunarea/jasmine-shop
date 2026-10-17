<?php

use App\Services\Auth\PasswordResetMailer;
use App\Services\Auth\PasswordResetService;
use App\Services\Auth\PasswordSessionVersions;
use CodeIgniter\Test\CIUnitTestCase;

final class PasswordResetServiceTest extends CIUnitTestCase
{
    private $connection;
    private $mailer;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, sandi TEXT, role TEXT, active TEXT, auth_provider TEXT)');
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, keranjang TEXT, transaksi TEXT, poin TEXT)');
        $this->connection->table('user')->insert(['email'=>'buyer@example.test','sandi'=>password_hash('OldPassword123!',PASSWORD_DEFAULT),'role'=>'0','active'=>'1','auth_provider'=>'google']);
        $this->connection->table('user')->insert(['email'=>'admin@example.test','sandi'=>'old-admin-hash','role'=>'1','active'=>'1']);
        $this->connection->table('pembeli')->insert(['email_user'=>'buyer@example.test','keranjang'=>'[123]','transaksi'=>'["OLD"]','poin'=>'[50]']);
        require_once APPPATH . 'Database/Migrations/2026-10-17-000003_CreateCustomerPasswordResets.php';
        (new \App\Database\Migrations\CreateCustomerPasswordResets(\Config\Database::forge($this->connection)))->up();
        $this->mailer = new class extends PasswordResetMailer {
            public array $codes=[]; public array $changed=[]; public bool $delivered=true;
            public function sendCode(string $recipient,string $code): bool { $this->codes[]=[$recipient,$code]; return $this->delivered; }
            public function sendChanged(string $recipient): bool { $this->changed[]=$recipient; return true; }
        };
        $this->service = new class($this->connection,$this->mailer) extends PasswordResetService {
            public int $clock=2000000000; public string $code='012345';
            protected function now(): int { return $this->clock; }
            protected function newCode(): string { return $this->code; }
        };
    }
    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }
    private function request(string $email='buyer@example.test',string $ip='192.0.2.1'): void { $this->service->request($email,$ip); }
    private function reset($code='012345'): void { $this->service->reset('buyer@example.test',$code,'NewPassword456!','NewPassword456!','192.0.2.1'); }

    public function testNumericCodePreservesLeadingZeroAndOnlyHashIsStored(): void
    {
        $this->request();
        $this->assertSame([['buyer@example.test','012345']],$this->mailer->codes);
        $row=$this->connection->table('customer_password_reset_codes')->get()->getRowArray();
        $this->assertTrue(password_verify('012345',$row['otp_hash']));
        $this->assertArrayNotHasKey('code',$row);
        $this->assertNotSame('012345',$row['otp_hash']);
        $this->assertSame(600,(int)$row['expires_at']-(int)$row['created_at']);
    }

    public function testResetChangesOnlyPasswordConsumesCodeAndRevokesOldSessions(): void
    {
        $versions=new PasswordSessionVersions($this->connection);
        $old=$versions->current('buyer@example.test');
        $buyer=$this->connection->table('pembeli')->get()->getRowArray();
        $this->request(); $this->reset();
        $user=$this->connection->table('user')->where('email','buyer@example.test')->get()->getRowArray();
        $this->assertTrue(password_verify('NewPassword456!',$user['sandi']));
        $this->assertFalse(password_verify('OldPassword123!',$user['sandi']));
        $this->assertSame('google',$user['auth_provider']);
        $this->assertSame('1',$user['active']);
        $this->assertSame($buyer,$this->connection->table('pembeli')->get()->getRowArray());
        $this->assertFalse($versions->matches('buyer@example.test',$old));
        $this->assertTrue($versions->matches('buyer@example.test',$versions->current('buyer@example.test')));
        $this->assertSame(['buyer@example.test'],$this->mailer->changed);
        $this->expectException(\DomainException::class); $this->reset();
    }

    public function testFiveWrongGuessesBlockEvenTheCorrectCode(): void
    {
        $this->request();
        for($i=0;$i<5;$i++) { try { $this->reset('999999'); $this->fail('Expected wrong code'); } catch(\DomainException $e) {} }
        $this->expectException(\DomainException::class); $this->reset();
    }

    public function testFifthAttemptMaySucceedButExpiredCodeNeverDoes(): void
    {
        $this->request();
        for($i=0;$i<4;$i++) { try { $this->reset('999999'); } catch(\DomainException $e) {} }
        $this->reset();
        $this->assertSame(1,(int)$this->connection->table('customer_password_reset_codes')->get()->getRowArray()['used']);
        $this->service->clock+=61; $this->request(); $this->service->clock+=600;
        $this->expectException(\DomainException::class); $this->reset();
    }

    public function testResendCooldownAndNewCodeInvalidatePreviousCode(): void
    {
        $this->request(); $this->service->code='543210'; $this->request();
        $this->assertCount(1,$this->mailer->codes);
        $this->service->clock+=61; $this->request();
        $this->assertCount(2,$this->mailer->codes);
        try { $this->reset('012345'); $this->fail('Old code accepted'); } catch(\DomainException $e) {}
        $this->reset('543210'); $this->assertCount(1,$this->mailer->changed);
    }

    public function testLimitsApplyAcrossDifferentIpsAndUnknownAccountsGetNoEmail(): void
    {
        for($i=0;$i<4;$i++) { $this->request('buyer@example.test','192.0.2.'.($i+1)); $this->service->clock+=61; }
        $this->assertCount(3,$this->mailer->codes);
        $this->request('unknown@example.test'); $this->request('admin@example.test');
        $this->assertCount(3,$this->mailer->codes);
        $this->assertSame('old-admin-hash',$this->connection->table('user')->where('email','admin@example.test')->get()->getRowArray()['sandi']);
    }

    public function testDeliveryFailureLeavesNoUsableCode(): void
    {
        $this->mailer->delivered=false; $this->request();
        $this->expectException(\DomainException::class); $this->reset();
    }

    public function testInactiveCustomerIsNotActivatedByReset(): void
    {
        $this->connection->table('user')->where('email','buyer@example.test')->update(['active'=>'0']);
        $this->request(); $this->reset();
        $this->assertSame('0',$this->connection->table('user')->where('email','buyer@example.test')->get()->getRowArray()['active']);
    }

    /** @dataProvider invalidCodes */
    public function testCodeMustContainExactlySixAsciiDigits($code): void
    {
        $this->request(); $this->expectException(\DomainException::class); $this->reset($code);
    }
    public static function invalidCodes(): array { return [[null],[[]],['12345'],['1234567'],['12ab56'],['１２３４５６']]; }

    /** @dataProvider invalidPasswords */
    public function testPasswordAndConfirmationRules($password,$confirmation): void
    {
        $this->assertNotNull(PasswordResetService::passwordError($password,$confirmation));
    }
    public static function invalidPasswords(): array { return [[null,null],[[],[]],['short1','short1'],['NoDigitsHere','NoDigitsHere'],[str_repeat('a',72).'1',str_repeat('a',72).'1'],['ValidPass123','different']]; }

    public function testFailureDuringSessionRevocationRollsBackPasswordAndCodeConsumption(): void
    {
        $this->request(); $before=$this->connection->table('user')->where('email','buyer@example.test')->get()->getRowArray()['sandi'];
        $this->connection->query("CREATE TRIGGER fail_epoch BEFORE INSERT ON customer_password_sessions BEGIN SELECT RAISE(ABORT,'fixture'); END");
        try { $this->reset(); $this->fail('Expected write failure'); } catch(\RuntimeException $e) {
            $this->assertSame($before,$this->connection->table('user')->where('email','buyer@example.test')->get()->getRowArray()['sandi']);
            $this->assertSame(0,(int)$this->connection->table('customer_password_reset_codes')->get()->getRowArray()['used']);
            $this->assertSame([],$this->mailer->changed);
        }
    }
}
