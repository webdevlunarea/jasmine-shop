<?php

namespace Tests\Unit;

use App\Services\Referrals\AndroidReferralProgram;
use Config\Database;
use DomainException;
use PHPUnit\Framework\TestCase;

final class AndroidReferralProgramTest extends TestCase
{
    private $db;
    private AndroidReferralProgram $program;

    protected function setUp(): void
    {
        $this->db = Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->db->query('CREATE TABLE android_referral_settings (id INTEGER PRIMARY KEY, enabled INTEGER, referrer_reward_points INTEGER, minimum_subtotal INTEGER, reward_trigger_status TEXT, point_expiry_days INTEGER, updated_at TEXT)');
        $this->db->query('CREATE TABLE android_referral_codes (id INTEGER PRIMARY KEY AUTOINCREMENT, email_user TEXT UNIQUE, referral_code TEXT UNIQUE, created_at TEXT)');
        $this->db->query('CREATE TABLE android_referral_attributions (id INTEGER PRIMARY KEY AUTOINCREMENT, invitee_email TEXT UNIQUE, referral_code TEXT, referrer_reward_points INTEGER, minimum_subtotal INTEGER, reward_trigger_status TEXT, point_expiry_days INTEGER, created_at TEXT)');
        $this->db->query('CREATE TABLE android_referral_rewards (id INTEGER PRIMARY KEY AUTOINCREMENT, order_id TEXT UNIQUE, referrer_email TEXT, invitee_email TEXT UNIQUE, points INTEGER, eligible_subtotal INTEGER, state TEXT, created_at TEXT, awarded_at TEXT)');
        $this->db->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY AUTOINCREMENT, id_midtrans TEXT, email_cus TEXT, status TEXT, data_mid TEXT)');
        $this->db->query('CREATE TABLE pembeli (email_user TEXT PRIMARY KEY, poin TEXT)');
        $this->db->query('CREATE TABLE point_history (id INTEGER PRIMARY KEY, label TEXT, nominal INTEGER, keterangan TEXT, tanggal TEXT, email_user TEXT)');
        $this->db->table('android_referral_settings')->insert(array_merge(['id' => 1, 'updated_at' => '2026-10-21 10:00:00'], AndroidReferralProgram::defaults()));
        $this->db->table('pembeli')->insert(['email_user' => 'inviter@example.test', 'poin' => '[]']);
        $this->program = new AndroidReferralProgram($this->db);
    }

    protected function tearDown(): void { $this->db->close(); }

    public function testDefaultsMatchApprovedPilotSettings(): void
    {
        self::assertSame(5000, AndroidReferralProgram::defaults()['referrer_reward_points']);
        self::assertSame(300000, AndroidReferralProgram::defaults()['minimum_subtotal']);
        self::assertSame('Selesai', AndroidReferralProgram::defaults()['reward_trigger_status']);
        self::assertSame(0, AndroidReferralProgram::defaults()['enabled']);
    }

    public function testReferralRewardIsOnlyIssuedOnceForCompletedProductionAndroidOrder(): void
    {
        $this->program->saveSettings(['enabled' => '1', 'referrer_reward_points' => '5000', 'minimum_subtotal' => '300000', 'point_expiry_days' => '365', 'reward_trigger_status' => 'Selesai']);
        $code = $this->program->overview('inviter@example.test')['code'];
        $this->program->attribute('invitee@example.test', $code);
        $order = ['id_midtrans' => 'ORDER-1', 'email_cus' => 'invitee@example.test', 'status' => 'Selesai',
            'data_mid' => json_encode(['_app_checkout_channel' => 'android', '_app_payment_environment' => 'production', '_app_eligible_subtotal' => 350000])];
        $this->db->table('pemesanan')->insert(['id_midtrans' => $order['id_midtrans'], 'email_cus' => $order['email_cus'], 'status' => 'Selesai', 'data_mid' => $order['data_mid']]);
        self::assertTrue($this->program->rewardCompletedOrder($order));
        self::assertFalse($this->program->rewardCompletedOrder($order));
        $points = json_decode($this->db->table('pembeli')->where('email_user', 'inviter@example.test')->get()->getRow('poin'), true);
        self::assertSame(5000, $points[0]['nominal']);
        self::assertSame(1, $this->db->table('point_history')->countAllResults());
        self::assertSame(1, $this->db->table('android_referral_rewards')->where('state', 'awarded')->countAllResults());
    }

    public function testSelfReferralIsRejectedAndBelowMinimumOrNonAndroidOrdersDoNotEarn(): void
    {
        $this->program->saveSettings(['enabled' => '1', 'referrer_reward_points' => '5000', 'minimum_subtotal' => '300000', 'point_expiry_days' => '365', 'reward_trigger_status' => 'Selesai']);
        $code = $this->program->overview('inviter@example.test')['code'];
        try { $this->program->attribute('inviter@example.test', $code); self::fail('Self-referral must be blocked.'); }
        catch (DomainException $error) { self::assertStringContainsString('sendiri', $error->getMessage()); }
        $this->program->attribute('invitee@example.test', $code);
        $base = ['id_midtrans' => 'ORDER-2', 'email_cus' => 'invitee@example.test', 'status' => 'Selesai'];
        $webOrder = $base + ['data_mid' => json_encode(['_app_checkout_channel' => 'website', '_app_payment_environment' => 'production', '_app_eligible_subtotal' => 500000])];
        self::assertFalse($this->program->rewardCompletedOrder($webOrder));
        $smallOrder = $base + ['data_mid' => json_encode(['_app_checkout_channel' => 'android', '_app_payment_environment' => 'production', '_app_eligible_subtotal' => 299999])];
        self::assertFalse($this->program->rewardCompletedOrder($smallOrder));
    }
}
