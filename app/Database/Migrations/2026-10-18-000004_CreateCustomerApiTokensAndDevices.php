<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateCustomerApiTokensAndDevices extends Migration
{
    public function up()
    {
        foreach (['customer_api_tokens', 'customer_push_devices', 'customer_push_notifications', 'customer_checkout_requests'] as $table) {
            if ($this->db->tableExists($table)) {
                throw new RuntimeException('Tabel ' . $table . ' sudah tersedia. Periksa schema sebelum melanjutkan.');
            }
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'family_id' => ['type' => 'CHAR', 'constraint' => 36],
            'token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'token_type' => ['type' => 'VARCHAR', 'constraint' => 12],
            'password_version' => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => ''],
            'device_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'expires_at' => ['type' => 'DATETIME'],
            'revoked_at' => ['type' => 'DATETIME', 'null' => true],
            'replaced_by_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'last_used_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash');
        $this->forge->addKey(['email_user', 'revoked_at']);
        $this->forge->addKey(['family_id', 'token_type']);
        $this->forge->addKey('expires_at');
        $this->forge->createTable('customer_api_tokens');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'device_token_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'device_token' => ['type' => 'TEXT'],
            'platform' => ['type' => 'VARCHAR', 'constraint' => 20],
            'device_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_seen_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('device_token_hash');
        $this->forge->addKey(['email_user', 'is_active']);
        $this->forge->createTable('customer_push_devices');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'notification_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'title' => ['type' => 'VARCHAR', 'constraint' => 100],
            'body' => ['type' => 'VARCHAR', 'constraint' => 240],
            'payload' => ['type' => 'TEXT', 'null' => true],
            'dedupe_key' => ['type' => 'VARCHAR', 'constraint' => 190],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'pending'],
            'attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'next_attempt_at' => ['type' => 'DATETIME'],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'last_error' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('dedupe_key');
        $this->forge->addKey(['status', 'next_attempt_at']);
        $this->forge->addKey(['email_user', 'created_at']);
        $this->forge->createTable('customer_push_notifications');

        // Isolated idempotency ledger: leave legacy `pemesanan` untouched.
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'idempotency_key_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'request_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'order_id' => ['type' => 'VARCHAR', 'constraint' => 100],
            'state' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'processing'],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['email_user', 'idempotency_key_hash']);
        $this->forge->addUniqueKey('order_id');
        $this->forge->addKey(['state', 'updated_at']);
        $this->forge->createTable('customer_checkout_requests');
    }

    public function down()
    {
        foreach (['customer_checkout_requests', 'customer_push_notifications', 'customer_push_devices', 'customer_api_tokens'] as $table) {
            if ($this->db->tableExists($table) && $this->db->table($table)->countAllResults() > 0) {
                throw new RuntimeException('Rollback dihentikan: ' . $table . ' berisi sesi atau perangkat terdaftar. Backup dan persetujuan eksplisit diperlukan.');
            }
        }
        $this->forge->dropTable('customer_checkout_requests', true);
        $this->forge->dropTable('customer_push_notifications', true);
        $this->forge->dropTable('customer_push_devices', true);
        $this->forge->dropTable('customer_api_tokens', true);
    }
}
