<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class PasswordResetCheck extends BaseCommand
{
    protected $group = 'Auth';
    protected $name = 'password-reset:check';
    protected $description = 'Periksa schema reset sandi dan kelengkapan konfigurasi email tanpa mengirim email atau mencetak rahasia.';

    public function run(array $params)
    {
        $config = config(\Config\Email::class);
        $sender = $config->fromEmail ?: $config->SMTPUser;
        $ready = (bool)filter_var($sender, FILTER_VALIDATE_EMAIL)
            && ($config->protocol !== 'smtp' || ($config->SMTPHost !== '' && $config->SMTPUser !== '' && $config->SMTPPass !== ''));
        CLI::write('Email configuration: ' . ($ready ? 'READY' : 'NOT_READY') . ' (pengiriman belum diuji)');
        $ok = true;
        try {
            $db = db_connect();
            foreach ([
                'user' => ['email', 'sandi', 'role'],
                'customer_password_reset_codes' => ['email_user', 'request_id', 'otp_hash', 'created_at', 'expires_at', 'attempts', 'used'],
                'customer_password_sessions' => ['email_user', 'version'],
                'customer_password_reset_limits' => ['bucket', 'window_start', 'hits'],
            ] as $table => $columns) {
                if (!$db->tableExists($table)) { CLI::write('FAIL missing_table:' . $table); $ok = false; continue; }
                foreach ($columns as $column) {
                    if (!$db->fieldExists($column, $table)) { CLI::write('FAIL missing_column:' . $table . '.' . $column); $ok = false; }
                }
                if ($db->DBDriver === 'MySQLi') {
                    $row = $db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$db->prefixTable($table)])->getRowArray();
                    if ($row && strcasecmp((string)$row['ENGINE'], 'InnoDB') !== 0) { CLI::write('FAIL nontransactional_table:' . $table); $ok = false; }
                }
            }
        } catch (\Throwable $e) { CLI::write('FAIL database_metadata_unavailable'); $ok = false; }
        if ($ok) CLI::write('Password reset schema: READY');
        return $ready && $ok ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
