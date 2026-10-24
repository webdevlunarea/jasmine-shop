<?php

use App\Services\Vouchers\VoucherMutationLock;
use CodeIgniter\Test\CIUnitTestCase;

final class VoucherMutationLockTest extends CIUnitTestCase
{
    public function testCommitsSuccessfulMutation(): void
    {
        $db = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE test_voucher_changes (id INTEGER PRIMARY KEY, value TEXT)');
        $lock = new VoucherMutationLock($db);

        $result = $lock->run('test', function () use ($db): string {
            $db->table('test_voucher_changes')->insert(['id' => 1, 'value' => 'committed']);
            return 'ok';
        });

        $this->assertSame('ok', $result);
        $this->assertSame('committed', $db->table('test_voucher_changes')->where('id', 1)->get()->getRowArray()['value']);
        $db->close();
    }

    public function testRollsBackMutationWhenOperationThrows(): void
    {
        $db = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $db->query('CREATE TABLE test_voucher_changes (id INTEGER PRIMARY KEY, value TEXT)');
        $lock = new VoucherMutationLock($db);

        try {
            $lock->run('test', function () use ($db): void {
                $db->table('test_voucher_changes')->insert(['id' => 1, 'value' => 'rolled back']);
                throw new RuntimeException('simulated failure');
            });
            $this->fail('Expected simulated failure');
        } catch (RuntimeException $error) {
            $this->assertSame('simulated failure', $error->getMessage());
        }

        $this->assertSame(0, $db->table('test_voucher_changes')->countAllResults());
        $db->close();
    }
}
