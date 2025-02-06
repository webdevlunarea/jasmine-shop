<?php

namespace App\Controllers;

use App\Models\GambarArtikelModel;
use App\Models\ArtikelModel;
use App\Models\BarangModel;
use App\Models\GambarUserModel;
use App\Models\VoucherModel;
use App\Models\PemesananModel;


class GambarController extends BaseController
{
    protected $artikelModel;
    protected $gambarArtikelModel;
    protected $barangModel;
    protected $voucherModel;
    protected $gambarUserModel;
    protected $pemesananModel;
    public function __construct()
    {
        $this->artikelModel = new ArtikelModel();
        $this->gambarArtikelModel = new GambarArtikelModel();
        $this->barangModel = new BarangModel();
        $this->voucherModel = new VoucherModel();
        $this->gambarUserModel = new GambarUserModel();
        $this->pemesananModel = new PemesananModel();
    }

    public function tampilGambarBarang($idBarang)
    {
        $gambar = $this->barangModel->getBarangAdmin($idBarang)['gambar'];
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $gambar;
    }

    // public function tampilGambarVarian($idBarang, $urutan)
    // {
    //     $gambar = $this->gambarBarangModel->getGambar($idBarang);
    //     $gambarSelected = $gambar['gambar' . $urutan];
    //     $this->response->setHeader('Content-Type', 'image/webp');
    //     echo $gambarSelected;
    // }

    public function tampilGambarBarangSlaah($idBarang)
    {
        // Decode URL dari Base64
        // $imageUrl = base64_decode($encodedUrl);
        // $imageUrl = 'https://ilenafurniture.com/viewpic/' . $idBarang;
        // dd($imageUrl);

        // Mendownload gambar dari URL
        // $imageContent = file_get_contents($imageUrl);
        $imageContent = $this->barangModel->getBarangAdmin($idBarang)['gambar'];
        if ($imageContent === false) {
            return $this->response->setStatusCode(404, 'Image not found.');
        }

        // Buat gambar dari string
        $image = imagecreatefromstring($imageContent);
        if ($image === false) {
            return $this->response->setStatusCode(500, 'Invalid image format.');
        }

        // Mendapatkan watermark PNG
        $watermarkPath = base_url('img/WM Black 300.png'); // Pastikan path watermark yang benar
        // dd($watermarkPath);
        // if (!file_exists($watermarkPath)) {
        //     return $this->response->setStatusCode(404, 'Watermark image not found.');
        // }
        $watermarkContent = file_get_contents($watermarkPath);
        if ($watermarkContent === false) {
            return $this->response->setStatusCode(404, 'Watermark image not found.');
        }

        // $watermark = imagecreatefrompng($watermarkPath);
        $watermark = imagecreatefromstring($watermarkContent);
        if ($watermark === false) {
            return $this->response->setStatusCode(500, 'Invalid watermark format.');
        }

        $this->response->setHeader('Content-Type', 'image/webp');
        echo $watermark;

        // Dapatkan ukuran gambar dan watermark
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        // Tentukan posisi watermark di pojok kanan bawah
        $xPos = $imageWidth - $watermarkWidth - 10; // Margin 10px dari kanan
        $yPos = $imageHeight - $watermarkHeight - 10; // Margin 10px dari bawah

        // Tambahkan watermark ke gambar
        imagecopy($image, $watermark, $xPos, $yPos, 0, 0, $watermarkWidth, $watermarkHeight);

        // Set header untuk menampilkan gambar dengan tipe konten yang sesuai
        header('Content-Type: image/jpeg');

        // Output gambar
        imagejpeg($image);

        // Bersihkan memori
        imagedestroy($image);
        imagedestroy($watermark);
    }

    public function tampilGambarArtikel($idArtikel, $urutan = false)
    {
        if ($urutan) {
            $gambar = $this->gambarArtikelModel->where(['id' => $idArtikel])->first();
            $gambarSelected = $gambar['gambar' . $urutan];
        } else {
            $artikel = $this->artikelModel->getArtikel($idArtikel);
            $gambarSelected = $artikel['header'];
        }
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $gambarSelected;
    }
    public function tampilGambarUser($email_user) //emailnya di base64
    {
        $email = base64_decode($email_user);
        $getGambarUser = $this->gambarUserModel->getGambar($email);
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $getGambarUser['gambar'];
    }
    public function voucherPoster($idVoucher)
    {
        $gambar = $this->voucherModel->getVoucher($idVoucher);
        $gambarSelected = $gambar['poster'];
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $gambarSelected;
    }
    public function voucherPosterEmail($idVoucher)
    {
        $gambar = $this->voucherModel->getVoucher($idVoucher);
        $gambarSelected = $gambar['poster_email'];
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $gambarSelected;
    }
    public function tampilGambarBuktiBayar($idMidtrans)
    {
        $pemesananCur = $this->pemesananModel->getPemesanan($idMidtrans);
        $gambarSelected = $pemesananCur['bukti_bayar'];
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $gambarSelected;
    }
}
