<?php

namespace App\Commands;

use App\Services\Auth\FacebookSchemaAudit;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class FacebookCheck extends BaseCommand
{
    protected $group = 'Auth';
    protected $name = 'facebook:check';
    protected $description = 'Periksa kesiapan konfigurasi dan metadata database Facebook tanpa mencetak rahasia atau mengubah data.';

    public function run(array $params)
    {
        $config = config(\Config\Facebook::class);
        CLI::write('Facebook config: ' . ($config->ready() ? 'READY' : 'NOT_READY'));
        CLI::write('Pemeriksaan hanya membaca metadata; tidak memanggil Meta atau mengubah database.');
        try {
            $findings = (new FacebookSchemaAudit())->findings(db_connect());
        } catch (\Throwable $e) {
            $findings = ['database_metadata_unavailable'];
        }
        foreach ($findings as $finding) CLI::write('FAIL ' . $finding);
        if (!$findings) CLI::write('Database schema: READY');
        return $config->ready() && !$findings ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
