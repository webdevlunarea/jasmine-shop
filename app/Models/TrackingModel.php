<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingModel extends Model
{
    protected $table = 'tracking';
    protected $allowedFields = [
        'waktu',
        'ip',
        'path',
        'durasi',
    ];

    public function getTracking($waktu = false)
    {
        if (!$waktu) {
            return $this->orderBy('waktu', 'desc')->findAll();
        }
        return $this->where(['waktu' => $waktu])->first();
    }

    protected function applyTrafficScope($builder, $days = 30)
    {
        $days = max(1, (int)$days);
        $since = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        $builder->where('waktu >=', $since);

        $adminPrefixes = [
            '/addtracking',
            '/listcustomer',
            '/invoiceadmin',
            '/addinvoiceadmin',
            '/listproduct',
            '/listproducttable',
            '/addproduct',
            '/editproduct',
            '/findproductadmin',
            '/listbanner',
            '/addbanner',
            '/editbanner',
            '/listvoucher',
            '/addvoucher',
            '/editvoucher',
            '/listredeem',
            '/manageratingterjual',
            '/stokadmin',
            '/pdf',
            '/trafficadmin',
        ];

        foreach ($adminPrefixes as $prefix) {
            $builder->notLike('path', $prefix, 'after');
        }

        return $builder;
    }

    public function getTrafficSummary($days = 30)
    {
        $builder = $this->builder();
        $this->applyTrafficScope($builder, $days);
        $summary = $builder
            ->select('COUNT(*) AS pageviews, COUNT(DISTINCT ip) AS visitors, AVG(durasi) AS avg_duration', false)
            ->get()
            ->getRowArray();

        $todayBuilder = $this->builder();
        $this->applyTrafficScope($todayBuilder, 1);
        $today = $todayBuilder->countAllResults();

        return [
            'pageviews' => (int)($summary['pageviews'] ?? 0),
            'visitors' => (int)($summary['visitors'] ?? 0),
            'avg_duration' => (float)($summary['avg_duration'] ?? 0),
            'today' => (int)$today,
        ];
    }

    public function getDailyTraffic($days = 14)
    {
        $builder = $this->builder();
        $this->applyTrafficScope($builder, $days);
        return $builder
            ->select('DATE(waktu) AS tanggal, COUNT(*) AS pageviews, COUNT(DISTINCT ip) AS visitors, AVG(durasi) AS avg_duration', false)
            ->groupBy('DATE(waktu)')
            ->orderBy('tanggal', 'asc')
            ->get()
            ->getResultArray();
    }

    public function getTopPages($days = 30, $limit = 10)
    {
        $builder = $this->builder();
        $this->applyTrafficScope($builder, $days);
        return $builder
            ->select('path, COUNT(*) AS pageviews, COUNT(DISTINCT ip) AS visitors, AVG(durasi) AS avg_duration', false)
            ->groupBy('path')
            ->orderBy('pageviews', 'desc')
            ->limit((int)$limit)
            ->get()
            ->getResultArray();
    }

    public function getRecentVisits($limit = 30)
    {
        $builder = $this->builder();
        $this->applyTrafficScope($builder, 30);
        return $builder
            ->select('waktu, ip, path, durasi')
            ->orderBy('waktu', 'desc')
            ->limit((int)$limit)
            ->get()
            ->getResultArray();
    }
}
