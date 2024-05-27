<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\GambarBarangModel;

class CopyGambarController extends BaseController
{
    protected $barangModel;
    protected $gambarBarangModel;
    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->gambarBarangModel = new GambarBarangModel();
    }

    public function copyGambar()
    {
        $produk = $this->barangModel->getBarang();
        foreach ($produk as $p) {
            $varian = json_decode($p['varian'], true);
            $jml_varian = $p['jml_varian'];
            $hitungGambar = count($varian) - 1 + (int)$jml_varian;
            $insertGambarBarang = [
                'id' => $p['id']
            ];
            for ($i = 1; $i <= $hitungGambar; $i++) {
                $insertGambarBarang["gambar" . $i] = file_get_contents("https://jasminefurniture.store/apicomp/getgambar/" . $p['id'] . "/" . $i);
            }
            $this->gambarBarangModel->insert($insertGambarBarang);
        }
        dd([
            'hasil' => 'berhasil'
        ]);
    }
}
