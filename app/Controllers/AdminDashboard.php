<?php

namespace App\Controllers;

use App\Services\Auth\AdminPermissions;

final class AdminDashboard extends BaseController
{
    public function index()
    {
        $role = AdminPermissions::normalizeRole(session('admin_role') ?: 'ADMIN');
        $customPermissions = (array) session('admin_permissions');
        if ((string) session('role') !== '1' || !AdminPermissions::canAccess($role, 'admin.dashboard', $customPermissions)) {
            return $this->response->setStatusCode(403)->setBody('Akses tidak tersedia.');
        }

        $canOrders = AdminPermissions::canAccess($role, 'orders.read', $customPermissions);
        $canCustomers = AdminPermissions::canAccess($role, 'customers.read', $customPermissions);
        $canCatalog = AdminPermissions::canAccess($role, 'catalog.read', $customPermissions);
        $metrics = [];
        $db = db_connect();

        if ($canOrders && $db->tableExists('pemesanan')) {
            $metrics['pendingOrders'] = $this->safeProductionOrderCount('Menunggu Pembayaran');
            $metrics['processingOrders'] = $this->safeProductionOrderCount('Proses');
            $metrics['shippedOrders'] = $this->safeProductionOrderCount('Dikirim');
        }

        if ($canCustomers && $db->tableExists('user')) {
            try {
                $builder = $db->table('user')->where('role', '0')->where('active', '1');
                if ($db->fieldExists('admin_role', 'user')) {
                    $builder->groupStart()->where('admin_role', null)->orWhere('admin_role', '')->groupEnd();
                }
                $metrics['customers'] = (int) $builder->countAllResults();
            } catch (\Throwable $error) {
                $metrics['customers'] = null;
                log_message('error', 'Admin dashboard customer count unavailable: ' . get_class($error));
            }
        }

        if ($canCatalog && $db->tableExists('barang')) {
            try {
                $metrics['products'] = (int) $db->table('barang')->where('active', '1')->countAllResults();
                $metrics['lowStock'] = (int) $db->table('barang')->where('active', '1')->where('stok <=', 5)->countAllResults();
            } catch (\Throwable $error) {
                $metrics['products'] = $metrics['lowStock'] = null;
                log_message('error', 'Admin dashboard product count unavailable: ' . get_class($error));
            }
        }

        $shortcuts = [];
        $shortcutDefinitions = [
            ['orders.read', 'Pesanan', 'Kelola dan pantau pesanan', 'receipt_long', '/listcustomer'],
            ['customers.read', 'Pengguna', 'Cari akun pelanggan', 'group', '/customerdirectoryadmin'],
            ['catalog.read', 'Produk', 'Atur katalog dan stok', 'inventory_2', '/listproduct'],
            ['content.read', 'Promosi & konten', 'Kelola banner, artikel, dan voucher', 'campaign', '/listbanner'],
            ['finance.read', 'Invoice', 'Lihat invoice dan pembayaran', 'description', '/invoiceadmin'],
            ['settings.read', 'Traffic', 'Lihat ringkasan pengunjung', 'bar_chart', '/trafficadmin'],
        ];
        foreach ($shortcutDefinitions as [$permission, $label, $description, $icon, $url]) {
            if (AdminPermissions::canAccess($role, $permission, $customPermissions)) {
                $shortcuts[] = compact('label', 'description', 'icon', 'url');
            }
        }

        return view('pages/adminDashboard', [
            'title' => 'Dashboard',
            'roleLabel' => $this->roleLabel($role),
            'metrics' => $metrics,
            'shortcuts' => $shortcuts,
        ]);
    }

    private function safeProductionOrderCount(string $status): ?int
    {
        try {
            return model(\App\Models\PemesananModel::class)->countAdminProductionOrders(
                \App\Services\Payments\PaymentSafety::testAccountEmails(),
                $status
            );
        } catch (\Throwable $error) {
            log_message('error', 'Admin dashboard order count unavailable: ' . get_class($error));
            return null;
        }
    }

    private function roleLabel(string $role): string
    {
        $labels = [
            'DEV' => 'Developer', 'SUPER_ADMIN' => 'Super Admin', 'ADMIN' => 'Administrator',
            'ORDER_ADMIN' => 'Pesanan & pelanggan', 'CATALOG_ADMIN' => 'Katalog & konten',
            'CONTENT_ADMIN' => 'Konten & promosi', 'FINANCE_ADMIN' => 'Keuangan',
        ];
        if (isset($labels[$role])) return $labels[$role];
        if (strpos($role, 'CUSTOM_') === 0) {
            try {
                $row = db_connect()->table('admin_roles')->select('label')->where('role_key', $role)->get()->getRowArray();
                if (!empty($row['label'])) return (string) $row['label'];
            } catch (\Throwable $error) {
                // The dashboard remains usable if optional custom-role tables are absent.
            }
        }
        return 'Staf';
    }
}
