<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShippingPricingSnapshot extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('order_shipments')) throw new \RuntimeException('Shipping foundation harus tersedia terlebih dahulu.');
        if ($this->db->fieldExists('shipping_pricing', 'order_shipments')) throw new \RuntimeException('Kolom shipping_pricing sudah ada. Periksa schema dan riwayat migration.');
        $this->forge->addColumn('order_shipments', ['shipping_pricing' => ['type' => 'TEXT', 'null' => true]]);
        $this->db->resetDataCache();
    }

    public function down()
    {
        if ($this->db->fieldExists('shipping_pricing', 'order_shipments')) {
            if ($this->db->table('order_shipments')->where('shipping_pricing IS NOT NULL', null, false)->countAllResults() > 0) {
                throw new \RuntimeException('Rollback dihentikan karena snapshot ongkir sudah digunakan. Backup dan review terpisah diperlukan.');
            }
            $this->forge->dropColumn('order_shipments', 'shipping_pricing');
            $this->db->resetDataCache();
        }
    }
}
