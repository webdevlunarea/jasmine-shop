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
        $canCustomerModeration = AdminPermissions::canAccess($role, 'customers.manage', $customPermissions);
        $canCatalog = AdminPermissions::canAccess($role, 'catalog.read', $customPermissions);
        $metrics = [];
        $db = db_connect();

        if ($canOrders && $db->tableExists('pemesanan')) {
            $metrics['pendingOrders'] = $this->safeProductionOrderCount('Menunggu Pembayaran');
            $metrics['processingOrders'] = $this->safeProductionOrderCount('Proses');
            $metrics['shippedOrders'] = $this->safeProductionOrderCount('Dikirim');
            try {
                $actionable = model(\App\Models\PemesananModel::class)->getAdminActionableOrderCounts(
                    \App\Services\Payments\PaymentSafety::testAccountEmails()
                );
                $metrics['paymentReview'] = (int)($actionable['review_transfer'] ?? 0);
            } catch (\Throwable $error) {
                $metrics['paymentReview'] = null;
                log_message('error', 'Admin dashboard transfer review count unavailable: {type}', ['type' => get_class($error)]);
            }
            if ($db->tableExists('retur_pengajuan')) {
                try {
                    $metrics['returnsPending'] = (int)$db->table('retur_pengajuan')
                        ->whereIn('status', ['Menunggu Review Admin', 'Menunggu Sinkron Sistem'])->countAllResults();
                } catch (\Throwable $error) {
                    $metrics['returnsPending'] = null;
                    log_message('error', 'Admin dashboard return count unavailable: {type}', ['type' => get_class($error)]);
                }
            }
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

        if ($canCustomerModeration && $db->tableExists('customer_account_deletion_requests')) {
            try {
                $metrics['deletionRequests'] = (int)$db->table('customer_account_deletion_requests')
                    ->where('status', 'pending')->countAllResults();
            } catch (\Throwable $error) {
                $metrics['deletionRequests'] = null;
                log_message('error', 'Admin dashboard deletion request count unavailable: {type}', ['type' => get_class($error)]);
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
            ['orders.read', 'Pesanan', 'Lihat dan proses pesanan', 'receipt_long', '/listcustomer'],
            ['customers.read', 'Pelanggan', 'Cari akun pelanggan', 'group', '/customerdirectoryadmin'],
            ['catalog.read', 'Produk', 'Atur produk dan persediaan', 'inventory_2', '/listproduct'],
            ['content.read', 'Promosi', 'Atur banner, artikel, dan voucher', 'campaign', '/listbanner'],
            ['finance.read', 'Tagihan', 'Periksa tagihan dan pembayaran', 'description', '/invoiceadmin'],
            ['settings.read', 'Pengunjung', 'Lihat jumlah pengunjung toko', 'bar_chart', '/trafficadmin'],
        ];
        foreach ($shortcutDefinitions as [$permission, $label, $description, $icon, $url]) {
            if (AdminPermissions::canAccess($role, $permission, $customPermissions)) {
                $shortcuts[] = compact('label', 'description', 'icon', 'url');
            }
        }
        if (AdminPermissions::canAccess($role, 'settings.audit.read', $customPermissions)) {
            $shortcuts[] = ['label' => 'Log aktivitas', 'description' => 'Lihat riwayat tindakan staf', 'icon' => 'history', 'url' => '/admin/activity-logs'];
        }

        $attentionItems = [];
        if (isset($metrics['pendingOrders']) && $metrics['pendingOrders'] > 0) {
            $attentionItems[] = ['Pesanan menunggu pembayaran', $metrics['pendingOrders'], 'receipt_long', '/listcustomer'];
        }
        if (isset($metrics['paymentReview']) && $metrics['paymentReview'] > 0) {
            $attentionItems[] = ['Bukti transfer perlu ditinjau', $metrics['paymentReview'], 'payments', '/listcustomer'];
        }
        if (isset($metrics['returnsPending']) && $metrics['returnsPending'] > 0) {
            $attentionItems[] = ['Pengajuan retur perlu ditinjau', $metrics['returnsPending'], 'assignment_return', '/admin/returns'];
        }
        if (isset($metrics['deletionRequests']) && $metrics['deletionRequests'] > 0) {
            $attentionItems[] = ['Permintaan hapus akun baru', $metrics['deletionRequests'], 'manage_accounts', '/customeradmin'];
        }
        if (isset($metrics['lowStock']) && $metrics['lowStock'] > 0) {
            $attentionItems[] = ['Produk dengan stok menipis', $metrics['lowStock'], 'production_quantity_limits', '/stokadmin/all/1'];
        }

        return view('pages/adminDashboard', [
            'title' => 'Dashboard',
            'roleLabel' => $this->roleLabel($role),
            'metrics' => $metrics,
            'shortcuts' => $shortcuts,
            'attentionItems' => $attentionItems,
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
