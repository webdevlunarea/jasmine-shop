<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuspensionDurationAndMessage extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('customer_account_suspensions')) {
            if (!$this->db->fieldExists('user_message', 'customer_account_suspensions')) {
                $this->forge->addColumn('customer_account_suspensions', [
                    'user_message' => ['type' => 'VARCHAR', 'constraint' => 250, 'default' => 'Akun Anda sedang dibatasi. Hubungi Lunarea melalui halaman bantuan.'],
                ]);
            }
            if (!$this->db->fieldExists('expires_at', 'customer_account_suspensions')) {
                $this->forge->addColumn('customer_account_suspensions', [
                    'expires_at' => ['type' => 'DATETIME', 'null' => true],
                ]);
            }
        }
        if ($this->db->tableExists('customer_account_suspension_events')) {
            if (!$this->db->fieldExists('user_message', 'customer_account_suspension_events')) {
                $this->forge->addColumn('customer_account_suspension_events', [
                    'user_message' => ['type' => 'VARCHAR', 'constraint' => 250, 'null' => true],
                ]);
            }
            if (!$this->db->fieldExists('expires_at', 'customer_account_suspension_events')) {
                $this->forge->addColumn('customer_account_suspension_events', [
                    'expires_at' => ['type' => 'DATETIME', 'null' => true],
                ]);
            }
        }
    }

    public function down()
    {
        foreach (['customer_account_suspension_events', 'customer_account_suspensions'] as $table) {
            if (!$this->db->tableExists($table)) continue;
            foreach (['expires_at', 'user_message'] as $field) {
                if ($this->db->fieldExists($field, $table)) $this->forge->dropColumn($table, $field);
            }
        }
    }
}
