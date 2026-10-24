<?php

namespace App\Services\Auth;

use CodeIgniter\Database\BaseConnection;

class CustomerEmailChanges
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null) { $this->db = $db ?? db_connect(); }

    private function tableReady(): void
    {
        if (!$this->db->tableExists('customer_email_changes')) {
            throw new \RuntimeException('Email change schema is not deployed');
        }
    }

    public function settings(string $email): array
    {
        $this->tableReady();
        $row = $this->db->table('customer_email_changes')->where('email_user', $email)->get()->getRowArray() ?: [];
        $next = null;
        if (!empty($row['changed_at'])) {
            $next = (new \DateTimeImmutable($row['changed_at'], new \DateTimeZone('UTC')))->modify('+6 months');
        }
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        return [
            'available' => true,
            'can_change' => !$next || $now >= $next,
            'next_change_label' => $next ? $next->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('d/m/Y') : null,
            'pending_email' => (string)($row['pending_email'] ?? ''),
            'pending_expires' => $row['expires_at'] ?? null,
        ];
    }

    public function request(string $email, string $password, string $newEmail): void
    {
        $this->tableReady();
        $newEmail = mb_strtolower(trim($newEmail));
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL) || mb_strlen($newEmail) > 254) {
            throw new \DomainException('Masukkan alamat email baru yang valid.');
        }
        if (strcasecmp($email, $newEmail) === 0) throw new \DomainException('Email baru sama dengan email akun saat ini.');
        $user = $this->db->table('user')->where('email', $email)->get()->getRowArray();
        if (!$user || empty($user['sandi']) || !password_verify($password, (string)$user['sandi'])) {
            throw new \DomainException('Kata sandi saat ini tidak sesuai.');
        }
        if ($this->db->table('user')->where('email', $newEmail)->countAllResults() > 0) {
            throw new \DomainException('Email tersebut sudah digunakan akun lain.');
        }
        $old = $this->db->table('customer_email_changes')->where('email_user', $email)->get()->getRowArray();
        if ($old && !empty($old['changed_at'])) {
            $next = (new \DateTimeImmutable($old['changed_at'], new \DateTimeZone('UTC')))->modify('+6 months');
            if (new \DateTimeImmutable('now', new \DateTimeZone('UTC')) < $next) {
                throw new \DomainException('Email hanya dapat diganti setiap 6 bulan. Perubahan berikutnya tersedia mulai ' . $next->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('d/m/Y') . '.');
            }
        }
        if ($old && !empty($old['requested_at']) && strtotime($old['requested_at'] . ' UTC') > time() - 60) {
            throw new \DomainException('Tunggu 1 menit sebelum meminta kode baru.');
        }

        $code = (string)random_int(100000, 999999);
        $now = gmdate('Y-m-d H:i:s');
        $record = [
            'email_user' => $email, 'pending_email' => $newEmail,
            'otp_hash' => password_hash($code, PASSWORD_DEFAULT),
            'request_id' => bin2hex(random_bytes(18)),
            'expires_at' => gmdate('Y-m-d H:i:s', time() + 600), 'requested_at' => $now,
            'attempts' => 0, 'updated_at' => $now,
        ];
        try {
            if ($old) $this->db->table('customer_email_changes')->where('email_user', $email)->update($record);
            else $this->db->table('customer_email_changes')->insert($record + ['created_at' => $now]);
        } catch (\Throwable $e) {
            if ((int)($this->db->error()['code'] ?? 0) === 1062) throw new \DomainException('Alamat email tersebut sedang dalam proses perubahan akun lain.');
            throw new \RuntimeException('Email change request could not be stored');
        }
        if (!$this->sendCode($newEmail, $code)) {
            $this->db->table('customer_email_changes')->where('email_user', $email)->where('request_id', $record['request_id'])
                ->update(['pending_email' => null, 'otp_hash' => null, 'request_id' => null, 'expires_at' => null]);
            throw new \RuntimeException('Kode verifikasi gagal dikirim');
        }
    }

    public function confirm(string $email, string $password, string $code): string
    {
        $this->tableReady();
        $user = $this->db->table('user')->where('email', $email)->get()->getRowArray();
        if (!$user || empty($user['sandi']) || !password_verify($password, (string)$user['sandi'])) {
            throw new \DomainException('Kata sandi saat ini tidak sesuai.');
        }
        $row = $this->db->table('customer_email_changes')->where('email_user', $email)->get()->getRowArray();
        if (!$row || empty($row['pending_email']) || empty($row['otp_hash']) || empty($row['expires_at'])) {
            throw new \DomainException('Tidak ada permintaan perubahan email yang aktif.');
        }
        if (strtotime($row['expires_at'] . ' UTC') < time()) throw new \DomainException('Kode sudah kedaluwarsa. Ajukan kode baru.');
        if ((int)$row['attempts'] >= 5) throw new \DomainException('Batas percobaan tercapai. Ajukan kode baru.');
        if (!preg_match('/^\d{6}$/', $code) || !password_verify($code, $row['otp_hash'])) {
            $this->db->table('customer_email_changes')->where('email_user', $email)
                ->where('request_id', $row['request_id'])->where('otp_hash', $row['otp_hash'])
                ->where('attempts <', 5)->set('attempts', 'attempts + 1', false)->update();
            throw new \DomainException('Kode verifikasi tidak sesuai.');
        }
        if ($this->db->table('user')->where('email', $row['pending_email'])->countAllResults() > 0) {
            throw new \DomainException('Email tersebut sudah digunakan akun lain.');
        }
        $newEmail = (string)$row['pending_email'];
        if (!$this->db->transBegin()) throw new \RuntimeException('Email change transaction could not start');
        try {
            // Claim the code once so parallel valid submissions cannot both change the account.
            $claimed = $this->db->table('customer_email_changes')->where('email_user', $email)
                ->where('request_id', $row['request_id'])->where('otp_hash', $row['otp_hash'])
                ->where('attempts <', 5)->where('expires_at >=', gmdate('Y-m-d H:i:s'))
                ->update(['otp_hash' => null]);
            if (!$claimed || $this->db->affectedRows() !== 1) {
                throw new \DomainException('Kode sudah digunakan atau kedaluwarsa. Ajukan kode baru.');
            }
            $userUpdated = $this->db->table('user')->where('email', $email)->update(['email' => $newEmail]);
            if (!$userUpdated || $this->db->affectedRows() !== 1) throw new \RuntimeException('Account email could not be updated');
            $buyerUpdated = $this->db->table('pembeli')->where('email_user', $email)->update(['email_user' => $newEmail]);
            if (!$buyerUpdated || $this->db->affectedRows() !== 1) throw new \RuntimeException('Customer email could not be updated');

            // Re-key only known account-owned references. Orders remain visible in the account history.
            foreach ([
                'customer_usernames' => 'email_user', 'facebook_identities' => 'email_user',
                'customer_totp_mfa' => 'email_user',
                'customer_addresses' => 'email_user',
                'customer_password_sessions' => 'email_user', 'voucher_claimed' => 'email_user',
                'gambar_user' => 'email_user', 'shipping_quotes' => 'email_user', 'point_history' => 'email_user',
                'retur_pengajuan' => 'email_cus', 'preorder_barang' => 'email_customer',
                'rating' => 'email_cus', 'pemesanan' => 'email_cus',
            ] as $table => $column) {
                if ($this->db->tableExists($table) && in_array($column, $this->db->getFieldNames($table), true)) {
                    if (!$this->db->table($table)->where($column, $email)->update([$column => $newEmail])) {
                        throw new \RuntimeException('Account reference could not be updated');
                    }
                }
            }
            if ($this->db->tableExists('customer_api_tokens')
                && in_array('email_user', $this->db->getFieldNames('customer_api_tokens'), true)
                && in_array('revoked_at', $this->db->getFieldNames('customer_api_tokens'), true)) {
                if (!$this->db->table('customer_api_tokens')->where('email_user', $email)->where('revoked_at', null)
                    ->update(['revoked_at' => gmdate('Y-m-d H:i:s')])) {
                    throw new \RuntimeException('Active API sessions could not be revoked');
                }
            }
            if ($this->db->tableExists('customer_totp_mfa')) {
                if (!$this->db->table('customer_totp_mfa')->where('email_user', $newEmail)->update([
                    'challenge_hash' => null, 'challenge_expires_at' => null,
                    'challenge_device_name' => null, 'challenge_attempts' => 0,
                ])) throw new \RuntimeException('MFA challenges could not be invalidated');
            }
            // Pending recovery codes are revoked at the identity change boundary.
            if ($this->db->tableExists('customer_password_reset_codes')) {
                if (!$this->db->table('customer_password_reset_codes')->where('email_user', $email)->delete()) {
                    throw new \RuntimeException('Pending recovery codes could not be revoked');
                }
            }

            // Voucher redeem stores a JSON email list; preserve all other entries exactly.
            if ($this->db->tableExists('voucher_redeem') && in_array('email_user', $this->db->getFieldNames('voucher_redeem'), true)) {
                $rows = $this->db->table('voucher_redeem')->select('code, email_user')->get()->getResultArray();
                foreach ($rows as $voucher) {
                    $list = json_decode((string)$voucher['email_user'], true);
                    if (!is_array($list)) continue;
                    $changed = false;
                    foreach ($list as &$entry) {
                        if (is_array($entry) && isset($entry['email_user']) && strcasecmp((string)$entry['email_user'], $email) === 0) {
                            $entry['email_user'] = $newEmail; $changed = true;
                        } elseif (is_string($entry) && strcasecmp($entry, $email) === 0) {
                            $entry = $newEmail; $changed = true;
                        }
                    }
                    unset($entry);
                    if ($changed && !$this->db->table('voucher_redeem')->where('code', $voucher['code'])->update(['email_user' => json_encode($list, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)])) {
                        throw new \RuntimeException('Voucher ownership could not be updated');
                    }
                }
            }
            $changeUpdated = $this->db->table('customer_email_changes')->where('email_user', $email)->update([
                'email_user' => $newEmail, 'pending_email' => null, 'otp_hash' => null, 'request_id' => null,
                'expires_at' => null, 'attempts' => 0, 'changed_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
            ]);
            if (!$changeUpdated || $this->db->affectedRows() !== 1) throw new \RuntimeException('Email change state could not be finalized');
            if ($this->db->tableExists('customer_password_sessions')) {
                $sessions = $this->db->table('customer_password_sessions');
                $version = bin2hex(random_bytes(32));
                $existing = $sessions->where('email_user', $newEmail)->get()->getRowArray();
                if ($existing) {
                    if (!$sessions->where('email_user', $newEmail)->update(['version' => $version])) throw new \RuntimeException('Password session could not be rotated');
                } elseif (!$sessions->insert(['email_user' => $newEmail, 'version' => $version])) {
                    throw new \RuntimeException('Password session could not be created');
                }
            }
            if ($this->db->transStatus() === false) throw new \RuntimeException('Email change transaction failed');
            if (!$this->db->transCommit()) throw new \RuntimeException('Email change transaction commit failed');
        } catch (\Throwable $e) {
            if ((int)$this->db->transDepth > 0) $this->db->transRollback();
            if ($e instanceof \DomainException) throw $e;
            throw new \RuntimeException('Email change transaction failed');
        }
        $this->sendNotice($email);
        return $newEmail;
    }

    protected function sendCode(string $to, string $code): bool { return $this->deliver($to, 'Verifikasi email baru Lunarea', '<p>Kode verifikasi email Anda:</p><p style="font-size:28px;font-weight:700;letter-spacing:6px">' . esc($code) . '</p><p>Kode berlaku 10 menit dan maksimal 5 kali percobaan. Abaikan jika Anda tidak meminta perubahan.</p>'); }
    protected function sendNotice(string $to): bool { return $this->deliver($to, 'Pemberitahuan perubahan email Lunarea', '<p>Email akun Lunarea Anda baru saja diubah.</p><p>Jika Anda tidak melakukan perubahan ini, segera gunakan pemulihan sandi dan hubungi layanan pelanggan.</p>'); }
    protected function deliver(string $to, string $subject, string $body): bool
    {
        $mail = null;
        try {
            $config = config(\Config\Email::class); $mail = \Config\Services::email(null, false);
            $mail->setFrom($config->fromEmail ?: $config->SMTPUser, $config->fromName ?: 'Lunarea Furniture');
            $mail->setTo($to); $mail->setSubject($subject); $mail->setMailType('html');
            $mail->setMessage(view('emails/layout', [
                'title' => $subject,
                'preheader' => 'Pemberitahuan keamanan akun Lunarea Furniture.',
                'bodyHtml' => '<div style="color:#3f3b34;font-size:15px;line-height:1.75">' . $body . '<p style="margin:22px 0 0">Salam,<br><strong>Lunarea Furniture</strong></p></div>',
            ], ['saveData' => false]));
            $mail->setAltMessage(trim(preg_replace('/\s+/', ' ', strip_tags($body))) . "\n\nLunarea furniture, always your choice, always your furniture.\nhttps://lunareafurniture.com");
            if (!$mail->send(false)) throw new \RuntimeException();
            return true;
        } catch (\Throwable $e) { log_message('error', 'Customer email-change notification delivery failed. Check SMTP configuration privately.'); return false; }
        finally { if ($mail !== null) $mail->clear(true); }
    }
}
