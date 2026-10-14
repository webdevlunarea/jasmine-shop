<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerAddresses extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('customer_addresses')) {
            throw new \RuntimeException('Tabel customer_addresses sudah tersedia. Periksa schema sebelum melanjutkan.');
        }
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'label' => ['type' => 'VARCHAR', 'constraint' => 60],
            'recipient_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'recipient_phone' => ['type' => 'VARCHAR', 'constraint' => 25],
            'address_data' => ['type' => 'TEXT'],
            'is_default' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['email_user', 'is_default']);
        $this->forge->createTable('customer_addresses');
    }

    public function down()
    {
        if ($this->db->tableExists('customer_addresses')) {
            if ($this->db->table('customer_addresses')->countAllResults() > 0) {
                throw new \RuntimeException('Rollback dihentikan: buku alamat berisi data. Backup dan persetujuan penghapusan diperlukan.');
            }
            $this->forge->dropTable('customer_addresses');
        }
    }
}
