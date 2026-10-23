<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerAccountSuspensions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500],
            'user_message' => ['type' => 'VARCHAR', 'constraint' => 250],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
            'suspended_by' => ['type' => 'VARCHAR', 'constraint' => 254],
            'suspended_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('email_user', true);
        $this->forge->addKey('suspended_at');
        $this->forge->addKey('expires_at');
        $this->forge->createTable('customer_account_suspensions', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci']);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'action' => ['type' => 'VARCHAR', 'constraint' => 20],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'user_message' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
            'admin_email' => ['type' => 'VARCHAR', 'constraint' => 254],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['email_user', 'created_at']);
        $this->forge->addKey(['action', 'created_at']);
        $this->forge->createTable('customer_account_suspension_events', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('customer_account_suspension_events', true);
        $this->forge->dropTable('customer_account_suspensions', true);
    }
}
