<?php

namespace App\Controllers;

use App\Models\GambarArtikelModel;
use App\Models\ArtikelModel;

class GambarController extends BaseController
{
    protected $artikelModel;
    protected $gambarArtikelModel;
    public function __construct()
    {
        $this->artikelModel = new ArtikelModel();
        $this->gambarArtikelModel = new GambarArtikelModel();
    }

    // public function tampilGambarBarang($idBarang)
    // {
    //     $gambar = $this->barangModel->getBarang($idBarang)['gambar'];
    //     $this->response->setHeader('Content-Type', 'image/webp');
    //     echo $gambar;
    // }

    // public function tampilGambarVarian($idBarang, $urutan)
    // {
    //     $gambar = $this->gambarBarangModel->getGambar($idBarang);
    //     $gambarSelected = $gambar['gambar' . $urutan];
    //     $this->response->setHeader('Content-Type', 'image/webp');
    //     echo $gambarSelected;
    // }

    public function tampilGambarArtikel($idArtikel, $urutan = false)
    {
        if ($urutan) {
            $gambar = $this->gambarArtikelModel->getGambar($idArtikel);
            $gambarSelected = $gambar['gambar' . $urutan];
        } else {
            $artikel = $this->artikelModel->getArtikel($idArtikel);
            $gambarSelected = $artikel['header'];
        }
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $gambarSelected;
    }
}
