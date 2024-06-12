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
        $d = strtotime("+7 Hours");
        $tanggal = date("Y-m-d H:i:s", $d);
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';

        $this->trackingModel->insert([
            'waktu' => $tanggal,
            'ip' => $ipaddress,
            'path' => $body['path'],
            'durasi' => $body['durasi'],
        ]);

        $arr = [
            'success' => true,
            'waktu' => $tanggal,
            'ip' => $ipaddress,
            'path' => $body['path'],
            'durasi' => $body['durasi'],
        ];
        return $this->response->setJSON($arr, false);
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
