<?php

namespace App\Controllers;

use App\Models\TrackingModel;
use App\Models\BarangModel;

class TrackingController extends BaseController
{
    protected $trackingModel;
    protected $barangModel;
    public function __construct()
    {
        $this->trackingModel = new TrackingModel();
        $this->barangModel = new BarangModel();
    }
    public function addTracking()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        if (!is_array($body)) {
            $body = [];
        }

        $path = isset($body['path']) ? trim((string)$body['path']) : '/';
        if ($path === '') {
            $path = '/';
        }
        $path = substr($path, 0, 255);

        $durasi = isset($body['durasi']) ? (float)$body['durasi'] : 0;
        if ($durasi < 0) {
            $durasi = 0;
        }
        if ($durasi > 86400) {
            $durasi = 86400;
        }

        if ($this->isIgnoredPath($path)) {
            return $this->response->setJSON([
                'success' => true,
                'skipped' => true,
            ], false);
        }

        $d = strtotime("+7 Hours");
        $tanggal = date("Y-m-d H:i:s", $d);
        $ipaddress = $this->getClientIp();

        $this->trackingModel->insert([
            'waktu' => $tanggal,
            'ip' => $ipaddress,
            'path' => $path,
            'durasi' => $durasi,
        ]);

        $arr = [
            'success' => true,
            'waktu' => $tanggal,
            'ip' => $ipaddress,
            'path' => $path,
            'durasi' => $durasi,
        ];
        return $this->response->setJSON($arr, false);
    }

    protected function getClientIp()
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $value = explode(',', $_SERVER[$key])[0];
                return substr(trim($value), 0, 45);
            }
        }

        return 'UNKNOWN';
    }

    protected function isIgnoredPath($path)
    {
        $prefixes = [
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

        foreach ($prefixes as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    public function trackPop()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $produk = $this->barangModel->getBarang($body['id']);
        if ($produk) {
            $this->barangModel->where(['id' => $body['id']])->set([
                'tracking_pop' => (int)$produk['tracking_pop'] + 1
            ])->update();
            $arr = [
                'success' => true,
                'pesan' => 'Barang berhasil di update'
            ];
        } else {
            $arr = [
                'success' => false,
                'pesan' => 'Barang tidak ditemukan'
            ];
        }
        return $this->response->setJSON($arr, false);
    }
}
