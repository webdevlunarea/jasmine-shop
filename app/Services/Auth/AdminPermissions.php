<?php

namespace App\Services\Auth;

final class AdminPermissions
{
    private const ALL = ['orders', 'customers', 'catalog', 'content', 'finance', 'shipping', 'settings'];

    private const ROLE_CATEGORIES = [
        'DEV' => self::ALL,
        'SUPER_ADMIN' => self::ALL,
        'ADMIN' => self::ALL,
        'ORDER_ADMIN' => ['orders', 'customers'],
        'CATALOG_ADMIN' => ['catalog', 'content'],
        'CONTENT_ADMIN' => ['content'],
        'FINANCE_ADMIN' => ['orders', 'finance'],
    ];

    private const EDITABLE_PERMISSIONS = [
        'orders.read' => 'Lihat pesanan', 'orders.write' => 'Ubah pesanan / resi',
        'customers.read' => 'Lihat data pengguna', 'customers.write' => 'Kelola dukungan pengguna', 'customers.manage' => 'Moderasi dan suspend akun',
        'catalog.read' => 'Lihat katalog', 'catalog.write' => 'Ubah produk dan stok',
        'content.read' => 'Lihat konten dan promosi', 'content.write' => 'Ubah konten dan promosi', 'content.manage' => 'Ubah data rating/terjual khusus',
        'finance.read' => 'Lihat invoice dan pembayaran', 'finance.write' => 'Kelola invoice dan konfirmasi pembayaran',
        'shipping.read' => 'Lihat pengaturan pengiriman', 'shipping.write' => 'Ubah tarif dan subsidi pengiriman',
        'settings.read' => 'Lihat traffic dan pengaturan', 'settings.write' => 'Ubah pengaturan, referral, dan notifikasi', 'settings.manage' => 'Kirim broadcast dan promosi massal',
        'settings.audit.read' => 'Lihat riwayat aktivitas staf',
    ];

    public static function normalizeRole($role): string
    {
        $role = strtoupper(trim((string) $role));
        if ($role === '') return 'ADMIN'; // Backward-compatible role for existing admin sessions.
        if (array_key_exists($role, self::ROLE_CATEGORIES)) return $role;
        return preg_match('/^CUSTOM_[A-Z0-9_]{1,24}$/', $role) ? $role : 'INVALID';
    }

    public static function canAccess($role, string $permission, array $customPermissions = []): bool
    {
        $role = self::normalizeRole($role);
        if ($permission === 'admin.dashboard') return $role !== 'INVALID';
        if ($role === 'DEV') return true;
        if ($role === 'SUPER_ADMIN') return $permission !== 'admin.roles.manage';
        if (strpos($role, 'CUSTOM_') === 0) {
            if (!array_key_exists($permission, self::EDITABLE_PERMISSIONS)) return false;
            if (in_array($permission, $customPermissions, true)) return true;
            [$category, $action] = array_pad(explode('.', $permission, 2), 2, '');
            return $action === 'read' && in_array($category . '.write', $customPermissions, true);
        }
        if (!array_key_exists($role, self::ROLE_CATEGORIES)) return false;
        if ($permission === 'settings.audit.read') return in_array('settings', self::ROLE_CATEGORIES[$role], true);

        [$category, $action] = array_pad(explode('.', $permission, 2), 2, 'manage');
        if ($permission === 'system.maintenance' || $permission === 'admin.manage') return false;
        if (!in_array($category, self::ROLE_CATEGORIES[$role], true)) return false;

        if ($role === 'CONTENT_ADMIN' && $category === 'content') return in_array($action, ['read', 'write'], true);
        if ($role === 'ORDER_ADMIN' && $category === 'customers') return in_array($action, ['read', 'write'], true);
        if ($role === 'FINANCE_ADMIN' && $category === 'orders') return $action === 'read';
        return in_array($action, ['read', 'write', 'manage'], true);
    }

    public static function editablePermissions(): array
    {
        return self::EDITABLE_PERMISSIONS;
    }

    public static function assignableRoles(): array
    {
        return [
            'ADMIN' => 'Administrator operasional',
            'ORDER_ADMIN' => 'Pesanan & pelanggan',
            'CATALOG_ADMIN' => 'Katalog & konten',
            'CONTENT_ADMIN' => 'Konten & promosi',
            'FINANCE_ADMIN' => 'Keuangan',
        ];
    }

