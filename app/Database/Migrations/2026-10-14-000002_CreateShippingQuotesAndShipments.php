<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateShippingQuotesAndShipments extends Migration
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
            'token' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'email_user' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
            ],
            'shipping_method_code' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'provider' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'courier_code' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'courier_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'service_code' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'service_name' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
                'null' => true,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'cost' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'etd' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'origin_id' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'destination_id' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'provinsi_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'kabupaten_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'kecamatan_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'kelurahan_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'kodepos' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'weight_grams' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'cart_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'address_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'raw_response' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'used_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addUniqueKey('token');
        $this->forge->addKey(['email_user', 'expires_at']);
        $this->forge->addKey(['cart_hash', 'address_hash']);
        $this->forge->createTable('shipping_quotes', true);

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_midtrans' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'shipping_quote_token' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'shipping_method_code' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'provider' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'courier_code' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'courier_name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'service_code' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'service_name' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
                'null' => true,
            ],
            'shipping_cost' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'etd' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'tracking_number' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'shipping_status' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'default' => 'Menunggu pengiriman',
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
        $this->forge->addKey('shipping_quote_token');
        $this->forge->createTable('order_shipments', true);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        foreach (['order_shipments', 'shipping_quotes'] as $table) {
            if ($db->tableExists($table) && $db->table($table)->countAllResults() > 0) {
                throw new RuntimeException('Rollback dibatalkan: ' . $table . ' sudah berisi data.');
            }
        }

        $this->forge->dropTable('order_shipments', true);
        $this->forge->dropTable('shipping_quotes', true);
    }
}
