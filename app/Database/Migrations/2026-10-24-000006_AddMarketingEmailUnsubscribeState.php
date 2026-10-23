<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMarketingEmailUnsubscribeState extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('customer_marketing_preferences')) {
            return;
        }

        if (!$this->db->fieldExists('email_unsubscribed_at', 'customer_marketing_preferences')) {
            $this->forge->addColumn('customer_marketing_preferences', [
                'email_unsubscribed_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'email_promotions'],
            ]);
        }

        // Preserve prior negative choices from the old opt-in UI as explicit unsubscribes.
        $this->db->table('customer_marketing_preferences')
            ->where('email_promotions', 0)
            ->where('email_unsubscribed_at', null)
            ->update(['email_unsubscribed_at' => gmdate('Y-m-d H:i:s')]);
    }

    public function down()
    {
        if ($this->db->tableExists('customer_marketing_preferences')
            && $this->db->fieldExists('email_unsubscribed_at', 'customer_marketing_preferences')) {
            $this->forge->dropColumn('customer_marketing_preferences', 'email_unsubscribed_at');
        }
    }
}
