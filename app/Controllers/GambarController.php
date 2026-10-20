<?php

namespace App\Controllers;

use App\Models\GambarArtikelModel;
use App\Models\ArtikelModel;
use App\Models\BarangModel;
use App\Models\GambarUserModel;
use App\Models\VoucherModel;
use App\Models\PemesananModel;
use App\Models\BannerModel;
use App\Models\GambarBarangModel;


class GambarController extends BaseController
{
    protected $artikelModel;
    protected $gambarArtikelModel;
    protected $barangModel;
    protected $voucherModel;
    protected $gambarUserModel;
    protected $pemesananModel;
    protected $bannerModel;
    protected $gambarBarangModel;
    public function __construct()
    {
        $this->artikelModel = new ArtikelModel();
        $this->gambarArtikelModel = new GambarArtikelModel();
        $this->barangModel = new BarangModel();
        $this->voucherModel = new VoucherModel();
        $this->gambarUserModel = new GambarUserModel();
        $this->pemesananModel = new PemesananModel();
        $this->bannerModel = new BannerModel();
        $this->gambarBarangModel = new GambarBarangModel();
    }

    public function tampilGambarBarang($idBarang)
    {
        $product = $this->barangModel->select('gambar')->where('id', $idBarang)->first();
        return \App\Services\PublicCatalogImage::respond(
            $this->response, $product['gambar'] ?? null, $this->request->getHeaderLine('If-None-Match')
        );
    }

    public function tampilFotoProduk($idBarang, $slot)
    {
        if (!ctype_digit((string)$slot) || (int)$slot < 1 || (int)$slot > 50) {
            return $this->response->setStatusCode(404)->setHeader('Cache-Control', 'no-store')->setBody('');
        }
        $key = 'gambar'.(int)$slot;
        $row = $this->gambarBarangModel->select($key)->where('id', $idBarang)->first();
        $bytes = $row[$key] ?? null;
        if (!$bytes && (int)$slot === 1) $bytes = $this->barangModel->select('gambar')->where('id', $idBarang)->first()['gambar'] ?? null;
        return \App\Services\PublicCatalogImage::respond($this->response, $bytes, $this->request->getHeaderLine('If-None-Match'));
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
        $encoded = strtr((string)$email_user, '-_', '+/');
        $email = base64_decode($encoded, true);
        if (!is_string($email) || $email === '') return $this->response->setStatusCode(404);
        $getGambarUser = $this->gambarUserModel->getGambar($email);
        $bytes = $getGambarUser['gambar'] ?? null;
        if (!is_string($bytes) || $bytes === '') return $this->response->setStatusCode(404);
        $image = @getimagesizefromstring($bytes);
        $mime = is_array($image) && in_array($image['mime'] ?? '', ['image/jpeg', 'image/png', 'image/webp'], true)
            ? $image['mime']
            : 'application/octet-stream';
        $revision = hash('sha256', $bytes);
        $etag = '"' . $revision . '"';
        $requestedRevision = (string)$this->request->getGet('v');
        $isVersioned = preg_match('/^[a-f0-9]{24}$/i', $requestedRevision)
            && hash_equals(substr($revision, 0, 24), strtolower($requestedRevision));
        $cacheControl = $isVersioned ? 'private, max-age=31536000, immutable' : 'private, max-age=300';
        if (trim($this->request->getHeaderLine('If-None-Match')) === $etag) {
            return $this->response->setStatusCode(304)
                ->setHeader('ETag', $etag)
                ->setHeader('Cache-Control', $cacheControl)
                ->setBody('');
        }
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', $cacheControl)
            ->setHeader('ETag', $etag)
            ->setBody($bytes);
    }
    public function voucherPoster($idVoucher)
    {
        $gambar = $this->voucherModel->select('poster')->where(['id' => $idVoucher])->first();
        $bytes = $gambar['poster'] ?? null;
        $image = is_string($bytes) && $bytes !== '' ? @getimagesizefromstring($bytes) : false;
        $mime = $image['mime'] ?? '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/avif'], true)) {
            return $this->response->setStatusCode(404)->setHeader('Cache-Control', 'no-store')->setBody('');
        }
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', 'public, max-age=300, must-revalidate')
            ->setHeader('ETag', '"' . hash('sha256', $bytes) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($bytes);
    }
    public function voucherPosterEmail($idVoucher)
    {
        $gambar = $this->voucherModel->where(['id' => $idVoucher])->first();
        $gambarSelected = $gambar['poster_email'];
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $gambarSelected;
    }
    public function tampilGambarBanner($idBanner)
    {
        $banner = $this->bannerModel->select('gambar')->where('id', $idBanner)->first();
        return \App\Services\PublicCatalogImage::respond(
            $this->response, $banner['gambar'] ?? null, $this->request->getHeaderLine('If-None-Match')
        );
    }
    public function tampilGambarBuktiBayar($idMidtrans)
    {
        $pemesananCur = $this->pemesananModel->getPemesanan($idMidtrans);
        $gambarSelected = $pemesananCur['bukti_bayar'];
        $this->response->setHeader('Content-Type', 'image/webp');
        echo $gambarSelected;
    }
}
