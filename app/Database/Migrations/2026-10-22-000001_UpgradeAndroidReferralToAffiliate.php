<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Upgrade the original Android-only referral tables without discarding their attribution history. */
class UpgradeAndroidReferralToAffiliate extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('android_referral_settings')) return;
        if (!$this->db->fieldExists('commission_percent', 'android_referral_settings')) {
            $this->db->query("ALTER TABLE `android_referral_settings` ADD `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00 AFTER `referrer_reward_points`");
        }
        if ($this->db->tableExists('android_referral_attributions')) {
            if (!$this->db->fieldExists('commission_percent', 'android_referral_attributions')) {
                $this->db->query("ALTER TABLE `android_referral_attributions` ADD `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00 AFTER `referrer_reward_points`");
            }
            if (!$this->db->fieldExists('channel', 'android_referral_attributions')) {
                $this->db->query("ALTER TABLE `android_referral_attributions` ADD `channel` VARCHAR(16) NOT NULL DEFAULT 'android' AFTER `commission_percent`");
            }
        }
        if ($this->db->tableExists('android_referral_rewards')) {
            if (!$this->db->fieldExists('commission_percent', 'android_referral_rewards')) {
                $this->db->query("ALTER TABLE `android_referral_rewards` ADD `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00 AFTER `eligible_subtotal`");
            }
            if (!$this->db->fieldExists('channel', 'android_referral_rewards')) {
                $this->db->query("ALTER TABLE `android_referral_rewards` ADD `channel` VARCHAR(16) NOT NULL DEFAULT 'android' AFTER `commission_percent`");
            }
            $indexes = $this->db->query("SHOW INDEX FROM `android_referral_rewards`")->getResultArray();
            if (in_array('uq_android_referral_reward_invitee', array_column($indexes, 'Key_name'), true)) {
                $this->db->query("ALTER TABLE `android_referral_rewards` DROP INDEX `uq_android_referral_reward_invitee`");
            }
        }
    }

    public function down()
    {
        throw new \RuntimeException('Affiliate commission and order history are retained; this migration is intentionally irreversible.');
    }
}
