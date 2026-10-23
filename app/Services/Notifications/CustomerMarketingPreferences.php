<?php

namespace App\Services\Notifications;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use InvalidArgumentException;

/** Email marketing uses opt-out semantics; push delivery follows active Android notification registration. */
final class CustomerMarketingPreferences
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function get(string $email): array
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Email akun tidak valid.');
        if (!$this->db->tableExists('customer_marketing_preferences')) {
            return ['email_promotions' => true];
        }
        $row = $this->db->table('customer_marketing_preferences')->where('email_user', $email)->get()->getRowArray();
        return [
            'email_promotions' => !$row || empty($row['email_unsubscribed_at']),
        ];
    }

    public function update(string $email, bool $emailPromotions): array
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Email akun tidak valid.');
        if (!$this->db->tableExists('customer_marketing_preferences')) {
            throw new \RuntimeException('Marketing preferences schema is not deployed.');
        }
        $now = gmdate('Y-m-d H:i:s');
        $existing = $this->db->table('customer_marketing_preferences')->where('email_user', $email)->get()->getRowArray();
        if (!$this->db->fieldExists('email_unsubscribed_at', 'customer_marketing_preferences')) {
            throw new \RuntimeException('Marketing unsubscribe migration is not deployed.');
        }
        $data = [
            'email_user' => $email,
            'email_promotions' => $emailPromotions ? 1 : 0,
            'email_consent_at' => null,
            'push_promotions' => 0,
            'push_consent_at' => null,
            'email_unsubscribed_at' => $emailPromotions ? null : ($existing['email_unsubscribed_at'] ?? $now),
            'email_unsubscribe_token_hash' => $emailPromotions ? ($existing['email_unsubscribe_token_hash'] ?? null) : null,
            'email_unsubscribe_token_encrypted' => $emailPromotions ? ($existing['email_unsubscribe_token_encrypted'] ?? null) : null,
            'updated_at' => $now,
        ];
        $saved = $existing
            ? $this->db->table('customer_marketing_preferences')->where('email_user', $email)->update($data)
            : $this->db->table('customer_marketing_preferences')->insert($data);
        if ($saved === false) throw new \RuntimeException('Preference update failed.');

        return ['email_promotions' => $emailPromotions];
    }

    public function unsubscribeToken(string $email): string
    {
        $email = strtolower(trim($email));
        $row = $this->db->table('customer_marketing_preferences')->where('email_user', $email)->get()->getRowArray();
        if (!$row) {
            $this->update($email, true);
            $row = $this->db->table('customer_marketing_preferences')->where('email_user', $email)->get()->getRowArray();
        }
        if (!$row || !empty($row['email_unsubscribed_at'])) throw new \RuntimeException('Email promotions are not enabled.');
        if (!empty($row['email_unsubscribe_token_hash']) && !empty($row['email_unsubscribe_token_encrypted'])) {
            $encoded = (string)$row['email_unsubscribe_token_encrypted'];
            $ciphertext = str_starts_with($encoded, 'b64:') ? base64_decode(substr($encoded, 4), true) : false;
            if ($ciphertext !== false) return service('encrypter')->decrypt($ciphertext);
        }
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $ciphertext = service('encrypter')->encrypt($token);
        $this->db->table('customer_marketing_preferences')->where('email_user', $email)->update([
            'email_unsubscribe_token_hash' => hash('sha256', $token),
            'email_unsubscribe_token_encrypted' => 'b64:' . base64_encode($ciphertext),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
        return $token;
    }

    public function unsubscribeByToken(string $token): bool
    {
        if (!preg_match('/^[A-Za-z0-9_-]{40,50}$/', $token) || !$this->db->tableExists('customer_marketing_preferences')) return false;
        $saved = $this->db->table('customer_marketing_preferences')
            ->where('email_unsubscribe_token_hash', hash('sha256', $token))
            ->where('email_promotions', 1)
            ->update([
                'email_promotions' => 0,
                'email_consent_at' => null,
                'email_unsubscribed_at' => gmdate('Y-m-d H:i:s'),
                'email_unsubscribe_token_hash' => null,
                'email_unsubscribe_token_encrypted' => null,
                'updated_at' => gmdate('Y-m-d H:i:s'),
            ]);
        return $saved !== false && $this->db->affectedRows() === 1;
    }
}
