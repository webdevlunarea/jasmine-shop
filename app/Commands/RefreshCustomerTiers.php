<?php

namespace App\Commands;

use App\Services\Customers\CustomerTierMaintenance;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class RefreshCustomerTiers extends BaseCommand
{
    protected $group = 'Customers';
    protected $name = 'customers:tiers-refresh';
    protected $description = 'Hitung ulang tier dan singkirkan transaksi tier yang sudah kedaluwarsa dengan proses bertahap.';
    protected $options = ['--batch-size' => 'Jumlah akun per batch (1–500, default 100).'];

    public function run(array $params)
    {
        $batchSize = min(500, max(1, (int)(CLI::getOption('batch-size') ?: 100)));
        $cursor = '';
        $read = 0;
        $updated = 0;
        $skipped = 0;

        try {
            $service = new CustomerTierMaintenance();
            do {
                $batch = $service->refreshBatch($cursor, $batchSize);
                $read += $batch['read'];
                $updated += $batch['updated'];
                $skipped += $batch['skipped'];
                if ($batch['read'] === 0 || $batch['last_email'] === $cursor) break;
                $cursor = $batch['last_email'];
            } while ($batch['read'] === $batchSize);

            CLI::write("Akun diperiksa: {$read}; tier diperbarui: {$updated}; data dilewati: {$skipped}.");
            if ($skipped > 0) CLI::write('Sebagian data tier tidak valid dan tidak diubah; periksa log/data akun sebelum koreksi.', 'yellow');
            return EXIT_SUCCESS;
        } catch (\Throwable $error) {
            log_message('error', 'Customer tier refresh failed: ' . get_class($error));
            CLI::error('Pembaruan tier berhenti karena terjadi kegagalan. Data akun yang sudah diproses tetap tersimpan.');
            return EXIT_ERROR;
        }
    }
}
