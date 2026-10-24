<?php

namespace App\Controllers;

use App\Services\Auth\AdminPermissions;
use App\Services\Auth\PasswordResetService;

final class AdminStaffInvitations extends BaseController
{
    private function isDev(): bool
    {
        return (string) session('role') === '1' && strtoupper((string) session('admin_role')) === 'DEV';
    }

    private function allowedRole(string $role, $db): bool
    {
        if (in_array($role, ['DEV', 'SUPER_ADMIN', 'ADMIN'], true)) return false;
        return array_key_exists($role, AdminPermissions::assignableRoles())
            || (preg_match('/^CUSTOM_[A-Z0-9_]{1,24}$/', $role) === 1
                && $db->tableExists('admin_roles')
                && $db->table('admin_roles')->where('role_key', $role)->countAllResults() > 0);
    }

    public function create()
    {
        if (!$this->isDev()) return $this->response->setStatusCode(403)->setBody('Akses hanya untuk DEV.');
        $db = db_connect();
        if (!$db->tableExists('admin_staff_invitations') || !$db->tableExists('admin_role_audit')) {
            return $this->back('Jalankan SQL undangan staf terlebih dahulu.');
        }
        $emailInput = $this->request->getPost('email');
        $roleInput = $this->request->getPost('admin_role');
        $email = is_string($emailInput) ? strtolower(trim($emailInput)) : '';
        $role = is_string($roleInput) ? strtoupper(trim($roleInput)) : '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || !$this->allowedRole($role, $db)) {
            return $this->back('Email atau pilihan akses staf belum valid.');
        }
        if ($db->table('user')->where('email', $email)->countAllResults() > 0
            || ($db->tableExists('pembeli') && $db->table('pembeli')->where('email_user', $email)->countAllResults() > 0)) {
            return $this->back('Email sudah terhubung ke akun. Gunakan email lain yang belum terdaftar.');
        }

        $now = gmdate('Y-m-d H:i:s');
        $token = bin2hex(random_bytes(32));
        $expires = gmdate('Y-m-d H:i:s', time() + 172800);
        $db->transBegin();
        try {
            $existing = $db->table('admin_staff_invitations')->where('email', $email)->get()->getRowArray();
            if ($existing && $existing['status'] !== 'pending') {
                throw new \DomainException('Email ini pernah menyelesaikan undangan staf. Hubungi Dev untuk meninjau akun tersebut.');
            }
            $inviteData = ['email' => $email, 'token_hash' => hash('sha256', $token), 'role_key' => $role,
                'status' => 'pending', 'expires_at' => $expires, 'created_by' => (string) session('email'), 'created_at' => $now];
            if ($existing) $db->table('admin_staff_invitations')->where('id', $existing['id'])->update($inviteData);
            else $db->table('admin_staff_invitations')->insert($inviteData);
            $db->table('admin_role_audit')->insert(['actor_email' => (string)session('email'), 'action' => 'INVITE_STAFF',
                'role_key' => $role, 'target_email' => $email, 'details' => json_encode(['expires_at' => $expires], JSON_UNESCAPED_UNICODE), 'created_at' => $now]);
            if ($db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Invitation storage failed');
        } catch (\Throwable $error) {
            $db->transRollback();
            if ($error instanceof \DomainException) return $this->back($error->getMessage());
            log_message('error', 'Staff invitation creation failed: {type}', ['type' => get_class($error)]);
            return $this->back('Undangan belum tersimpan. Periksa kesiapan database lalu coba lagi.');
        }

        $url = site_url('staff/invite/' . $token);
        if (!(new \App\Services\Auth\AdminStaffInvitationMailer())->send($email, $url, $role, $expires)) {
            return $this->back('Undangan tersimpan, tetapi email belum terkirim. Kirim ulang dari halaman ini setelah email server siap.');
        }
        return $this->back('Undangan staf berhasil dikirim dan berlaku selama 48 jam.');
    }

    public function acceptForm(string $token)
    {
        $invite = $this->findInvite($token);
        if (!$invite) return $this->inviteView('Tautan undangan tidak valid atau sudah kedaluwarsa.', false);
        return view('pages/staffInviteAccept', ['invite' => $invite, 'error' => '', 'token' => $token]);
    }

