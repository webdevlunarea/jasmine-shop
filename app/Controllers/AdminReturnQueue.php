<?php

namespace App\Controllers;

use App\Services\Auth\AdminPermissions;

final class AdminReturnQueue extends BaseController
{
    private const PER_PAGE = 50;
    private const OPEN_STATUSES = ['Menunggu Review Admin', 'Menunggu Sinkron Sistem'];

    public function index()
    {
        $role = AdminPermissions::normalizeRole(session('admin_role') ?: 'ADMIN');
        $permissions = (array)session('admin_permissions');
        if ((string)session('role') !== '1' || !AdminPermissions::canAccess($role, 'orders.read', $permissions)) {
            return $this->response->setStatusCode(403)->setBody('Akses tidak tersedia.');
        }

        $db = db_connect();
        $ready = $db->tableExists('retur_pengajuan');
        $status = $this->request->getGet('status');
        $status = is_string($status) && in_array($status, self::OPEN_STATUSES, true) ? $status : '';
        $pageInput = $this->request->getGet('page');
        $page = max(1, (int)(is_string($pageInput) || is_numeric($pageInput) ? $pageInput : 1));
        $rows = [];
        $total = 0;
        $error = '';

        if ($ready) {
            try {
                $required = ['id', 'id_midtrans', 'email_cus', 'items', 'alasan', 'solusi', 'status', 'created_at'];
                if (array_diff($required, $db->getFieldNames('retur_pengajuan'))) {
                    $ready = false;
                    $error = 'Struktur data retur belum lengkap.';
                } else {
                    $count = $db->table('retur_pengajuan')->whereIn('status', self::OPEN_STATUSES);
                    if ($status !== '') $count->where('status', $status);
                    $total = (int)$count->countAllResults();
                    $page = min($page, max(1, (int)ceil($total / self::PER_PAGE)));
                    $query = $db->table('retur_pengajuan')
                        ->select('id, id_midtrans, email_cus, items, alasan, solusi, status, created_at')
                        ->whereIn('status', self::OPEN_STATUSES);
                    if ($status !== '') $query->where('status', $status);
                    $rows = $query->orderBy('created_at', 'ASC')->orderBy('id', 'ASC')
                        ->get(self::PER_PAGE, ($page - 1) * self::PER_PAGE)->getResultArray();
                }
            } catch (\Throwable $exception) {
                log_message('error', 'Admin return queue could not load: {type}', ['type' => get_class($exception)]);
                $error = 'Daftar retur belum dapat dimuat. Coba lagi nanti.';
            }
        }

        return view('pages/adminReturnQueue', [
            'title' => 'Retur pelanggan', 'ready' => $ready, 'error' => $error,
            'returns' => $rows, 'status' => $status, 'page' => $page,
            'pages' => max(1, (int)ceil($total / self::PER_PAGE)), 'total' => $total,
        ]);
    }
}
