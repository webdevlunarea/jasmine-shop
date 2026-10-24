<?php

namespace App\Controllers;

use App\Services\Auth\AdminPermissions;

final class AdminRoleAdmin extends BaseController
{
    private function isDev(): bool
    {
        return (string) session('role') === '1' && strtoupper((string) session('admin_role')) === 'DEV';
    }

    public function index()
    {
        if (!$this->isDev()) return $this->response->setStatusCode(403)->setBody('Akses hanya untuk DEV.');
        $db = db_connect();
        $ready = $db->tableExists('admin_roles') && $db->tableExists('admin_role_permissions')
            && $db->tableExists('admin_role_audit') && $db->fieldExists('admin_role', 'user');
        $roles = $ready ? $db->table('admin_roles')->orderBy('label')->get()->getResultArray() : [];
        $admins = $ready ? $db->table('user')->select('email, admin_role')->where('role', '1')->orderBy('email')->limit(500)->get()->getResultArray() : [];
        $roleLogs = $ready ? $db->table('admin_role_audit')->orderBy('created_at', 'DESC')->limit(50)->get()->getResultArray() : [];
        $selected = null;
        $selectedPermissions = [];
        $editKey = strtoupper(trim((string) $this->request->getGet('edit')));
        if ($ready && preg_match('/^CUSTOM_[A-Z0-9_]{1,24}$/', $editKey)) {
            $selected = $db->table('admin_roles')->where('role_key', $editKey)->get()->getRowArray();
            $selectedPermissions = $selected ? AdminPermissions::loadCustomPermissions($editKey) : [];
        }

        return view('pages/adminRoleAdmin', [
            'title' => 'Peran & akses', 'ready' => $ready, 'roles' => $roles, 'admins' => $admins,
            'systemRoles' => AdminPermissions::assignableRoles(), 'permissionCatalog' => AdminPermissions::editablePermissions(),
            'selectedRole' => $selected, 'selectedPermissions' => $selectedPermissions,
            'roleLogs' => $roleLogs,
            'msg' => session()->getFlashdata('msg'),
        ]);
    }

    public function saveRole()
    {
        if (!$this->isDev()) return $this->response->setStatusCode(403)->setBody('Akses hanya untuk DEV.');
        $db = db_connect();
        if (!$db->tableExists('admin_roles') || !$db->tableExists('admin_role_permissions') || !$db->tableExists('admin_role_audit')) {
            return $this->back('Tabel RBAC belum tersedia. Jalankan SQL setup RBAC terlebih dahulu.');
        }

        $labelInput = $this->request->getPost('label');
        $descriptionInput = $this->request->getPost('description');
        $roleKeyInput = $this->request->getPost('role_key');
        $permissions = $this->request->getPost('permissions');
        if (!is_string($labelInput) || !is_string($descriptionInput) || (!is_string($roleKeyInput) && $roleKeyInput !== null)) {
            return $this->back('Isian peran tidak valid.');
        }
        $label = trim($labelInput);
        $description = trim($descriptionInput);
        $existingKey = strtoupper(trim((string) $roleKeyInput));
        if ($label === '' || mb_strlen($label) > 60 || mb_strlen($description) > 255 || !is_array($permissions)) {
            return $this->back('Nama, keterangan, atau pilihan izin belum valid.');
        }
        $allowed = array_keys(AdminPermissions::editablePermissions());
        $permissions = array_values(array_unique(array_intersect($allowed, array_map(static fn($permission) => is_string($permission) ? $permission : '', $permissions))));
        if (!$permissions) return $this->back('Pilih setidaknya satu izin untuk peran ini.');

        if ($existingKey !== '') {
            if (!preg_match('/^CUSTOM_[A-Z0-9_]{1,24}$/', $existingKey) || !$db->table('admin_roles')->where('role_key', $existingKey)->countAllResults()) {
                return $this->back('Peran kustom yang akan diubah tidak ditemukan.');
            }
            $roleKey = $existingKey;
        } else {
            $slug = strtoupper(trim((string) preg_replace('/[^A-Z0-9]+/i', '_', $label), '_'));
            $roleKey = 'CUSTOM_' . substr($slug ?: 'ROLE', 0, 24);
            if ($db->table('admin_roles')->where('role_key', $roleKey)->countAllResults()) {
                return $this->back('Nama peran tersebut sudah dipakai. Ubah nama perannya agar unik.');
            }
        }

        $now = date('Y-m-d H:i:s');
        $db->transBegin();
        try {
            if ($existingKey === '') {
                $db->table('admin_roles')->insert([
                    'role_key' => $roleKey, 'label' => $label, 'description' => $description,
                    'created_by' => (string) session('email'), 'created_at' => $now, 'updated_at' => $now,
                ]);
            } else {
                $db->table('admin_roles')->where('role_key', $roleKey)->update([
                    'label' => $label, 'description' => $description, 'updated_at' => $now,
                ]);
                $db->table('admin_role_permissions')->where('role_key', $roleKey)->delete();
            }
            foreach ($permissions as $permission) {
                $db->table('admin_role_permissions')->insert(['role_key' => $roleKey, 'permission_key' => $permission]);
            }
            $db->table('admin_role_audit')->insert([
                'actor_email' => (string) session('email'), 'action' => $existingKey === '' ? 'CREATE_ROLE' : 'UPDATE_ROLE',
                'role_key' => $roleKey, 'target_email' => null,
                'details' => json_encode(['label' => $label, 'permissions' => $permissions], JSON_UNESCAPED_UNICODE), 'created_at' => $now,
            ]);
            if ($db->transStatus() === false) throw new \RuntimeException('RBAC transaction failed');
            $db->transCommit();
        } catch (\Throwable $error) {
            $db->transRollback();
            log_message('error', 'Admin custom role save failed: {type}', ['type' => get_class($error)]);
            return $this->back('Peran belum tersimpan. Periksa kesiapan tabel RBAC lalu coba lagi.');
        }

        return $this->back($existingKey === '' ? 'Peran baru berhasil dibuat.' : 'Izin peran berhasil diperbarui. Admin terkait perlu login ulang agar izin baru aktif.');
    }

