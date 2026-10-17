<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFacebookIdentities extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'facebook_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
        ]);
        $this->forge->addKey('facebook_id', true);
        $this->forge->addUniqueKey('email_user');
        $this->forge->createTable('facebook_identities');
    }

    public function down()
    {
        if ($this->db->table('facebook_identities')->countAllResults() > 0) {
            throw new \RuntimeException('Backup pemetaan akun diperlukan sebelum rollback.');
        }
        $this->forge->dropTable('facebook_identities');
    }
}
