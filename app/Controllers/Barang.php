<?php

namespace App\Controllers;

use App\Models\BarangModel;

class Barang extends BaseController
{
    protected $barangModel;
    public function __construct()
    {
        $this->barangModel = new BarangModel();
    }
    public function getAllBarang()
    {
        $data = [
            'title' => 'Beranda | Jual.an',
            'barang' => $this->barangModel->getBarang()
        ];
        return view('pages/home', $data);
    }
}
