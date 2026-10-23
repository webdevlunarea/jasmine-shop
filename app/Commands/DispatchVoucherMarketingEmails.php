<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/** Sends voucher emails in small, retryable batches; explicit unsubscriptions are excluded. */
class DispatchVoucherMarketingEmails extends BaseCommand
{
    protected $group = 'Marketing';
    protected $name = 'marketing:voucher-email-dispatch';
    protected $description = 'Kirim antrean email voucher bertahap kepada pelanggan yang memilih ikut.';
    protected $options = ['--limit' => 'Jumlah email maksimum per eksekusi (1–20, default 10).'];

    public function run(array $params)
    {
        $db = Database::connect();
        foreach (['customer_marketing_preferences', 'customer_voucher_email_campaigns', 'customer_voucher_email_recipients'] as $table) {
            if (!$db->tableExists($table)) {
                CLI::error('Tabel antrean email promosi belum tersedia. Jalankan migration terlebih dahulu.');
                return EXIT_ERROR;
            }
        }
        $limit = max(1, min(5, (int)(CLI::getOption('limit') ?: 2)));
        $now = gmdate('Y-m-d H:i:s');
        $emailDailyCutoff = gmdate('Y-m-d H:i:s', time() - 86400);
        $recentlySent = (int)$db->table('customer_voucher_email_recipients')->where('status', 'sent')
            ->where('sent_at >=', $emailDailyCutoff)->countAllResults();
        if ($recentlySent >= 100) {
            CLI::write('Batas aman 100 email promosi per 24 jam tercapai; antrean menunggu jendela pengiriman berikutnya.');
            return EXIT_SUCCESS;
        }
        $limit = min($limit, 100 - $recentlySent);
        $db->table('customer_voucher_email_recipients')->where('status', 'processing')
            ->where('updated_at <', gmdate('Y-m-d H:i:s', time() - 900))
            ->update(['status' => 'pending', 'updated_at' => $now]);

        $rows = $db->table('customer_voucher_email_recipients r')
            ->select('r.*, c.subject, c.body_html, c.voucher_id')
            ->join('customer_voucher_email_campaigns c', 'c.id = r.campaign_id')
            ->where('r.status', 'pending')->where('r.next_attempt_at <=', $now)
            ->orderBy('r.id', 'ASC')->limit($limit)->get()->getResultArray();

        $sent = 0; $deferred = 0; $failed = 0; $cancelled = 0;
        foreach ($rows as $row) {
            $claimed = $db->table('customer_voucher_email_recipients')
                ->where(['id' => $row['id'], 'status' => 'pending'])->update(['status' => 'processing', 'updated_at' => $now]);
            if (!$claimed || $db->affectedRows() !== 1) continue;

            $email = strtolower(trim((string)$row['email_user']));
            $eligible = $db->table('user u')->select('u.email')
                ->join('customer_marketing_preferences p', 'LOWER(p.email_user) = LOWER(u.email)', 'left', false)
                ->where('u.active', '1')->where('u.role', '0')->where('p.email_unsubscribed_at', null)
                ->where('LOWER(u.email) = ' . $db->escape($email), null, false)->get()->getRowArray();
            $voucher = $db->table('voucher')->select('id, active')->where('id', $row['voucher_id'])->get()->getRowArray();
            if (!$eligible || !$voucher || empty($voucher['active'])) {
                $db->table('customer_voucher_email_recipients')->where('id', $row['id'])->update([
                    'status' => 'cancelled', 'last_error' => !$eligible ? 'OPT_OUT_OR_INACTIVE' : 'VOUCHER_INACTIVE', 'updated_at' => $now,
                ]);
                $cancelled++;
                continue;
            }

            // A global 24-hour cap shared with FCM keeps one account from receiving repeated promotions across channels.
            $cutoff = gmdate('Y-m-d H:i:s', time() - 86400);
            $lastEmail = $db->table('customer_voucher_email_recipients')
                ->selectMax('sent_at', 'last_sent')->where('email_hash', $row['email_hash'])->where('status', 'sent')
                ->where('sent_at >=', $cutoff)->get()->getRowArray()['last_sent'] ?? null;
            $lastPush = null;
            if ($db->tableExists('customer_push_notifications')) {
                $lastPush = $db->table('customer_push_notifications')->selectMax('sent_at', 'last_sent')
                    ->where('email_user', $email)->like('notification_type', 'broadcast_', 'after')
                    ->where('status', 'sent')->where('sent_at >=', $cutoff)->get()->getRowArray()['last_sent'] ?? null;
            }
            $recentMarketingSends = array_filter([(string)$lastEmail, (string)$lastPush]);
            $lastSent = $recentMarketingSends ? max($recentMarketingSends) : '';
            if ($lastSent !== '') {
                $next = gmdate('Y-m-d H:i:s', strtotime($lastSent . ' UTC') + 86400);
                $db->table('customer_voucher_email_recipients')->where('id', $row['id'])->update([
                    'status' => 'pending', 'next_attempt_at' => $next, 'last_error' => 'MARKETING_FREQUENCY_CAP', 'updated_at' => $now,
                ]);
                $deferred++;
                continue;
            }

            try {
                $emailService = \Config\Services::email();
                $emailConfig = config('Email');
                $from = $emailConfig->fromEmail ?: $emailConfig->SMTPUser;
                $emailService->clear(true);
                $emailService->setFrom($from, $emailConfig->fromName ?: 'Lunarea Furniture');
                $emailService->setReplyTo($from, 'Customer Care Lunarea Furniture');
                $emailService->setTo($email);
                $emailService->setSubject((string)$row['subject']);
                $emailService->setMailType('html');
                $unsubscribeToken = (new \App\Services\Notifications\CustomerMarketingPreferences($db))->unsubscribeToken($email);
                $unsubscribeUrl = rtrim(base_url(), '/') . '/promosi/berhenti/' . rawurlencode($unsubscribeToken);
                $bodyHtml = (string)$row['body_html']
                    . '<p style="margin-top:20px;font-size:12px;color:#68736b">Email promosi ini dikirim karena Anda memilih menerima kabar promosi Lunarea. <a href="' . esc($unsubscribeUrl) . '">Berhenti menerima email promosi</a>.</p>';
                $message = view('emails/layout', [
                    'title' => (string)$row['subject'], 'preheader' => (string)$row['subject'],
                    'bodyHtml' => '<div style="color:#3f3b34;font-size:15px;line-height:1.75">' . $bodyHtml . '</div>',
                ], ['saveData' => false]);
                $emailService->setMessage($message);
                $emailService->setAltMessage(trim(preg_replace('/\s+/', ' ', strip_tags($bodyHtml))) . "\n\nBerhenti menerima email promosi: " . $unsubscribeUrl);
                if (!$emailService->send(false)) throw new \RuntimeException('SMTP_SEND_FAILED');
                $db->table('customer_voucher_email_recipients')->where('id', $row['id'])->update([
                    'status' => 'sent', 'sent_at' => $now, 'last_error' => null, 'updated_at' => $now,
                ]);
                $sent++;
            } catch (\Throwable $error) {
                $attempts = (int)$row['attempts'] + 1;
                $isFailed = $attempts >= 6;
                $delay = min(21600, 120 * (2 ** min($attempts, 7)));
                $db->table('customer_voucher_email_recipients')->where('id', $row['id'])->update([
                    'status' => $isFailed ? 'failed' : 'pending', 'attempts' => $attempts,
                    'next_attempt_at' => gmdate('Y-m-d H:i:s', time() + $delay),
                    'last_error' => 'EMAIL_DELIVERY_FAILED', 'updated_at' => $now,
                ]);
                log_message('error', 'Voucher marketing email delivery failed: recipient_id=' . (int)$row['id'] . ' exception=' . get_class($error));
                $failed++;
            }
            // Keep this worker gentle on shared SMTP providers and server resources.
            usleep(250000);
        }

        $campaignIds = array_values(array_unique(array_map(static fn(array $r): int => (int)$r['campaign_id'], $rows)));
        foreach ($campaignIds as $campaignId) {
            $remaining = $db->table('customer_voucher_email_recipients')->where('campaign_id', $campaignId)
                ->whereIn('status', ['pending', 'processing'])->countAllResults();
            if ($remaining === 0) $db->table('customer_voucher_email_campaigns')->where('id', $campaignId)
                ->update(['status' => 'complete', 'updated_at' => $now]);
        }
        CLI::write("Diproses: " . count($rows) . "; terkirim: {$sent}; ditunda: {$deferred}; gagal: {$failed}; dilewati: {$cancelled}.");
        return EXIT_SUCCESS;
    }
}
