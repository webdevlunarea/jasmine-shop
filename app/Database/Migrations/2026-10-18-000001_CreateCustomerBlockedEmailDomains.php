<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerBlockedEmailDomains extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'domain' => ['type' => 'VARCHAR', 'constraint' => 190],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('domain');
        $attrs = $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci'] : [];
        $this->forge->createTable('customer_blocked_email_domains', false, $attrs);
    }

    public function down()
    {
        if ($this->db->tableExists('customer_blocked_email_domains') && $this->db->table('customer_blocked_email_domains')->countAllResults() > 0) {
            throw new \RuntimeException('Ekspor daftar domain terblokir sebelum rollback.');
        }
        if ($this->db->tableExists('customer_blocked_email_domains')) $this->forge->dropTable('customer_blocked_email_domains');
    }
}
