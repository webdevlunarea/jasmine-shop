<?php

namespace App\Commands;

use App\Services\Auth\CustomerAccountSuspensions;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ExpireCustomerSuspensions extends BaseCommand
{
    protected $group = 'Customers';
    protected $name = 'customers:suspensions-expire';
    protected $description = 'Akhiri penangguhan pelanggan yang sudah melewati masa berlaku.';

    public function run(array $params)
    {
        try {
            $expired = (new CustomerAccountSuspensions())->expireDue(200);
            CLI::write("Penangguhan yang berakhir dan dicatat: {$expired}.");
            return EXIT_SUCCESS;
        } catch (\Throwable $e) {
            log_message('error', 'Customer suspension expiry failed: ' . get_class($e));
            CLI::error('Penangguhan kedaluwarsa belum dapat diproses.');
            return EXIT_ERROR;
        }
    }
}
