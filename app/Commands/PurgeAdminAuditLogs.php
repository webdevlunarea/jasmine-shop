<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use DateTimeImmutable;
use DateTimeZone;

final class PurgeAdminAuditLogs extends BaseCommand
{
    protected $group = 'Security';
    protected $name = 'admin:purge-audit-logs';
    protected $description = 'Hapus catatan aktivitas admin yang berusia lebih dari enam bulan.';

    public function run(array $params)
    {
        $db = Database::connect();
        $cutoff = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('-6 months')->format('Y-m-d H:i:s');
        $purged = 0;
        foreach (['admin_activity_logs', 'admin_role_audit'] as $table) {
            if (!$db->tableExists($table)) continue;
            $db->table($table)->where('created_at <', $cutoff)->delete();
            $purged += $db->affectedRows();
        }
        CLI::write('Catatan audit admin lebih lama dari enam bulan dihapus: ' . $purged);
        return EXIT_SUCCESS;
    }
}
