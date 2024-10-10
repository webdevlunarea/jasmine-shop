<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\GambarBarangModel;
use App\Models\PembeliModel;
use App\Models\PemesananModel;
use App\Models\UserModel;
use App\Models\FormModel;
use App\Models\ArtikelModel;
use App\Models\GambarArtikelModel;
use App\Models\SubmitEmailModel;
use App\Models\VoucherModel;
use App\Models\InvoiceModel;

class ApiSheet extends BaseController
{
    protected $barangModel;
    protected $gambarBarangModel;
    protected $userModel;
    protected $pembeliModel;
    protected $pemesananModel;
    protected $formModel;
    protected $artikelModel;
    protected $gambarArtikelModel;
    protected $submitEmailModel;
    protected $voucherModel;
    protected $invoiceModel;
    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->gambarBarangModel = new GambarBarangModel();
        $this->userModel = new UserModel();
        $this->pembeliModel = new PembeliModel();
        $this->pemesananModel = new PemesananModel();
        $this->formModel = new FormModel();
        $this->artikelModel = new ArtikelModel();
        $this->gambarArtikelModel = new GambarArtikelModel();
        $this->submitEmailModel = new SubmitEmailModel();
        $this->voucherModel = new VoucherModel();
        $this->invoiceModel = new InvoiceModel();
    }
    public function updateStok()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        return $this->response->setJSON([
            'success' => true,
            'ini body' => $body,
        ], false);
    }
}
