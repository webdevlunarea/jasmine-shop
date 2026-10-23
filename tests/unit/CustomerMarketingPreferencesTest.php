<?php

use App\Services\Notifications\CustomerMarketingPreferences;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class CustomerMarketingPreferencesTest extends CIUnitTestCase
{
    private BaseConnection $testDb;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testDb = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->testDb->query('CREATE TABLE customer_marketing_preferences (
            email_user TEXT PRIMARY KEY, email_promotions INTEGER NOT NULL DEFAULT 0, push_promotions INTEGER NOT NULL DEFAULT 0,
            email_consent_at TEXT NULL, push_consent_at TEXT NULL, email_unsubscribe_token_hash TEXT NULL,
            email_unsubscribe_token_encrypted TEXT NULL, updated_at TEXT NOT NULL
        )');
    }

    protected function tearDown(): void
    {
        $this->testDb->close();
        parent::tearDown();
    }

    public function testExistingAccountDefaultsToOptedOutAndPreferenceCanBeRevoked(): void
    {
        $prefs = new CustomerMarketingPreferences($this->testDb);
        $this->assertSame(['email_promotions' => false, 'push_promotions' => false], $prefs->get('legacy@example.test'));

        $this->assertSame(['email_promotions' => true, 'push_promotions' => true], $prefs->update('buyer@example.test', true, true));
        $row = $this->testDb->table('customer_marketing_preferences')->where('email_user', 'buyer@example.test')->get()->getRowArray();
        $this->assertSame(1, (int)$row['email_promotions']);
        $this->assertSame(1, (int)$row['push_promotions']);
        $this->assertNotEmpty($row['email_consent_at']);

        $prefs->update('buyer@example.test', false, false);
        $revoked = $this->testDb->table('customer_marketing_preferences')->where('email_user', 'buyer@example.test')->get()->getRowArray();
        $this->assertSame(0, (int)$revoked['email_promotions']);
        $this->assertSame(0, (int)$revoked['push_promotions']);
        $this->assertNull($revoked['email_consent_at']);
        $this->assertNull($revoked['email_unsubscribe_token_hash']);
    }
}
