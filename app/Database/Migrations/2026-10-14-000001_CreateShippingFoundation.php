<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateShippingFoundation extends Migration
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
            'code' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'provider' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'settings' => [
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
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('is_active');
        $this->forge->createTable('shipping_methods', true);

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'shipping_method_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'provinsi_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'kabupaten_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'kecamatan_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'updated_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->forge->addUniqueKey(['shipping_method_id', 'kecamatan_id']);
        $this->forge->addKey(['kecamatan_id', 'is_active']);
        $this->forge->addKey(['provinsi_id', 'kabupaten_id', 'kecamatan_id']);
        $this->forge->createTable('shipping_internal_coverages', true);

        $db = \Config\Database::connect();
        if ($db->tableExists('shipping_methods')) {
            $exists = $db->table('shipping_methods')
                ->where('code', 'internal_delivery')
                ->countAllResults();

            if ((int) $exists === 0) {
                $db->table('shipping_methods')->insert([
                    'code' => 'internal_delivery',
                    'name' => 'Jasa Kirim Toko',
                    'type' => 'internal_delivery',
                    'provider' => 'internal',
                    'is_active' => 0,
                    'sort_order' => 10,
                    'settings' => json_encode([
                        'fixed_cost' => 0,
                        'label' => 'Gratis',
                    ]),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $rajaOngkirExists = $db->table('shipping_methods')
                ->where('code', 'rajaongkir_domestic')
                ->countAllResults();

            if ((int) $rajaOngkirExists === 0) {
                $db->table('shipping_methods')->insert([
                    'code' => 'rajaongkir_domestic',
                    'name' => 'Ekspedisi',
                    'type' => 'external_rate',
                    'provider' => 'rajaongkir',
                    'is_active' => 0,
                    'sort_order' => 20,
                    'settings' => json_encode([
                        'origin_id' => '',
                        'origin_label' => 'Kota Semarang, Jawa Tengah',
                        'couriers' => ['jne', 'jnt', 'sicepat', 'tiki', 'pos', 'sentral'],
                        'quote_only' => true,
                    ]),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('shipping_internal_coverages')) {
            $coverageCount = $db->table('shipping_internal_coverages')->countAllResults();
            if ($coverageCount > 0) {
                throw new RuntimeException('Rollback dibatalkan: shipping_internal_coverages sudah berisi data.');
            }
        }

        if ($db->tableExists('shipping_methods')) {
            $methodCount = $db->table('shipping_methods')->countAllResults();
            if ($methodCount > 0) {
                throw new RuntimeException('Rollback dibatalkan: shipping_methods sudah berisi data.');
            }
        }

        $this->forge->dropTable('shipping_internal_coverages', true);
        $this->forge->dropTable('shipping_methods', true);
    }
}