    public static function loadCustomPermissions(string $role): array
    {
        if (strpos($role, 'CUSTOM_') !== 0) return [];
        try {
            $db = db_connect();
            if (!$db->tableExists('admin_role_permissions')) return [];
            $rows = $db->table('admin_role_permissions')->select('permission_key')->where('role_key', $role)->get()->getResultArray();
            $allowed = array_keys(self::EDITABLE_PERMISSIONS);
            return array_values(array_intersect($allowed, array_column($rows, 'permission_key')));
        } catch (\Throwable $e) {
            log_message('error', 'Admin role permissions could not be loaded.');
            return [];
        }
    }

    public static function requiredPermission(string $path, string $method): string
    {
        $path = '/' . trim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
        $method = strtoupper($method);
        $rules = [
            'admin.dashboard' => ['/admin/dashboard'],
            'system.maintenance' => ['/benerinstokluna'],
            'admin.roles.manage' => ['/admin/roles'],
            'finance' => ['/invoiceadmin', '/addinvoiceadmin', '/actionaddinvoiceadmin', '/payorder', '/pdf'],
            'orders' => ['/listcustomer', '/orderdone', '/editresi', '/syncorder', '/admin/returns'],
            'customers' => ['/customerdirectoryadmin', '/customeradmin', '/customersuspensionadmin', '/listform', '/customeradmin/'],
            'catalog' => ['/listproduct', '/addproduct', '/editproduct', '/delproduct', '/activeproduct', '/findproductadmin', '/listproducttable', '/stokadmin', '/stokadminacc', '/lunamappingadmin', '/categoryimagesadmin'],
            'content' => ['/listbanner', '/addbanner', '/editbanner', '/actionaddbanner', '/actioneditbanner', '/activebanner', '/deletebanner', '/listvoucher', '/addvoucher', '/editvoucher', '/actionaddvoucher', '/actioneditvoucher', '/activevoucher', '/deletevoucher', '/actionbroadcastvoucher', '/actionbroadcastvoucheremail', '/voucher/addmember', '/listredeem', '/redeemcode', '/flashsaleadmin', '/addarticle', '/editarticle', '/delarticle', '/delkomen', '/editkomen', '/manageratingterjual', '/addrating', '/delrating', '/updateterjualadmin', '/editratingadmin', '/generatedummyratingterjual', '/freshgeneratedummyratingterjual', '/hapusdummyrating'],
            'shipping' => ['/shippingadmin'],
            'settings' => ['/trafficadmin', '/settingwarna', '/androidreferraladmin', '/notificationtestadmin', '/lunamappingadmin'],
            'settings.audit' => ['/admin/activity-logs'],
        ];

        foreach ($rules as $category => $prefixes) {
            foreach ($prefixes as $prefix) {
                if ($path === $prefix || strpos($path, $prefix . '/') === 0 || (substr($prefix, -1) === '/' && strpos($path, $prefix) === 0)) {
                    if ($category === 'admin.dashboard') return $category;
                    if ($category === 'system.maintenance' || $category === 'admin.roles.manage') return $category;
                    if (in_array($path, ['/actionbroadcastvoucher', '/actionbroadcastvoucheremail'], true)
                        || strpos($path, '/actionbroadcastvoucher/') === 0) return 'settings.manage';
                    if (in_array($path, ['/updateterjualadmin', '/generatedummyratingterjual', '/freshgeneratedummyratingterjual', '/hapusdummyrating'], true)
                        || strpos($path, '/updateterjualadmin/') === 0) return 'content.manage';
                    if ($category === 'settings.audit') return 'settings.audit.read';
                    if (in_array($path, ['/customeradmin', '/customersuspensionadmin'], true) || strpos($path, '/customeradmin/') === 0) return 'customers.manage';
                    $isFormRoute = (bool) preg_match('#^/(add|edit)(product|banner|voucher|article|invoiceadmin)(/|$)#', $path);
                    $action = $method === 'GET' && !$isFormRoute ? 'read' : 'write';
                    return $category . '.' . $action;
                }
            }
        }

        return 'admin.manage';
    }
}
