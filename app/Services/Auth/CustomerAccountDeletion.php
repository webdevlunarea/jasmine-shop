<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/** Processes a customer's deletion request only when no protected history requires retention. */
final class CustomerAccountDeletion
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function startReview(int $requestId): void
    {
        $this->requireRequestTable();
        $now = gmdate('Y-m-d H:i:s');
        $ok = $this->db->table('customer_account_deletion_requests')
            ->where(['id' => $requestId, 'status' => 'pending'])
            ->update(['status' => 'processing', 'updated_at' => $now]);
        if (!$ok || $this->db->affectedRows() !== 1) {
            throw new \DomainException('Permintaan sudah berubah atau tidak ditemukan. Muat ulang daftar.');
        }
    }

    /** Permanently removes account/profile data only when no transaction-related records need retention. */
    public function deleteEligibleRequest(int $requestId): void
    {
        $this->requireRequestTable();
        if ($requestId < 1 || !$this->db->transBegin()) throw new RuntimeException('Proses penghapusan belum dapat dimulai.');

        try {
            // Require the explicit review transition; direct POSTs cannot skip review.
            $query = $this->db->table('customer_account_deletion_requests')
                ->where('id', $requestId)->where('status', 'processing');
            $sql = $query->getCompiledSelect();
            if ($this->db->DBDriver !== 'SQLite3') $sql .= ' FOR UPDATE';
            $request = $this->db->query($sql)->getRowArray();
            if (!$request) throw new \DomainException('Permintaan aktif tidak ditemukan. Muat ulang daftar.');

            $email = strtolower(trim((string)$request['email_user']));
            $userQuery = $this->db->table('user')->where($this->emailMatch('email', $email), null, false)
                ->where('role', '0')->getCompiledSelect();
            if ($this->db->DBDriver !== 'SQLite3') $userQuery .= ' FOR UPDATE';
            $user = $this->db->query($userQuery)->getRowArray();
            if (!$user) throw new \DomainException('Akun pelanggan tidak ditemukan. Periksa permintaan sebelum menutupnya.');

            $protected = [
                'pemesanan' => 'email_cus',
                'retur_pengajuan' => 'email_cus',
                'preorder_barang' => 'email_customer',
                'rating' => 'email_cus',
                'point_history' => 'email_user',
                'voucher_redeem' => 'email_user',
                // Referral rewards are financial/points evidence, even if the buyer's order
                // row is not present in an older or migrated database.
                'android_referral_rewards' => ['referrer_email', 'invitee_email'],
            ];
            $retainedRecords = [];
            foreach ($protected as $table => $columns) {
                foreach ((array)$columns as $column) {
                    if ($this->db->tableExists($table) && in_array($column, $this->db->getFieldNames($table), true)
                        && $this->db->table($table)->where($this->emailMatch($column, $email), null, false)->countAllResults() > 0) {
                        $retainedRecords[] = $table;
                        break;
                    }
                }
            }
            if ($retainedRecords) {
                throw new \DomainException('Akun memiliki riwayat transaksi atau data yang perlu disimpan. Penghapusan otomatis dibatalkan; lakukan peninjauan retensi terlebih dahulu.');
            }

            // Remove account-owned identity, authentication, and profile rows. Tables are
            // optional across deployments; transaction and evidence tables are intentionally excluded.
            $accountRows = [
                'customer_usernames' => 'email_user',
                'customer_email_changes' => 'email_user',
                'customer_password_reset_codes' => 'email_user',
                'customer_password_sessions' => 'email_user',
                'customer_addresses' => 'email_user',
                'facebook_identities' => 'email_user',
                'customer_totp_mfa' => 'email_user',
                'customer_api_tokens' => 'email_user',
                'customer_push_devices' => 'email_user',
                'customer_push_notifications' => 'email_user',
                'voucher_claimed' => 'email_user',
                'gambar_user' => 'email_user',
                'shipping_quotes' => 'email_user',
                'customer_marketing_preferences' => 'email_user',
                'customer_voucher_email_recipients' => 'email_user',
                // Codes and attributions are account-owned; reward rows above remain protected.
                'android_referral_codes' => 'email_user',
                'android_referral_attributions' => 'invitee_email',
            ];
            foreach ($accountRows as $table => $column) {
                if ($this->db->tableExists($table) && in_array($column, $this->db->getFieldNames($table), true)
                    && !$this->db->table($table)->where($this->emailMatch($column, $email), null, false)->delete()) {
                    throw new RuntimeException('Data akun terkait belum dapat dihapus.');
                }
            }

            if ($this->db->tableExists('pembeli') && in_array('email_user', $this->db->getFieldNames('pembeli'), true)
                && !$this->db->table('pembeli')->where($this->emailMatch('email_user', $email), null, false)->delete()) {
                throw new RuntimeException('Profil akun belum dapat dihapus.');
            }
            if (!$this->db->table('user')->where($this->emailMatch('email', $email), null, false)
                ->where('role', '0')->delete()
                || $this->db->affectedRows() !== 1) {
                throw new RuntimeException('Akun pelanggan belum dapat dihapus.');
            }

            $now = gmdate('Y-m-d H:i:s');
            if (!$this->db->table('customer_account_deletion_requests')->where('id', $requestId)->update([
                'status' => 'completed', 'processed_at' => $now, 'updated_at' => $now,
            ])) {
                throw new RuntimeException('Status permintaan belum dapat disimpan.');
            }
            if ($this->db->transStatus() === false || !$this->db->transCommit()) {
                throw new RuntimeException('Penghapusan belum berhasil disimpan.');
            }
        } catch (\Throwable $error) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            throw $error;
        }
    }

    private function requireRequestTable(): void
    {
        if (!$this->db->tableExists('customer_account_deletion_requests')) {
            throw new RuntimeException('Antrean permintaan penghapusan belum tersedia.');
        }
    }

    /** Email values may differ only by casing/whitespace across legacy tables. */
    private function emailMatch(string $column, string $email): string
    {
        // Column names are constants from the internal table allowlists above.
        return 'LOWER(TRIM(`' . $column . '`)) = ' . $this->db->escape($email);
    }
}
