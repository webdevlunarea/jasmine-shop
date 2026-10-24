<?php

namespace App\Commands;

use App\Services\Auth\DisposableEmailDomainList;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class SyncDisposableEmailDomains extends BaseCommand
{
    protected $group = 'Customers';
    protected $name = 'customers:sync-disposable-email-domains';
    protected $description = 'Perbarui daftar lokal domain email sementara untuk pemeriksaan pendaftaran.';

    public function run(array $params)
    {
        try {
            $count = (new DisposableEmailDomainList())->refresh();
            CLI::write('Daftar domain email sementara diperbarui: ' . $count . ' domain.');
            return EXIT_SUCCESS;
        } catch (Throwable $error) {
            log_message('error', 'Pembaruan daftar email sementara gagal: {type}', ['type' => get_class($error)]);
            CLI::error('Daftar domain tidak berubah. Periksa koneksi dan coba lagi nanti.');
            return EXIT_ERROR;
        }
    }
}
