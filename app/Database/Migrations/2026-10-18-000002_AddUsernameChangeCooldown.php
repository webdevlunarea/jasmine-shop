<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsernameChangeCooldown extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customer_usernames', [
            'changed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);

        // Existing usernames have no historical change timestamp. Start their
        // first cooldown from deployment; future changes are timestamped by the service.
        $this->db->table('customer_usernames')
            ->where('changed_at', null)
            ->update(['changed_at' => gmdate('Y-m-d H:i:s')]);
    }

    public function down()
    {
        $this->forge->dropColumn('customer_usernames', 'changed_at');
    }
}
