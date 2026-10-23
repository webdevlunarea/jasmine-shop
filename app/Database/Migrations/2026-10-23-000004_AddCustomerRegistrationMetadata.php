<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerRegistrationMetadata extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('registration_method', 'user')) {
            $this->forge->addColumn('user', [
                'registration_method' => ['type' => 'VARCHAR', 'constraint' => 16, 'null' => true],
            ]);
        }
        if (!$this->db->fieldExists('registered_at', 'user')) {
            $this->forge->addColumn('user', [
                'registered_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
        }
        $indexes = $this->db->getIndexData('user');
        $hasIndex = false;
        foreach ($indexes as $index) if (($index->name ?? '') === 'idx_user_registration_method_date') $hasIndex = true;
        if (!$hasIndex) $this->db->query('CREATE INDEX idx_user_registration_method_date ON ' . $this->db->protectIdentifiers($this->db->prefixTable('user')) . ' (registration_method, registered_at)');
        $this->db->query("UPDATE " . $this->db->protectIdentifiers($this->db->prefixTable('user'))
            . " SET registration_method = auth_provider WHERE registration_method IS NULL AND auth_provider IN ('google', 'facebook')");
    }

    public function down()
    {
        $table = $this->db->protectIdentifiers($this->db->prefixTable('user'));
        foreach ($this->db->getIndexData('user') as $index) {
            if (($index->name ?? '') === 'idx_user_registration_method_date') {
                $this->db->query('DROP INDEX idx_user_registration_method_date ON ' . $table);
                break;
            }
        }
        foreach (['registered_at', 'registration_method'] as $field) {
            if ($this->db->fieldExists($field, 'user')) $this->forge->dropColumn('user', $field);
        }
    }
}
