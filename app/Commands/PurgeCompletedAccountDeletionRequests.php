<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class PurgeCompletedAccountDeletionRequests extends BaseCommand
{
    protected $group = 'Privacy';
    protected $name = 'privacy:purge-deletion-requests';
    protected $description = 'Hapus catatan permintaan penghapusan akun yang selesai lebih dari 180 hari lalu.';

    public function run(array $params)
    {
        $db = Database::connect();
        if (!$db->tableExists('customer_account_deletion_requests')) {
            CLI::write('Tabel permintaan penghapusan belum tersedia. Tidak ada perubahan.', 'yellow');
            return EXIT_ERROR;
        }
        $cutoff = date('Y-m-d H:i:s', time() - 180 * 86400);
        $db->table('customer_account_deletion_requests')->where('status', 'completed')
            ->where('processed_at <', $cutoff)->delete();
        CLI::write('Catatan penghapusan lama dihapus: ' . $db->affectedRows());
        return EXIT_SUCCESS;
    }
}