    public function accept()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirmation = $this->request->getPost('password_confirm');
        if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) return $this->inviteView('Tautan undangan tidak valid atau sudah kedaluwarsa.', false);
        if ($error = PasswordResetService::passwordError($password, $confirmation)) {
            $invite = $this->findInvite($token);
            return $invite ? view('pages/staffInviteAccept', ['invite' => $invite, 'error' => $error, 'token' => $token])
                : $this->inviteView('Tautan undangan tidak valid atau sudah kedaluwarsa.', false);
        }

        $db = db_connect();
        if (!$db->tableExists('admin_staff_invitations')) return $this->inviteView('Fitur undangan staf belum siap.', false);
        $db->transBegin();
        try {
            $builder = $db->table('admin_staff_invitations')->where('token_hash', hash('sha256', $token))->where('status', 'pending')->where('expires_at >', gmdate('Y-m-d H:i:s'));
            $sql = $builder->getCompiledSelect();
            $invite = $db->query($sql . ($db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$invite || !$this->allowedRole((string)$invite['role_key'], $db)) throw new \DomainException('Tautan undangan tidak valid atau sudah kedaluwarsa.');
            if ($db->table('user')->where('email', $invite['email'])->countAllResults() > 0
                || ($db->tableExists('pembeli') && $db->table('pembeli')->where('email_user', $invite['email'])->countAllResults() > 0)) {
                throw new \DomainException('Email ini sudah memiliki akun. Hubungi Dev untuk meninjau akses.');
            }

            $user = ['email' => $invite['email'], 'sandi' => password_hash($password, PASSWORD_DEFAULT), 'role' => '1',
                'admin_role' => $invite['role_key'], 'active' => '1'];
            foreach (['otp' => '0', 'waktu_otp' => '0', 'auth_provider' => 'email', 'registration_method' => 'email', 'registered_at' => gmdate('Y-m-d H:i:s')] as $field => $value) {
                if ($db->fieldExists($field, 'user')) $user[$field] = $value;
            }
            if (!$db->table('user')->insert($user)) throw new \RuntimeException('Staff account insert failed');
            $accepted = $db->table('admin_staff_invitations')->where('id', $invite['id'])->where('status', 'pending')->update(['status' => 'accepted', 'accepted_at' => gmdate('Y-m-d H:i:s')]);
            if ($db->tableExists('admin_role_audit')) {
                $db->table('admin_role_audit')->insert(['actor_email' => (string)$invite['email'], 'action' => 'ACCEPT_STAFF',
                    'role_key' => (string)$invite['role_key'], 'target_email' => (string)$invite['email'],
                    'details' => json_encode(['invited_by' => (string)$invite['created_by']], JSON_UNESCAPED_UNICODE), 'created_at' => gmdate('Y-m-d H:i:s')]);
            }
            if (!$accepted || $db->transStatus() === false || !$db->transCommit()) throw new \RuntimeException('Staff acceptance failed');
        } catch (\Throwable $error) {
            $db->transRollback();
            if ($error instanceof \DomainException) return $this->inviteView($error->getMessage(), false);
            if (in_array((int)($db->error()['code'] ?? 0), [1062, 19], true)) return $this->inviteView('Email ini sudah terdaftar. Hubungi Dev untuk meninjau akses.', false);
            log_message('error', 'Staff invitation acceptance failed: {type}', ['type' => get_class($error)]);
            return $this->inviteView('Akun staf belum berhasil dibuat. Coba lagi atau hubungi Dev.', false);
        }
        session()->setFlashdata('msg', 'Akun staf berhasil dibuat. Silakan masuk dengan email dan sandi baru Anda.');
        return redirect()->to(site_url('login'));
    }

    private function findInvite(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) return null;
        $db = db_connect();
        if (!$db->tableExists('admin_staff_invitations')) return null;
        $invite = $db->table('admin_staff_invitations')->where('token_hash', hash('sha256', $token))->where('status', 'pending')
            ->where('expires_at >', gmdate('Y-m-d H:i:s'))->get()->getRowArray();
        return $invite && $this->allowedRole((string)$invite['role_key'], $db) ? $invite : null;
    }

    private function inviteView(string $message, bool $success)
    {
        return view('pages/staffInviteStatus', ['message' => $message, 'success' => $success]);
    }

    private function back(string $message)
    {
        session()->setFlashdata('msg', $message);
        return redirect()->to(site_url('admin/roles'));
    }
}
