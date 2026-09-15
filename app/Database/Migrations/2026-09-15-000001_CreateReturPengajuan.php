<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReturPengajuan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_midtrans' => [
                'type' => 'VARCHAR',
                'constraint' => 60,
            ],
            'email_cus' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],
            'nama_cus' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'hp_cus' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
            ],
            'items' => [
                'type' => 'TEXT',
            ],
            'alasan' => [
                'type' => 'TEXT',
            ],
            'solusi' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'review_admin',
            ],
            'bukti' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Menunggu Review Admin',
            ],
            'luna_response' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('id_midtrans');
        $this->forge->addKey('email_cus');
        $this->forge->createTable('retur_pengajuan', true);
    }

    public function down()
    {
        $this->forge->dropTable('retur_pengajuan', true);
    }
}
