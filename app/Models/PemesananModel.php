<?php

namespace App\Models;

use CodeIgniter\Model;

class PemesananModel extends Model
{
    protected $table = 'pemesanan';
    protected $allowedFields = [
        'email_cus',
        'nama_pen',
        'hp_pen',
        'alamat_pen',
        'resi',
        'id_midtrans',
        'items',
        'status',
        'kurir',
        'data_mid',
        'note',
        'diskonVoucher',
        'idVoucher',
        'cashback',
        'pakai_poin',
        'bukti_bayar'
    ];

    public function getPemesanan($id_midtrans = false)
    {
        if (!$id_midtrans) {
            return $this->orderBy('id', 'desc')->findAll();
        }
        return $this->where(['id_midtrans' => $id_midtrans])->first();
    }
    public function getPemesananCus($emailCus)
    {
        return $this->where(['email_cus' => $emailCus])->orderBy('id', 'desc')->findAll();
    }
    public function findForPublicTracking($keyword)
    {
        $keyword = trim((string)$keyword);
        if ($keyword === '') return null;

        return $this->groupStart()
            ->where('id_midtrans', $keyword)
            ->orWhere('resi', $keyword)
            ->groupEnd()
            ->orderBy('id', 'desc')
            ->first();
    }
    public function getPemesananPage($page)
    {
        // $hitungPag = floor($page / 20);
        $hitungPag = 20 * ($page - 1);
        if ($page > 1) {
            return $this->orderBy('id', 'desc')->findAll(20, $hitungPag);
        } else {
            return $this->orderBy('id', 'desc')->findAll(20, 0);
        }
    }

    private function adminProductionOrdersBuilder()
    {
        $builder = $this->builder();
        $jsonEnvironment = "JSON_EXTRACT(data_mid, '$._app_payment_environment')";
        $environment = $this->db->DBDriver === 'SQLite3'
            ? $jsonEnvironment
            : "JSON_UNQUOTE({$jsonEnvironment})";
        return $builder->where($environment, 'production');
    }

    public function countAdminProductionOrders(?string $status = null): int
    {
        $builder = $this->adminProductionOrdersBuilder();
        if ($status !== null) $builder->where('status', $status);
        return (int)$builder->countAllResults();
    }

    private function adminVisibleOrdersBuilder()
    {
        $jsonEnvironment = "JSON_EXTRACT(data_mid, '$._app_payment_environment')";
        $environment = $this->db->DBDriver === 'SQLite3'
            ? $jsonEnvironment
            : "JSON_UNQUOTE({$jsonEnvironment})";
        return $this->builder()->where("COALESCE({$environment}, '') !=", 'sandbox');
    }

    /** Show production and unmarked legacy history; explicit sandbox orders stay hidden. */
    public function countAdminOrders(?string $status = null): int
    {
        $builder = $this->adminVisibleOrdersBuilder();
        if ($status !== null) $builder->where('status', $status);
        return (int)$builder->countAllResults();
    }

    /** @return array<int, array<string, mixed>> */
    public function getAdminOrdersPage(?string $status, int $limit, int $offset): array
    {
        $builder = $this->adminVisibleOrdersBuilder();
        if ($status !== null) $builder->where('status', $status);
        return $builder->orderBy('id', 'desc')->get($limit, max(0, $offset))->getResultArray();
    }

    public function getAdminProductionOrdersPage(?string $status, int $limit, int $offset): array
    {
        $builder = $this->adminProductionOrdersBuilder();
        if ($status !== null) $builder->where('status', $status);
        return $builder->orderBy('id', 'desc')->get($limit, max(0, $offset))->getResultArray();
    }

    public function getAdminActionableOrderCounts(): array
    {
        $builder = $this->adminProductionOrdersBuilder();
        $row = $builder->select("SUM(CASE WHEN status = 'Menunggu Pembayaran' THEN 1 ELSE 0 END) AS waiting_payment", false)
            ->select("SUM(CASE WHEN status = 'Menunggu Pembayaran Rekening' AND bukti_bayar IS NOT NULL AND bukti_bayar <> '' THEN 1 ELSE 0 END) AS review_transfer", false)
            ->select("SUM(CASE WHEN status = 'Proses' THEN 1 ELSE 0 END) AS ready_to_process", false)
            ->get()->getRowArray() ?? [];
        return [
            'waiting_payment' => (int)($row['waiting_payment'] ?? 0),
            'review_transfer' => (int)($row['review_transfer'] ?? 0),
            'ready_to_process' => (int)($row['ready_to_process'] ?? 0),
        ];
    }

    //pemesanan menunggu pembayaran
    public function getPemesananPending($email_cus)
    {
        $statusPending = ['Menunggu Pembayaran', 'Menunggu Pembayaran Rekening'];
        return $this
            ->where('email_cus', $email_cus)
            ->whereIn('status', $statusPending)
            ->first();
    }
}
