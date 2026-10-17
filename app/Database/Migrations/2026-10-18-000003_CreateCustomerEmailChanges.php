<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerEmailChanges extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('customer_email_changes')) {
            throw new \RuntimeException('Tabel customer_email_changes sudah tersedia. Periksa schema sebelum melanjutkan.');
        }

        $this->forge->addField([
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'pending_email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => true],
            'otp_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'request_id' => ['type' => 'CHAR', 'constraint' => 36, 'null' => true],
            'expires_at' => ['type' => 'DATETIME', 'null' => true],
            'requested_at' => ['type' => 'DATETIME', 'null' => true],
            'attempts' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'changed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('email_user', true);
        $this->forge->addUniqueKey('pending_email');
        $this->forge->createTable('customer_email_changes');
    }

    public function down()
    {
        if (!$this->db->tableExists('customer_email_changes')) return;
        if ($this->db->table('customer_email_changes')->countAllResults() > 0) {
            throw new \RuntimeException('Rollback dihentikan: customer_email_changes berisi data. Arsipkan/backup dan setujui pembersihan secara terpisah.');
        }
        $this->forge->dropTable('customer_email_changes');
    }
}
