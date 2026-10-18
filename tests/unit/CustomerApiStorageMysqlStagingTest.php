<?php

use App\Database\Migrations\CreateCustomerApiTokensAndDevices;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;

require_once APPPATH . 'Database/Migrations/2026-10-18-000004_CreateCustomerApiTokensAndDevices.php';

/** Opt-in DDL verification against the isolated local MySQL 8 staging clone only. */
final class CustomerApiStorageMysqlStagingTest extends CIUnitTestCase
{
    private ?BaseConnection $auditDb = null;
    private array $apiTables = [
        'customer_api_tokens', 'customer_push_devices',
        'customer_push_notifications', 'customer_checkout_requests',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $path = getenv('LUNA_IDENTITY_STAGING_CONNECTION');
        if (!$path) $this->markTestSkipped('Opt-in isolated MySQL staging only.');

        $config = json_decode((string)file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (($config['host'] ?? null) !== '127.0.0.1'
            || ($config['website'] ?? null) !== 'shipping_audit_web'
            || ($config['user'] ?? null) !== 'shipping_audit'
            || !is_int($config['port'] ?? null)
            || $config['port'] < 1024 || $config['port'] === 3306
            || empty($config['password'])) {
            throw new RuntimeException('Non-isolated database rejected.');
        }

        $this->auditDb = \Config\Database::connect([
            'DSN' => '', 'hostname' => $config['host'], 'port' => $config['port'],
            'username' => $config['user'], 'password' => $config['password'],
            'database' => $config['website'], 'DBDriver' => 'MySQLi', 'DBPrefix' => '',
            'pConnect' => false, 'DBDebug' => true, 'charset' => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci', 'strictOn' => true,
            'failover' => [], 'encrypt' => false,
        ], false);

        $identity = $this->auditDb->query('SELECT DATABASE() AS db, @@hostname AS host, @@port AS port, VERSION() AS version')->getRowArray();
        if (($identity['db'] ?? null) !== 'shipping_audit_web'
            || (int)($identity['port'] ?? 0) !== $config['port']
            || !str_starts_with((string)($identity['version'] ?? ''), '8.')) {
            throw new RuntimeException('Staging database identity mismatch.');
        }

        foreach ($this->apiTables as $table) {
            if ($this->auditDb->tableExists($table)) {
                $this->markTestSkipped('API storage already exists; refusing to mutate existing staging data.');
            }
        }
    }

    protected function tearDown(): void
    {
        $this->auditDb?->close();
        parent::tearDown();
    }

    public function testApiStorageMigrationCreatesAndRollsBackOnlyItsFourTablesOnMysql8(): void
    {
        $db = $this->auditDb;
        $before = $db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME")
            ->getResultArray();
        $migration = new CreateCustomerApiTokensAndDevices(\Config\Database::forge($db));

        $migration->up();
        foreach ($this->apiTables as $table) {
            $this->assertTrue($db->tableExists($table), $table);
            $metadata = $db->query(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$table]
            )->getRowArray();
            $this->assertSame('InnoDB', $metadata['ENGINE'] ?? null, $table . ' must be transactional');
        }

        $migration->down();
        foreach ($this->apiTables as $table) $this->assertFalse($db->tableExists($table), $table);
        $after = $db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME")
            ->getResultArray();
        $this->assertSame($before, $after, 'Existing staging tables must remain unchanged.');
    }
}
