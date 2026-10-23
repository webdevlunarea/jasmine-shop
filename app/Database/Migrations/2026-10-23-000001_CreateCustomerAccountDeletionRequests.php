<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateCustomerAccountDeletionRequests extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('customer_account_deletion_requests')) {
            throw new RuntimeException('Tabel customer_account_deletion_requests sudah tersedia.');
        }
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'requested_at' => ['type' => 'DATETIME'],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'requested_at']);
        $this->forge->addKey(['email_user', 'status']);
        $this->forge->createTable('customer_account_deletion_requests', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('customer_account_deletion_requests', true);
    }
}
