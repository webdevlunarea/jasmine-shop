<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateCustomerMarketingPreferencesAndEmailQueue extends Migration
{
    public function up()
    {
        foreach (['customer_marketing_preferences', 'customer_voucher_email_campaigns', 'customer_voucher_email_recipients', 'customer_marketing_campaign_guard'] as $table) {
            if ($this->db->tableExists($table)) {
                throw new RuntimeException('Tabel ' . $table . ' sudah tersedia. Periksa schema sebelum melanjutkan.');
            }
        }

        $this->forge->addField([
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'email_promotions' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'push_promotions' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'email_consent_at' => ['type' => 'DATETIME', 'null' => true],
            'push_consent_at' => ['type' => 'DATETIME', 'null' => true],
            'email_unsubscribe_token_hash' => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
            'email_unsubscribe_token_encrypted' => ['type' => 'TEXT', 'null' => true],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('email_user', true);
        $this->forge->addKey(['email_promotions', 'updated_at']);
        $this->forge->addKey(['push_promotions', 'updated_at']);
        $this->forge->createTable('customer_marketing_preferences');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'voucher_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 255],
            'body_html' => ['type' => 'MEDIUMTEXT'],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'queued'],
            'created_by' => ['type' => 'VARCHAR', 'constraint' => 254],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('voucher_id');
        $this->forge->addKey(['status', 'created_at']);
        $this->forge->createTable('customer_voucher_email_campaigns');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'campaign_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'email_user' => ['type' => 'VARCHAR', 'constraint' => 254],
            'email_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'pending'],
            'attempts' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'default' => 0],
            'next_attempt_at' => ['type' => 'DATETIME'],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'last_error' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['campaign_id', 'email_hash']);
        $this->forge->addKey(['status', 'next_attempt_at']);
        $this->forge->addKey(['email_hash', 'sent_at']);
        $this->forge->createTable('customer_voucher_email_recipients');

        $this->forge->addField([
            'id' => ['type' => 'TINYINT', 'unsigned' => true],
            'last_campaign_at' => ['type' => 'DATETIME', 'null' => true],
            'campaign_date_utc' => ['type' => 'DATE', 'null' => true],
            'campaign_count' => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('customer_marketing_campaign_guard');
        $this->db->table('customer_marketing_campaign_guard')->insert([
            'id' => 1, 'last_campaign_at' => null, 'campaign_date_utc' => null, 'campaign_count' => 0,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        foreach (['customer_voucher_email_recipients', 'customer_voucher_email_campaigns', 'customer_marketing_preferences'] as $table) {
            if ($this->db->tableExists($table) && $this->db->table($table)->countAllResults() > 0) {
                throw new RuntimeException('Rollback dihentikan: ' . $table . ' berisi preferensi atau antrean promosi.');
            }
        }
        $this->forge->dropTable('customer_voucher_email_recipients', true);
        $this->forge->dropTable('customer_voucher_email_campaigns', true);
        $this->forge->dropTable('customer_marketing_preferences', true);
        $this->forge->dropTable('customer_marketing_campaign_guard', true);
    }
}
