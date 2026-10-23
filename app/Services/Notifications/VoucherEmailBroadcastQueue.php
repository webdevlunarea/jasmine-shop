<?php

namespace App\Services\Notifications;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;

/** Durable, deduplicated email outbox for explicitly opted-in voucher recipients. */
final class VoucherEmailBroadcastQueue
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function queue(int $voucherId, string $adminEmail): int
    {
        if ($voucherId < 1 || !$this->db->tableExists('customer_marketing_preferences')
            || !$this->db->tableExists('customer_voucher_email_campaigns')
            || !$this->db->tableExists('customer_voucher_email_recipients')
            || !$this->db->tableExists('customer_marketing_campaign_guard')) {
            throw new RuntimeException('Marketing broadcast storage is unavailable.');
        }
        $voucher = $this->db->table('voucher')->where('id', $voucherId)->get()->getRowArray();
        if (!$voucher || empty($voucher['active']) || trim((string)($voucher['isi_email'] ?? '')) === '') return 0;
        if ($this->db->table('customer_voucher_email_campaigns')->where('voucher_id', $voucherId)->countAllResults() > 0) {
            throw new RuntimeException('VOUCHER_CAMPAIGN_ALREADY_QUEUED');
        }


        $rawRecipients = json_decode((string)($voucher['code'] ?? ''), true);
        if (!is_array($rawRecipients)) return 0;
        $targets = [];
        foreach ($rawRecipients as $recipient) {
            $email = strtolower(trim((string)($recipient['email_user'] ?? '')));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) $targets[$email] = true;
        }
        if (!$targets) return 0;

        // Explicit consent and active customer account are required; legacy accounts have no preference row and are excluded.
        $eligible = [];
        foreach (array_chunk(array_keys($targets), 400) as $chunk) {
            $rows = $this->db->table('customer_marketing_preferences p')
                ->select('p.email_user')
                ->join('user u', 'LOWER(u.email) = LOWER(p.email_user)', 'inner', false)
                ->where('p.email_promotions', 1)
                ->where('u.role', '0')->where('u.active', '1')
                ->whereIn('p.email_user', $chunk)->get()->getResultArray();
            foreach ($rows as $row) {
                $email = strtolower(trim((string)$row['email_user']));
                if (isset($targets[$email])) $eligible[$email] = true;
            }
        }
        if (!$eligible) return 0;

        $html = '<div style="width:100%"><img src="https://lunareafurniture.com/imgvoucher/email/' . (int)$voucher['id'] . '" alt="Informasi voucher" style="display:block;width:100%;height:auto;border-radius:5px" /></div>'
            . '<div style="height:15px"></div><div style="background:#fff;padding:20px;border-radius:10px"><table role="presentation" style="width:100%"><tbody>'
            . (string)$voucher['isi_email'] . '</tbody></table></div>';
        $now = gmdate('Y-m-d H:i:s');

        $this->db->transBegin();
        try {
            (new MarketingCampaignRateLimiter())->claim($this->db);
            // One campaign per voucher makes repeated admin clicks idempotent.
            $this->db->table('customer_voucher_email_campaigns')->insert([
                'voucher_id' => $voucherId,
                'subject' => 'Lunarea Store - ' . mb_substr((string)$voucher['nama'], 0, 220),
                'body_html' => $html,
                'status' => 'queued',
                'created_by' => mb_substr(strtolower(trim($adminEmail)), 0, 254),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $campaignId = (int)$this->db->insertID();
            if ($campaignId < 1) throw new RuntimeException('Campaign creation failed.');
            $rows = [];
            foreach (array_keys($eligible) as $email) {
                $rows[] = [
                    'campaign_id' => $campaignId,
                    'email_user' => $email,
                    'email_hash' => hash('sha256', $email),
                    'status' => 'pending',
                    'attempts' => 0,
                    'next_attempt_at' => $now,
                    'sent_at' => null,
                    'last_error' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($rows, 250) as $batch) {
                if ($this->db->table('customer_voucher_email_recipients')->insertBatch($batch) === false) {
                    throw new RuntimeException('Recipient queue insert failed.');
                }
            }
            if ($this->db->transStatus() === false) throw new RuntimeException('Campaign queue transaction failed.');
            $this->db->transCommit();
            return count($rows);
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }
}
