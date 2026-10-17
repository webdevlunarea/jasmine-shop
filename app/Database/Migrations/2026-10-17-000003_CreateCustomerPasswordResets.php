<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerPasswordResets extends Migration
{
    public function up()
    {
        $attrs = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci'] : [];
        $this->forge->addField([
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'request_id' => ['type' => 'CHAR', 'constraint' => 64],
            'otp_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'INT', 'unsigned' => true],
            'expires_at' => ['type' => 'INT', 'unsigned' => true],
            'attempts' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'used' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addKey('email_user', true);
        $this->forge->createTable('customer_password_reset_codes', false, $attrs);
        $this->forge->addField([
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'version' => ['type' => 'CHAR', 'constraint' => 64],
        ]);
        $this->forge->addKey('email_user', true);
        $this->forge->createTable('customer_password_sessions', false, $attrs);
        $this->forge->addField([
            'bucket' => ['type' => 'CHAR', 'constraint' => 64],
            'window_start' => ['type' => 'INT', 'unsigned' => true],
            'hits' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
        ]);
        $this->forge->addKey('bucket', true);
        $this->forge->createTable('customer_password_reset_limits', false, $attrs);
    }
    public function down()
    {
        foreach (['customer_password_reset_codes', 'customer_password_sessions', 'customer_password_reset_limits'] as $table) {
            if ($this->db->table($table)->countAllResults() > 0) throw new \RuntimeException('Backup data reset dan pencabutan sesi diperlukan sebelum rollback.');
        }
        foreach (['customer_password_reset_codes', 'customer_password_sessions', 'customer_password_reset_limits'] as $table) $this->forge->dropTable($table);
    }
}
