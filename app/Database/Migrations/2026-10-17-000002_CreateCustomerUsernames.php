<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerUsernames extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'username' => ['type' => 'VARCHAR', 'constraint' => 30],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
        ]);
        $this->forge->addKey('username', true);
        $this->forge->addUniqueKey('email_user');
        $attributes = $this->db->DBDriver === 'MySQLi'
            ? ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci'] : [];
        $this->forge->createTable('customer_usernames', false, $attributes);
    }

    public function down()
    {
        if ($this->db->table('customer_usernames')->countAllResults() > 0) {
            throw new \RuntimeException('Backup username pelanggan diperlukan sebelum rollback.');
        }
        $this->forge->dropTable('customer_usernames');
    }
}