    public function assignRole()
    {
        if (!$this->isDev()) return $this->response->setStatusCode(403)->setBody('Akses hanya untuk DEV.');
        $db = db_connect();
        if (!$db->tableExists('admin_roles') || !$db->tableExists('admin_role_audit') || !$db->fieldExists('admin_role', 'user')) {
            return $this->back('Tabel RBAC belum tersedia. Jalankan SQL setup RBAC terlebih dahulu.');
        }
        $emailInput = $this->request->getPost('email');
        $roleInput = $this->request->getPost('admin_role');
        if (!is_string($emailInput) || !is_string($roleInput)) return $this->back('Pilih akun dan peran yang valid.');
        $email = trim($emailInput);
        $roleKey = strtoupper(trim($roleInput));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->back('Pilih akun admin yang valid.');
        if (strtolower($email) === strtolower((string) session('email'))) return $this->back('Akun DEV yang sedang digunakan tidak dapat mengubah perannya sendiri.');
        $isAssignableBuiltIn = array_key_exists($roleKey, AdminPermissions::assignableRoles());
        $isExistingCustom = preg_match('/^CUSTOM_[A-Z0-9_]{1,24}$/', $roleKey)
            && $db->table('admin_roles')->where('role_key', $roleKey)->countAllResults();
        if (!$isAssignableBuiltIn && !$isExistingCustom) {
            return $this->back('Peran tujuan tidak valid.');
        }
        $admin = $db->table('user')->select('email, admin_role')->where(['email' => $email, 'role' => '1'])->get()->getRowArray();
        if (!$admin) return $this->back('Akun admin tidak ditemukan.');

        $oldRole = (string) ($admin['admin_role'] ?? 'ADMIN');
        $now = date('Y-m-d H:i:s');
        $db->transBegin();
        try {
            $db->table('user')->where(['email' => $email, 'role' => '1'])->update(['admin_role' => $roleKey]);
            $db->table('admin_role_audit')->insert([
                'actor_email' => (string) session('email'), 'action' => 'ASSIGN_ROLE', 'role_key' => $roleKey,
                'target_email' => $email, 'details' => json_encode(['from' => $oldRole, 'to' => $roleKey], JSON_UNESCAPED_UNICODE), 'created_at' => $now,
            ]);
            if ($db->transStatus() === false) throw new \RuntimeException('RBAC transaction failed');
            $db->transCommit();
        } catch (\Throwable $error) {
            $db->transRollback();
            log_message('error', 'Admin role assignment failed: {type}', ['type' => get_class($error)]);
            return $this->back('Peran admin belum berubah. Coba lagi setelah memeriksa tabel RBAC.');
        }
        return $this->back('Peran admin diperbarui. Admin tersebut perlu login ulang agar perubahan berlaku.');
    }

    private function back(string $message)
    {
        session()->setFlashdata('msg', $message);
        return redirect()->to(site_url('admin/roles'));
    }
}
