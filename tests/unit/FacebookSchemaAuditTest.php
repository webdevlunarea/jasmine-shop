<?php

use App\Services\Auth\FacebookSchemaAudit;
use CodeIgniter\Test\CIUnitTestCase;

final class FacebookSchemaAuditTest extends CIUnitTestCase
{
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    public function testMissingTablesAreReportedWithoutCreatingThem(): void
    {
        $before = $this->connection->listTables();
        $this->assertSame(['missing_table:facebook_identities', 'missing_table:user', 'missing_table:pembeli'], (new FacebookSchemaAudit())->findings($this->connection));
        $this->assertSame($before, $this->connection->listTables());
    }

    public function testReportsMissingAuthProviderWithoutReadingCustomerRows(): void
    {
        $this->connection->query('CREATE TABLE facebook_identities (facebook_id TEXT PRIMARY KEY, email_user TEXT UNIQUE)');
        $this->connection->query('CREATE TABLE user (email TEXT, sandi TEXT, role TEXT, active TEXT, otp TEXT, waktu_otp TEXT)');
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT, nama TEXT, nohp TEXT, alamat TEXT, wishlist TEXT, keranjang TEXT, transaksi TEXT, poin TEXT, tier TEXT, foto TEXT, tgl_lahir TEXT)');
        $this->connection->table('user')->insert(['email' => 'private@example.test', 'sandi' => 'private-fixture']);
        $findings = (new FacebookSchemaAudit())->findings($this->connection);
        $this->assertSame(['missing_column:user.auth_provider'], $findings);
        $this->assertStringNotContainsString('private', json_encode($findings));
        $this->assertSame(1, $this->connection->table('user')->countAllResults());
    }

    public function testCompleteSchemaPasses(): void
    {
        $this->connection->query('CREATE TABLE facebook_identities (facebook_id TEXT PRIMARY KEY, email_user TEXT UNIQUE)');
        $this->connection->query('CREATE TABLE user (email TEXT, sandi TEXT, role TEXT, active TEXT, otp TEXT, waktu_otp TEXT, auth_provider TEXT)');
        $this->connection->query('CREATE TABLE pembeli (email_user TEXT, nama TEXT, nohp TEXT, alamat TEXT, wishlist TEXT, keranjang TEXT, transaksi TEXT, poin TEXT, tier TEXT, foto TEXT, tgl_lahir TEXT)');
        $this->assertSame([], (new FacebookSchemaAudit())->findings($this->connection));
    }

    public function testFailureDiagnosticsOnlyExposeAllowlistedStageAndNumericCode(): void
    {
        $e = new \App\Services\Auth\FacebookAccountException('private-fixture', 1062);
        $this->assertSame('unknown', $e->stage());
        $this->assertSame(1062, $e->databaseCode());
        $this->assertNull($e->getPrevious());
        $this->assertStringNotContainsString('private-fixture', $e->getMessage());
    }
}
