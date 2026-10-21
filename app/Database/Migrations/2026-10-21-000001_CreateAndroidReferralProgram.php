<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAndroidReferralProgram extends Migration
{
    public function up()
    {
        $this->db->query("CREATE TABLE `android_referral_settings` (
            `id` TINYINT UNSIGNED NOT NULL,
            `enabled` TINYINT(1) NOT NULL DEFAULT 0,
            `referrer_reward_points` INT UNSIGNED NOT NULL DEFAULT 5000,
            `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00,
            `minimum_subtotal` BIGINT UNSIGNED NOT NULL DEFAULT 300000,
            `reward_trigger_status` VARCHAR(16) NOT NULL DEFAULT 'Selesai',
            `point_expiry_days` SMALLINT UNSIGNED NOT NULL DEFAULT 365,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->db->query("INSERT INTO `android_referral_settings` (`id`,`enabled`,`referrer_reward_points`,`minimum_subtotal`,`reward_trigger_status`,`point_expiry_days`,`updated_at`) VALUES (1,0,5000,300000,'Selesai',365,UTC_TIMESTAMP())");
        $this->db->query("CREATE TABLE `android_referral_codes` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `email_user` VARCHAR(254) NOT NULL,
            `referral_code` CHAR(10) NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`), UNIQUE KEY `uq_android_referral_email` (`email_user`),
            UNIQUE KEY `uq_android_referral_code` (`referral_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->db->query("CREATE TABLE `android_referral_attributions` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `invitee_email` VARCHAR(254) NOT NULL,
            `referral_code` CHAR(10) NOT NULL,
            `referrer_reward_points` INT UNSIGNED NOT NULL,
            `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00,
            `channel` VARCHAR(16) NOT NULL DEFAULT 'android',
            `minimum_subtotal` BIGINT UNSIGNED NOT NULL,
            `reward_trigger_status` VARCHAR(16) NOT NULL,
            `point_expiry_days` SMALLINT UNSIGNED NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id`), UNIQUE KEY `uq_android_referral_invitee` (`invitee_email`),
            KEY `idx_android_referral_code` (`referral_code`,`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->db->query("CREATE TABLE `android_referral_rewards` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `order_id` VARCHAR(100) NOT NULL,
            `referrer_email` VARCHAR(254) NOT NULL,
            `invitee_email` VARCHAR(254) NOT NULL,
            `points` INT UNSIGNED NOT NULL,
            `eligible_subtotal` BIGINT UNSIGNED NOT NULL,
            `commission_percent` DECIMAL(5,2) NOT NULL DEFAULT 3.00,
            `channel` VARCHAR(16) NOT NULL DEFAULT 'android',
            `state` VARCHAR(16) NOT NULL DEFAULT 'awarded',
            `created_at` DATETIME NOT NULL,
            `awarded_at` DATETIME NULL,
            PRIMARY KEY (`id`), UNIQUE KEY `uq_android_referral_reward_order` (`order_id`),
            KEY `idx_android_referral_reward_owner` (`referrer_email`,`state`,`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down()
    {
        foreach (['android_referral_rewards','android_referral_attributions','android_referral_codes','android_referral_settings'] as $table) {
            if ($this->db->tableExists($table) && $this->db->table($table)->countAllResults() > 0) {
                throw new \RuntimeException('Rollback ditahan karena data referral sudah tersimpan.');
            }
        }
        foreach (['android_referral_rewards','android_referral_attributions','android_referral_codes','android_referral_settings'] as $table) $this->forge->dropTable($table, true);
    }
}
