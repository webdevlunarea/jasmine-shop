<?php

use App\Services\Notifications\VoucherEmailBroadcastQueue;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class VoucherEmailBroadcastQueueTest extends CIUnitTestCase
{
    private BaseConnection $testDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDb = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->testDb->query('CREATE TABLE voucher (id INTEGER PRIMARY KEY, nama TEXT, active INTEGER, isi_email TEXT, code TEXT)');
        $this->testDb->query('CREATE TABLE user (email TEXT PRIMARY KEY, role TEXT NOT NULL, active TEXT NOT NULL)');
        $this->testDb->query('CREATE TABLE customer_marketing_preferences (
            email_user TEXT PRIMARY KEY, email_promotions INTEGER NOT NULL DEFAULT 0, push_promotions INTEGER NOT NULL DEFAULT 0,
            email_consent_at TEXT NULL, push_consent_at TEXT NULL, email_unsubscribed_at TEXT NULL, updated_at TEXT NOT NULL
        )');
        $this->testDb->query('CREATE TABLE customer_voucher_email_campaigns (
            id INTEGER PRIMARY KEY AUTOINCREMENT, voucher_id INTEGER NOT NULL UNIQUE, subject TEXT NOT NULL,
            body_html TEXT NOT NULL, status TEXT NOT NULL, created_by TEXT NOT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL
        )');
        $this->testDb->query('CREATE TABLE customer_voucher_email_recipients (
            id INTEGER PRIMARY KEY AUTOINCREMENT, campaign_id INTEGER NOT NULL, email_user TEXT NOT NULL, email_hash TEXT NOT NULL,
            status TEXT NOT NULL, attempts INTEGER NOT NULL, next_attempt_at TEXT NOT NULL, sent_at TEXT NULL,
            last_error TEXT NULL, created_at TEXT NOT NULL, updated_at TEXT NOT NULL, UNIQUE(campaign_id,email_hash)
        )');
        $this->testDb->query('CREATE TABLE customer_marketing_campaign_guard (id INTEGER PRIMARY KEY, last_campaign_at TEXT NULL, campaign_date_utc TEXT NULL, campaign_count INTEGER NOT NULL DEFAULT 0, updated_at TEXT NOT NULL)');
        $this->testDb->table('customer_marketing_campaign_guard')->insert(['id' => 1, 'last_campaign_at' => null, 'campaign_date_utc' => null, 'campaign_count' => 0, 'updated_at' => gmdate('Y-m-d H:i:s')]);
        $this->testDb->table('voucher')->insert([
            'id' => 7, 'nama' => 'Voucher Uji', 'active' => 1, 'isi_email' => '<tr><td>Promo</td></tr>',
            'code' => json_encode([['email_user' => 'buyer@example.test'], ['email_user' => 'legacy@example.test']]),
        ]);
        $this->testDb->table('user')->insert(['email' => 'buyer@example.test', 'role' => '0', 'active' => '1']);
        $this->testDb->table('user')->insert(['email' => 'legacy@example.test', 'role' => '0', 'active' => '1']);
        $this->testDb->table('customer_marketing_preferences')->insert([
            'email_user' => 'buyer@example.test', 'email_promotions' => 0, 'push_promotions' => 0,
            'email_consent_at' => null, 'push_consent_at' => null,
            'email_unsubscribed_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testQueueIncludesLegacyAccountsButExcludesExplicitUnsubscribesAndPreventsDuplicates(): void
    {
        $queue = new VoucherEmailBroadcastQueue($this->testDb);
        $this->assertSame(1, $queue->queue(7, 'admin@example.test', 'voucher_recipients', 100));
        $recipient = $this->testDb->table('customer_voucher_email_recipients')->get()->getRowArray();
        $this->assertSame('legacy@example.test', $recipient['email_user']);
        $campaign = $this->testDb->table('customer_voucher_email_campaigns')->get()->getRowArray();
        $this->assertSame('queued', $campaign['status']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('VOUCHER_CAMPAIGN_ALREADY_QUEUED');
        $queue->queue(7, 'admin@example.test', 'voucher_recipients', 100);
    }

    public function testQueueCanTargetActiveCustomersAndRespectAdminRecipientLimit(): void
    {
        $this->testDb->table('user')->insert(['email' => 'newbuyer@example.test', 'role' => '0', 'active' => '1']);
        $this->testDb->table('user')->insert(['email' => 'inactive@example.test', 'role' => '0', 'active' => '0']);
        $this->testDb->table('user')->insert(['email' => 'staff@example.test', 'role' => '1', 'active' => '1']);

        $queue = new VoucherEmailBroadcastQueue($this->testDb);
        $this->assertSame(1, $queue->queue(7, 'admin@example.test', 'active_customers', 1));
        $recipient = $this->testDb->table('customer_voucher_email_recipients')->get()->getRowArray();
        $this->assertSame('legacy@example.test', $recipient['email_user']);
    }
}
