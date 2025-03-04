<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\GambarBarangModel;
use App\Models\GambarUserModel;
use App\Models\PembeliModel;
use App\Models\PemesananModel;
use App\Models\UserModel;
use App\Models\FormModel;
use App\Models\ArtikelModel;
use App\Models\GambarArtikelModel;
use App\Models\SubmitEmailModel;
use App\Models\VoucherModel;
use App\Models\InvoiceModel;
use App\Models\PreorderBarangModel;
use App\Models\PointHistoryModel;
use App\Models\VoucherClaimedModel;
use App\Models\VoucherRedeemModel;
use App\Models\StokModel;
use Exception;
use WebSocket\Client;

class Pages extends BaseController
{
    protected $barangModel;
    protected $gambarBarangModel;
    protected $gambarUserModel;
    protected $userModel;
    protected $pembeliModel;
    protected $pemesananModel;
    protected $formModel;
    protected $artikelModel;
    protected $gambarArtikelModel;
    protected $submitEmailModel;
    protected $voucherModel;
    protected $invoiceModel;
    protected $preorderBarangModel;
    protected $pointHistoryModel;
    protected $voucherClaimedModel;
    protected $voucherRedeemModel;
    protected $stokModel;

    protected $emailUjiCoba;
    public function __construct()
    {
        $this->emailUjiCoba = ['galihsuks123@gmail.com', 'lunareafurniture@gmail.com', 'galih8.4.2001@gmail.com'];
        $this->barangModel = new BarangModel();
        $this->gambarBarangModel = new GambarBarangModel();
        $this->gambarUserModel = new GambarUserModel();
        $this->userModel = new UserModel();
        $this->pembeliModel = new PembeliModel();
        $this->pemesananModel = new PemesananModel();
        $this->formModel = new FormModel();
        $this->artikelModel = new ArtikelModel();
        $this->gambarArtikelModel = new GambarArtikelModel();
        $this->submitEmailModel = new SubmitEmailModel();
        $this->voucherModel = new VoucherModel();
        $this->invoiceModel = new InvoiceModel();
        $this->preorderBarangModel = new PreorderBarangModel();
        $this->pointHistoryModel = new PointHistoryModel();
        $this->voucherClaimedModel = new VoucherClaimedModel();
        $this->voucherRedeemModel = new VoucherRedeemModel();
        $this->stokModel = new StokModel();
    }
    public function generateRandomCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 12; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
            if (($i + 1) % 4 == 0 && $i != 11) {
                $code .= '-';
            }
        }
        return $code;
    }
    public function kirimPesanEmail($email_cus, $subject, $isi)
    {
        $email = \Config\Services::email();
        $email->setFrom('no-reply@lunareafurniture.com', 'Lunarea Furniture');
        $email->setTo($email_cus);
        $email->setSubject($subject);
        $template = '
        <table
            align="center"
            border="0"
            cellpadding="0"
            cellspacing="0"
            role="presentation"
            style="background-color: #ebfaee; width: 100%"
        >
            <tbody>
                <tr>
                    <td style="padding: 20px">
                        <div>' . $isi . '</div>
                        <div style="height: 15px"></div>
                        <div style="padding-top: 20px; padding-bottom: 10px">
                            <table
                                align="center"
                                border="0"
                                cellpadding="0"
                                cellspacing="0"
                                role="presentation"
                                style="width: 100%"
                            >
                                <tbody>
                                    <tr style="text-align: center">
                                        <td>
                                            <div>
                                                <table
                                                    style="
                                                        margin-right: auto;
                                                        margin-left: auto;
                                                    "
                                                >
                                                    <tbody>
                                                        <tr
                                                            style="
                                                                text-align: center;
                                                            "
                                                        >
                                                            <td>
                                                                <img
                                                                    style="
                                                                        width: 150px;
                                                                    "
                                                                    src="https://lunareafurniture.com/img/Logo%20Lunarea%20Bg%20Terang.png"
                                                                    alt="Lunarea"
                                                                />
                                                            </td>
                                                            <td width="10">
                                                                &nbsp;
                                                            </td>
                                                            <td>
                                                                <span>|</span>
                                                            </td>
                                                            <td width="10">
                                                                &nbsp;
                                                            </td>
                                                            <td>
                                                                <span
                                                                    style="
                                                                        text-wrap: nowrap;
                                                                    "
                                                                    >©2025 All
                                                                    rights
                                                                    reserved.</span
                                                                >
                                                            </td>
                                                        </tr>
                                                        <tr
                                                            style="
                                                                text-align: center;
                                                            "
                                                        >
                                                            <td colspan="5">
                                                                <div
                                                                    style="
                                                                        padding-top: 5px;
                                                                        margin-left: auto;
                                                                        margin-right: auto;
                                                                    "
                                                                >
                                                                    <table
                                                                        align="center"
                                                                        border="0"
                                                                        cellpadding="0"
                                                                        cellspacing="0"
                                                                        role="presentation"
                                                                    >
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <a
                                                                                        target="_blank"
                                                                                        href="https://www.instagram.com/Lunareafurniture.official"
                                                                                    >
                                                                                        <img
                                                                                            src="https://lunareafurniture.com/img/sosmed/ig.png"
                                                                                            alt="instagram"
                                                                                            style="
                                                                                                height: 15px;
                                                                                            "
                                                                                        />
                                                                                    </a>
                                                                                </td>
                                                                                <td
                                                                                    width="15"
                                                                                >
                                                                                    &nbsp;
                                                                                </td>
                                                                                <td>
                                                                                    <a
                                                                                        target="_blank"
                                                                                        href="https://www.tiktok.com/@lunareafurnitureofficial"
                                                                                        ><img
                                                                                            src="https://lunareafurniture.com/img/sosmed/tiktok.png"
                                                                                            alt="instagram"
                                                                                            style="
                                                                                                height: 15px;
                                                                                            "
                                                                                    /></a>
                                                                                </td>
                                                                                <td
                                                                                    width="15"
                                                                                >
                                                                                    &nbsp;
                                                                                </td>
                                                                                <td>
                                                                                    <a
                                                                                        target="_blank"
                                                                                        href="https://www.threads.net/@lunareafurniture.official"
                                                                                        ><img
                                                                                            src="https://lunareafurniture.com/img/sosmed/thread.png"
                                                                                            alt="instagram"
                                                                                            style="
                                                                                                height: 15px;
                                                                                            "
                                                                                    /></a>
                                                                                </td>
                                                                                <td
                                                                                    width="15"
                                                                                >
                                                                                    &nbsp;
                                                                                </td>
                                                                                <td>
                                                                                    <a
                                                                                        target="_blank"
                                                                                        href="https://x.com/official14312"
                                                                                        ><img
                                                                                            src="https://lunareafurniture.com/img/sosmed/x.png"
                                                                                            alt="instagram"
                                                                                            style="
                                                                                                height: 15px;
                                                                                            "
                                                                                    /></a>
                                                                                </td>
                                                                                <td
                                                                                    width="15"
                                                                                >
                                                                                    &nbsp;
                                                                                </td>
                                                                                <td>
                                                                                    <a
                                                                                        target="_blank"
                                                                                        href="https://www.facebook.com/profile.php?id=61560396845112&locale=id_ID"
                                                                                        ><img
                                                                                            src="https://lunareafurniture.com/img/sosmed/fb.png"
                                                                                            alt="instagram"
                                                                                            style="
                                                                                                height: 15px;
                                                                                            "
                                                                                    /></a>
                                                                                </td>
                                                                                <td
                                                                                    width="15"
                                                                                >
                                                                                    &nbsp;
                                                                                </td>
                                                                                <td>
                                                                                    <a
                                                                                        target="_blank"
                                                                                        href="https://www.youtube.com/@LunareaFurnitureOfficial"
                                                                                        ><img
                                                                                            src="https://lunareafurniture.com/img/sosmed/youtube.png"
                                                                                            alt="instagram"
                                                                                            style="
                                                                                                height: 15px;
                                                                                            "
                                                                                    /></a>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        ';
        $email->setMessage($template);
        $email->send();
    }
    public function index()
    {
        $produk = $this->barangModel->getBarangLimit();
        $produkBaru = $this->barangModel->getBarangPopuler();
        $msgEvent = session()->getFlashdata('msg_event');
        $msgActive = session()->getFlashdata('msg_active');
        $counterEvent = 0;
        if ($msgEvent) {
            $counterEvent += count($msgEvent['voucherClaimed']);
            $counterEvent += count($msgEvent['voucherNoClaimed']);
            $counterEvent += count($msgEvent['codeRedeem']);
        } else {
            $msgEvent['voucherNoClaimed'] = $this->voucherModel->getVoucher();
            $msgEvent['voucherClaimed'] = [];
            $msgEvent['codeRedeem'] = [];
            $counterEvent += count($msgEvent['voucherNoClaimed']);
        }
        if ($msgActive) $counterEvent++;
        $data = [
            'title' => 'Beranda',
            'produk' => $produk,
            'produkBaru' => $produkBaru,
            'metaKeyword' => 'lunarea furniture,toko furniture,
            lemari dewasa lunarea semarang,lemari anak lunarea semarang,meja rias lunarea semarang,meja belajar lunarea semarang,meja tv lunarea semarang,meja tulis lunarea semarang,meja komputer lunarea semarang,rak sepatu lunarea semarang,rak besi lunarea semarang,rak serbaguna lunarea semarang,kursi lunarea semarang',
            'msg_active' => $msgActive,
            'msg_event' => session()->get('role') == '1' ? false : $msgEvent,
            'counterEvent' => $counterEvent
        ];
        return view('pages/home', $data);
    }
    public function sendwa()
    {
        $targetWa = "6281905266517";
        $tokenWa = env('TOKEN_FONNTE', 'DefaultValue');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $targetWa,
                'message' => 'halo cokkkk',
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $tokenWa
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }
        curl_close($curl);

        if (isset($error_msg)) {
            echo $error_msg;
        }
        echo $response;
    }
    public function kebijakanprivasi()
    {
        $data = [
            'title' => 'Kebijakan Privasi',
        ];
        return view('pages/kebijakanprivasi', $data);
    }
    public function syaratdanketentuan()
    {
        $voucher = $this->voucherModel->where('syarat_ketentuan is NOT NULL')->findAll();
        $data = [
            'title' => 'Syarat dan Ketentuan',
            'voucher' => $voucher
        ];
        return view('pages/syaratdanketentuan', $data);
    }
    public function faq()
    {
        $data = [
            'title' => 'FAQ',
        ];
        return view('pages/faq', $data);
    }
    public function article($judul_article = false)
    {
        $getArtikel = $this->artikelModel->getArtikelJudul($judul_article);
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        if (!$getArtikel) return redirect()->to('article');
        if ($judul_article) {
            $artikel = $getArtikel['cur'];
            $artikel['header'] = '/imgart/' . $artikel['id'];
            $artikel['isi'] = json_decode($artikel['isi'], true);
            $artikel['kategori'] = explode(",", $artikel['kategori']);
            $artikel['waktu'] = date("d", strtotime($artikel['waktu'])) . " " . $bulan[date("m", strtotime($artikel['waktu'])) - 1] . " " . date("Y", strtotime($artikel['waktu']));

            $artikelTerkait = $this->artikelModel->like('kategori', $artikel['kategori'][0], 'both')->findAll();
            foreach ($artikelTerkait as $ind_a => $a) {
                $artikelTerkait[$ind_a]['header'] = '/imgart/' . $a['id'];
                $artikelTerkait[$ind_a]['isi'] = json_decode($a['isi'], true);
                $artikelTerkait[$ind_a]['kategori'] = explode(",", $a['kategori']);
                $artikelTerkait[$ind_a]['waktu'] = date("d", strtotime($a['waktu'])) . " " . $bulan[date("m", strtotime($a['waktu'])) - 1] . " " . date("Y", strtotime($a['waktu']));
            }
            $produkTerkait = $this->barangModel->where(['subkategori' => str_replace(" ", "-", $artikel['kategori'][0])])->orderBy('tracking_pop', 'desc')->findAll(10, 0);
            $data = [
                'title' => 'Artikel ' . $artikel['judul'],
                'artikel' => $artikel,
                'prevArtikel' => $getArtikel['prev'],
                'nextArtikel' => $getArtikel['next'],
                'artikelTerkait' => $artikelTerkait,
                'produkTerkait' => $produkTerkait,
                'komen' => json_decode($artikel['komen'], true),
                'komenJson' => $artikel['komen'],
                'metaDeskripsi' => $artikel['judul'],
                'metaKeyword' => $artikel['keywords']
            ];
            return view('pages/artikel', $data);
        } else {
            $artikel = $getArtikel;
            foreach ($artikel as $ind_a => $a) {
                $artikel[$ind_a]['header'] = '/imgart/' . $a['id'];
                $artikel[$ind_a]['isi'] = json_decode($a['isi'], true);
                $artikel[$ind_a]['kategori'] = explode(",", $a['kategori']);
                $artikel[$ind_a]['waktu'] = date("d", strtotime($a['waktu'])) . " " . $bulan[date("m", strtotime($a['waktu'])) - 1] . " " . date("Y", strtotime($a['waktu']));
            }
            $data = [
                'title' => 'Artikel',
                'artikel' => $artikel
            ];
            return view('pages/artikelAll', $data);
        }
    }
    public function actionSearchArticle()
    {
        $cari = $this->request->getVar('cari');
        return redirect()->to('/article/find/' . str_replace(' ', '-', $cari));
    }
    public function findArticle($cari)
    {
        $artikel = $this->artikelModel->like('judul', str_replace("-", " ", $cari), 'both')->orderBy('id', 'desc')->findAll();
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        foreach ($artikel as $ind_a => $a) {
            $artikel[$ind_a]['header'] = '/imgart/' . $a['id'];
            $artikel[$ind_a]['isi'] = json_decode($a['isi'], true);
            $artikel[$ind_a]['kategori'] = explode(",", $a['kategori']);
            $artikel[$ind_a]['waktu'] = date("d", strtotime($a['waktu'])) . " " . $bulan[date("m", strtotime($a['waktu'])) - 1] . " " . date("Y", strtotime($a['waktu']));
        }
        $data = [
            'title' => 'Artikel',
            'artikel' => $artikel,
            'find' => str_replace('-', ' ', $cari)
        ];
        return view('pages/artikelAll', $data);
    }
    public function articleCategory($kategori)
    {
        $artikel = $this->artikelModel->getArtikelKategori($kategori);
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        if (!$artikel) return redirect()->to('article');
        foreach ($artikel as $ind_a => $a) {
            $artikel[$ind_a]['header'] = '/imgart/' . $a['id'];
            $artikel[$ind_a]['isi'] = json_decode($a['isi'], true);
            $artikel[$ind_a]['kategori'] = explode(",", $a['kategori']);
            $artikel[$ind_a]['waktu'] = date("d", strtotime($a['waktu'])) . " " . $bulan[date("m", strtotime($a['waktu'])) - 1] . " " . date("Y", strtotime($a['waktu']));
        }
        $data = [
            'title' => 'Artikel',
            'artikel' => $artikel,
            'category' => $kategori
        ];
        return view('pages/artikelAll', $data);
    }
    public function addArticle()
    {
        $data = [
            'title' => 'Tambah Artikel',
        ];
        return view('pages/addArtikel1', $data);
    }
    public function actionAddArticle()
    {
        $judul = $this->request->getVar('judul');
        $penulis = $this->request->getVar('penulis');
        $kategori = $this->request->getVar('kategori');
        $kategoriBarang = $this->request->getVar('kategori-barang');
        $kategori = $kategoriBarang . ',' . $kategori;
        $waktu = $this->request->getVar('waktu');
        $header = file_get_contents($this->request->getFile('header'));
        $counter = explode(",", $this->request->getVar('arrCounter'));

        $d = strtotime("+7 Hours");
        $id = "A" . date("YmdHis", $d);
        $insertGambarArtikel = ['id' => $id];

        $isi = [];
        $counterGambar = 0;
        foreach ($counter as $c) {
            $itemIsi = [];
            $tag = $this->request->getVar('tag' . $c);
            $itemIsi['tag'] = $tag;
            if ($tag == 'h2' || $tag == 'h4' || $tag == 'p') {
                $itemIsi['teks'] = $this->request->getVar('teks' . $c);
                $itemIsi['style'] = $this->request->getVar('style' . $c);
            } else if ($tag == 'a') {
                $itemIsi['link'] = $this->request->getVar('link' . $c);
                $itemIsi['teks'] = $this->request->getVar('teks' . $c);
                $itemIsi['style'] = $this->request->getVar('style' . $c);
            } else if ($tag == 'img') {
                $counterGambar++;
                $insertGambarArtikel["gambar" . $counterGambar] = file_get_contents($this->request->getFile('file' . $c));
                $itemIsi['src'] = "/imgart/" . $id . "/" . $counterGambar;
                $itemIsi['style'] = $this->request->getVar('style' . $c);
            }
            array_push($isi, $itemIsi);
        }

        $path = str_replace(",", "", $judul);
        $path = str_replace(".", "", $path);
        $path = str_replace("& ", "", $path);
        $path = str_replace("?", "", $path);
        $path = str_replace(" ", "-", $path);
        $path = strtolower($path);
        $this->artikelModel->insert([
            'id' => $id,
            'judul' => $judul,
            'path' => $path,
            'penulis' => $penulis,
            'kategori' => $kategori,
            'waktu' => $waktu,
            'isi' => json_encode($isi),
            'header' => $header,
            'suka' => 0,
            'bagikan' => 0,
            'komen' => json_encode([]),
        ]);
        $this->gambarArtikelModel->insert($insertGambarArtikel);

        session()->setFlashdata('msg', 'Artikel berhasil ditambahkan');
        return redirect()->to('/article/' . $path);
    }
    public function editArticle($id)
    {
        $artikel = $this->artikelModel->where(['id' => $id])->first();
        $artikel['isi'] = json_decode($artikel['isi'], true);
        $counterIsi = count($artikel['isi']);
        $arrCounterIsi = [];
        for ($i = 1; $i <= $counterIsi; $i++) {
            array_push($arrCounterIsi, $i);
        }
        $d = strtotime("+7 Hours");
        $waktu = date("Y-m-d H:i:", $d) . "00";
        $data = [
            'title' => 'Edit Artikel',
            'artikel' => $artikel,
            'isi' => $artikel['isi'],
            'isiJson' => json_encode($artikel['isi']),
            'counterIsi' => $counterIsi,
            'arrCounterIsi' => json_encode($arrCounterIsi),
            'arrCounter' => implode(",", $arrCounterIsi),
            'waktu' => $waktu
        ];
        return view('pages/editArtikel', $data);
    }
    public function actionEditArticle($id)
    {
        $judul = $this->request->getVar('judul');
        $penulis = $this->request->getVar('penulis');
        $kategori = $this->request->getVar('kategori');
        $kategoriBarang = $this->request->getVar('kategori-barang');
        $kategori = $kategoriBarang . ',' . $kategori;
        $waktu = $this->request->getVar('waktu');
        $header = $this->request->getFile('header');
        $counter = explode(",", $this->request->getVar('arrCounter'));

        $getFiles = $this->request->getFiles();
        unset($getFiles['header']);

        $isiCur = json_decode($this->artikelModel->where(['id' => $id])->first()['isi'], true);
        $insertGambarArtikel = [];
        $arrUrutanImg = [];
        foreach ($isiCur as $ind_i => $i) {
            if ($i['tag'] == 'img') {
                array_push($arrUrutanImg, ($ind_i + 1));
            }
        }

        $isi = [];
        $counterGambar = 0;
        foreach ($counter as $c) {
            $itemIsi = [];
            $tag = $this->request->getVar('tag' . $c);
            $itemIsi['tag'] = $tag;
            if ($tag == 'h2' || $tag == 'h4' || $tag == 'p') {
                $itemIsi['teks'] = $this->request->getVar('teks' . $c);
                $itemIsi['style'] = $this->request->getVar('style' . $c);
            } else if ($tag == 'a') {
                $itemIsi['link'] = $this->request->getVar('link' . $c);
                $itemIsi['teks'] = $this->request->getVar('teks' . $c);
                $itemIsi['style'] = $this->request->getVar('style' . $c);
            } else if ($tag == 'img') {
                $counterGambar++;
                $insertGambarArtikel["gambar" . $counterGambar] = $this->request->getFile('file' . $c)->isValid() ? file_get_contents($this->request->getFile('file' . $c)) : $this->gambarArtikelModel->where(['id' => $id])->first()['gambar' . (array_search($c, $arrUrutanImg) + 1)];
                $itemIsi['src'] = "/imgart/" . $id . "/" . $counterGambar;
                $itemIsi['style'] = $this->request->getVar('style' . $c);
                $itemIsi['link'] = $this->request->getVar('link' . $c);
                $itemIsi['sumber'] = $this->request->getVar('sumber' . $c);
            }
            array_push($isi, $itemIsi);
        }

        $path = str_replace(",", "", $judul);
        $path = str_replace(".", "", $path);
        $path = str_replace("& ", "", $path);
        $path = str_replace("?", "", $path);
        $path = str_replace(" ", "-", $path);
        $path = strtolower($path);

        if (!empty($_FILES['header']['tmp_name'])) {
            $this->artikelModel->where(['id' => $id])->set([
                'judul' => $judul,
                'path' => $path,
                'penulis' => $penulis,
                'kategori' => $kategori,
                'waktu' => $waktu,
                'isi' => json_encode($isi),
                'header' => file_get_contents($header),
            ])->update();
        } else {
            $this->artikelModel->where(['id' => $id])->set([
                'judul' => $judul,
                'path' => $path,
                'penulis' => $penulis,
                'kategori' => $kategori,
                'waktu' => $waktu,
                'isi' => json_encode($isi)
            ])->update();
        }

        //pengosongan gambar artikel
        $kosongkanGambar = [];
        for ($i = 1; $i <= count($arrUrutanImg); $i++) {
            $kosongkanGambar['gambar' . $i] = null;
        }
        if (count($kosongkanGambar) > 0) {
            $this->gambarArtikelModel->where(['id' => $id])->set($kosongkanGambar)->update();
        }
        if (count($insertGambarArtikel) > 0) {
            $this->gambarArtikelModel->where(['id' => $id])->set($insertGambarArtikel)->update();
        }

        session()->setFlashdata('msg', 'Artikel berhasil diubah');
        return redirect()->to('/article/' . $path);
    }
    public function isiGambarArtikel()
    {
        $artikel = $this->artikelModel->findAll();
        foreach ($artikel as $a) {
            $this->gambarArtikelModel->insert(['id' => $a['id']]);
        }
        return $this->response->setJSON(['success' => true], false);
    }
    public function addKomen($judul_article)
    {
        $artikel = $this->artikelModel->where(['path' => $judul_article])->first();
        $komenCurr = json_decode($artikel['komen'], true);
        array_push($komenCurr, [
            'nama' => $this->request->getVar('nama'),
            'isi' => $this->request->getVar('isi'),
        ]);
        $this->artikelModel->where(['path' => $judul_article])->set(['komen' => json_encode($komenCurr)])->update();
        return redirect()->to('/article/' . $judul_article);
    }
    public function delKomen($ind_komen, $judul_artikel)
    {
        $artikel = $this->artikelModel->where(['path' => $judul_artikel])->first();
        $komenCurr = json_decode($artikel['komen'], true);
        unset($komenCurr[$ind_komen]);
        $komenBaru = array_values($komenCurr);
        $this->artikelModel->where(['path' => $judul_artikel])->set(['komen' => json_encode($komenBaru)])->update();
        return redirect()->to('/article/' . $judul_artikel);
    }
    public function editKomen($ind_komen, $judul_article)
    {
        $artikel = $this->artikelModel->where(['path' => $judul_article])->first();
        $komenCurr = json_decode($artikel['komen'], true);
        $komenCurr[$ind_komen] = [
            'nama' => $this->request->getVar('nama_edit'),
            'isi' => $this->request->getVar('isi_edit'),
        ];
        $this->artikelModel->where(['path' => $judul_article])->set(['komen' => json_encode($komenCurr)])->update();
        return redirect()->to('/article/' . $judul_article);
    }
    public function submitEmail($judul_article)
    {
        $email = $this->request->getVar('email');
        $this->submitEmailModel->insert(['email' => $email]);
        session()->set('submitEmail', true);
        return redirect()->to('/article/' . $judul_article);
    }
    public function addLikeArticle($id_artikel)
    {
        $artikelCurr = $this->artikelModel->getArtikel($id_artikel);
        $this->artikelModel->where(['id' => $id_artikel])->set(['suka' => $artikelCurr['suka'] + 1])->update();
        return redirect()->to('/article/' . $artikelCurr['path']);
    }
    public function addShareArticle($id_artikel)
    {
        $artikelCurr = $this->artikelModel->getArtikel($id_artikel);
        $this->artikelModel->where(['id' => $id_artikel])->set(['bagikan' => $artikelCurr['bagikan'] + 1])->update();
        return redirect()->to('/article/' . $artikelCurr['path']);
    }
    public function form()
    {
        $data = [
            'title' => 'Kontak Kami',
            'val' => [
                'msg' => session()->getFlashdata('val-msg')
            ]
        ];
        return view('pages/form', $data);
    }
    public function actionForm()
    {
        $nama = $this->request->getVar('nama');
        $nohp = $this->request->getVar('nohp');
        $alamat = $this->request->getVar('alamat');
        $pesan = $this->request->getVar('pesan');

        $this->kirimPesanEmail('info@lunareafurniture.com', 'Lunarea Store - Formulir', "<div>
            <h1>Pengisian Formulir</h1
            <p>Pesan :</p>
            <p>" . $pesan . "</p>
        </div>");

        $d = strtotime("+7 hours");
        $this->formModel->insert([
            'nama' => $nama,
            'nohp' => $nohp,
            'alamat' => $alamat,
            'pesan' => $pesan,
            'waktu' => date("Y-m-d H:i:s", $d)
        ]);
        session()->setFlashdata('form-thanks', true);
        return redirect()->to('/formthanks');
    }
    public function formThanks()
    {
        if (!session()->getFlashdata('form-thanks')) return redirect()->to('/form');
        $data = [
            'title' => 'Terima kasih atas pengisian Formulir',
        ];
        return view('pages/formThanks', $data);
    }
    public function all($subkategori = '')
    {
        $produk = $this->barangModel->where(['active' => '1'])->like('subkategori', $subkategori, 'both')->orderBy('nama', 'asc')->findAll(20, 0);
        $semuaproduk = $this->barangModel->where(['active' => '1'])->like('subkategori', $subkategori, 'both')->orderBy('nama', 'asc')->findAll();
        if (count($produk) <= 0) return redirect()->to('/all');
        $meta = [
            'lemari-dewasa' => [
                'deskripsi' => 'Kenapa Harus Punya Lemari Pakaian? Sebagai salah satu furniture penting yang ada di rumah, lemari memegang peran penting untuk memastikan barang di dalamnya tertata rapi. Lemari bisa ditempatkan pada bagian rumah di mana saja tergantung dari jenis penyimpanannya. Sesuai dengan namanya, lemari pakaian biasanya di tempat tidur sebagai tempat penyimpanan pakaian. Tapi tidak menutup kemungkinan juga lemari digunakan untuk menyimpan keperluan lain dan bisa ditempatkan secara fleksibel di ruangan lainnya.',
                'keywords' => ['lemari pakaian minimalis', 'lemari baju minimalis', 'Lemari pakaian minimalis modern minimalis terbaru', 'lemari pakaian 2 pintu', 'lemari pakaian 3 pintu', 'Harga Lemari Pakaian Minimalis Modern', 'Harga lemari baju', 'jual lemari pakaian', 'harga lemari pakaian minimalis modern', 'lemari dewasa lunarea'],
            ],
            'meja-rias' => [
                'deskripsi' => 'Alasan Kenapa Meja Rias Harus Ada Di Kamar Salah satu perabotan rumah tangga yang penting walaupun bukan termasuk yang utama adalah meja rias. Sama seperti namanya,  furniture satu ini punya fungsi yang vital terlebih buat perempuan. Dengan meja rias atau tolet inilah yang membuatmu tidak perlu worry lagi bakalan nggak stunning setiap harinya. Karena diperuntukkan secara spesial buat perempuan, tentunya kamu juga bisa dengan mudah menemukan berbagai jenis desain yang cantik dan menarik.',
                'keywords' => ['meja rias', 'meja rias minimalis modern', 'meja rias minimalis', 'meja rias minimalis kecil', 'meja rias simple', 'harga meja rias', 'meja rias multifungsi', 'meja rias lunarea'],
            ],
            'meja-belajar' => [
                'deskripsi' => 'Apa Itu Meja Belajar? Meja belajar merupakan salah satu furniture penting yang diupayakan dapat menciptakan lingkungan belajar yang nyaman dan produktif di rumah. Ada beberapa hal yang perlu jadi pertimbangan saat memilih meja belajar mana yang akan dibeli, sebut saja meja belajar minimalis, meja belajar aesthetic, meja belajar multifungsi, meja belajar simple, dan masih banyak lagi ragamnya. Nah, pada dasarnya yang perlu dipilih adalah sesuai dengan kebutuhan dan mengesampingkan keinginan. Tapi kalau bisa keduanya, kenapa tidak? meja belajar minimalis unik mungkin bisa jadi pilihan yang pas untukmu yang tidak suka sesuatu monoton.',
                'keywords' => ['meja belajar', 'meja belajar minimalis', 'meja belajar aesthetic', 'meja belajar simple', 'meja belajar minimalis modern', 'meja belajar minimalis unik', 'meja belajar lunarea'],
            ],
            'meja-tv' => [
                'deskripsi' => 'Ini Alasannya Perlu Meja TV di Rumah! Televisi sudah jadi barang elektronik yang dirasa wajib ada di setiap rumah. Kegunaannya sebagai media mencari hiburan baik itu saat sendiri atau bersama orang-orang tersayang menjadikan barang elektronik selalu eksis hingga sekarang. Maka dari itu, untuk melengkapi momen menonton TV jadi lebih asyik, diperlukan space khusus berupa Meja TV tentunya. Tak hanya itu saja, furniture meja yang satu ini juga punya fungsi lain sebagai tempat penyimpanan peralatan pendukung nonton TV seperti DVD player, PS player, WiFi, dan masih banyak lagi. Karena saking multifungsinya, furniture meja tempat TV ini punya sebutan lain di masyarakat seperti rak TV, buffet TV, credenza TV, dan sebutan lain yang sebenarnya jika dilihat dari fungsi tetap sama.',
                'keywords' => ['meja tv', 'meja tv minimalis', 'rak tv minimalis', 'harga rak tv minimalis modern', 'meja tv minimalis modern termurah', 'meja tv minimalis modern', 'meja tv lunarea'],
            ],
            'meja-tulis' => [
                'deskripsi' => 'Meja tulis merupakan salah satu furniture wajib yang ada di rumah atau kantor. Sama seperti namanya, meja jenis ini dibuat secara khusus untuk meningkatkan produktivitas dan kenyamanan. Faktor inilah yang diharapkan dapat membuat seseorang bisa menyelesaikan pekerjaannya lebih efektif dan efisien. Desain meja kerja kantor ini juga ditentukan oleh kebutuhan. Keberagaman desain ini merupakan jawaban atas beragamnya kebutuhan setiap orang masing-masing.',
                'keywords' => ["meja kantor", "meja kerja kantor", "meja kantor minimalis", "harga meja kantor", "meja rapat kantor", "jual meja kantor", "meja meeting kantor", "harga meja kerja kantor", "meja kantor kayu", "jual meja kantor terdekat", 'meja tulis lunarea'],
            ],
            'meja-komputer' => [
                'deskripsi' => 'Kenapa Harus Beli Meja Kerja? Meja tulis merupakan salah satu furniture wajib yang ada di rumah atau kantor. Sama seperti namanya, meja jenis ini dibuat secara khusus untuk meningkatkan produktivitas dan kenyamanan. Faktor inilah yang diharapkan dapat membuat seseorang bisa menyelesaikan pekerjaannya lebih efektif dan efisien. Desain meja kerja kantor ini juga ditentukan oleh kebutuhan. Ada meja tulis dengan top table lurus tanpa ada ambalan tambahan, ada jenis meja kantor yang terkadang diberikan fitur tambahan seperti laci dan space kabinet tertutup di sisi kiri dan atau kanan bawah. Keberagaman desain ini merupakan jawaban atas beragamnya kebutuhan setiap orang masing-masing.',
                'keywords' => ['beli meja kerja', 'meja tulis', 'meja kerja kantor', 'meja kantor', 'meja kerja kayu', 'meja kerja minimalis', 'meja komputer lunarea'],
            ],
            'rak-sepatu' => [
                'deskripsi' => 'Apa itu Rak Sepatu? Furniture yang satu ini menjadi perabotan rumah tangga yang penting dan harus ada di rumah. Terlebih jika memiliki banyak koleksi sneaker yang cukup banyak dan berharga. Pada umumnya, lemari rak sepatu dibuat dari bahan kayu, besi atau logam, plastik, hingga bahan lainnya. Ukuran dan desainnya pun beragam, bergantung pada kebutuhan dan penempatannya. Semisal saja, untuk penempatan di area yang rentan dengan debu akan lebih baik jika memilih rak sepatu tertutup.',
                'keywords' => ['Rak Sepatu', 'lemari rak sepatu', 'rak sepatu tertutup', 'rak sepatu minimalis', 'rak sepatu minimalis tertutup', 'beli rak sepatu tertutup', 'Rak sepatu dari kayu', 'Beli Rak Sepatu', 'rak sepatu lunarea'],
            ],
            'rak-besi' => [
                'deskripsi' => 'Rak besi serbaguna merupakan salah satu solusi penyimpanan dengan kegunaan yang beragam. Material besi yang digunakan membuat jenis furniture ini mampu menahan beban berat lebih baik jika dibandingkan dengan material jenis lainnya. Selain itu, rak besi juga lebih mudah dipasang dan disesuaikan sesuai dengan kebutuhan. Material besi yang kokoh ini membuat umur penggunaan rak besi lebih lama sehingga cocok digunakan sebagai investasi jangka panjang. Rak besi hadir dengan berbagai model dengan ukuran dan desain berbeda yang memungkinkan bagi Kamu untuk memilih rak besi susun sesuai dengan kebutuhan dan preferensi masing-masing.',
                'keywords' => ['rak besi serbaguna', 'rak besi', 'rak besi susun', 'rak susun besi', 'rak besi susun', 'rak besi bertingkat', 'rak besi minimalis', 'harga rak besi', 'harga rak besi 4 susun', 'rak besi lunarea'],
            ],
            'rak-serbaguna' => [
                'deskripsi' => 'Apa Itu Rak Serbaguna? Dari sekian banyaknya peralatan rumah tangga, rak susun serbaguna jadi furniture yang harus ada untuk membuat barang-barang tersimpan dengan lebih terorganisir. Nah, karena fungsi utama inilah, Kamu akan menemukan berbagai model rak modern dan minimalis dengan spesifikasi yang berbeda pula tergantung dari kebutuhan masing-masing.',
                'keywords' => ['rak serbaguna', 'rak susun serbaguna', 'rak penyimpanan serbaguna', 'rak portable serbaguna', 'harga rak serbaguna', 'rak kayu serbaguna minimalis', 'rak bertingkat baru', 'rak serbaguna lunarea'],
            ],
            'kursi' => [
                'deskripsi' => 'Apa Itu Kursi Stainless? Kursi susun stainless merupakan salah satu pilihan favorit bagi banyak orang. Tidak hanya digunakan di kafe dan restoran, tetapi juga semakin bisa pula untuk di rumah-rumah pribadi. Desain yang sederhana membuat kursi ini cocok dalam berbagai kebutuhan yang membutuhkan banyak kursi di sebuah pertemuan besar. Maka dari itu, tak jarang pulang kursi kondangan ini bisa ditemui di acara pernikahan, rapat, pesta, dan lain sebagainya.',
                'keywords' => ['kursi stainless', 'kursi susun', 'kursi kondangan', 'kursi besi', 'kursi tumpuk', 'kursi pesta', 'kursi kantor', 'kursi kerja', 'kursi hajatan', 'kursi lunarea']
            ],
            'lemari-anak' => [
                'deskripsi' => 'Kenapa Harus Punya Lemari Kecil Buat Si Buah Hati? Moms, udah punya lemari buat buah hati belum? Nah kalau belum, yuk simak dulu alasannya kenapa Moms harus punya lemari kecil yang dikhususkan buat menyimpan baju-baju si buah hati. Seperti lemari pakaian pada umumnya, lemari pakaian kecil ini juga memiliki fungsi yang sama, hanya saja ukurannya yang lebih kecil menyesuaikan kebutuhan si kecil. Memisahkan pakaian anak di lemari pakaian kecil juga membuat Moms lebih mudah saat mencari dan menata baju-baju si buah hati loh! Apalagi kalau si kesayangan lagi aktif-aktifnya. Pasti akan repot kalau baju-baju si kecil bercampur dengan baju-baju yang bukan miliknya bukan? Dengan lemari pakaian kecil ini juga secara tidak langsung membuat anak bertanggung jawab dengan pakaiannya sendiri. Inilah saat yang tepat untuk membuat anak merasa memiliki dan merawat barang-barang miliknya dengan baik yang disimpan dengan baik pula di lemari baju kecil miliknya.',
                'keywords' => ['lemari kecil', 'lemari pakaian kecil', 'lemari baju kecil', 'lemari minimalis kecil', 'lemari baju minimalis kecil', 'lemari kayu kecil', 'lemari anak', 'lemari kecil kayu', 'harga lemari kayu 2 pintu kecil', 'lemari anak lunarea']
            ]
        ];

        $data = [
            'title' => 'Semua Produk',
            'produk' => $produk,
            'kategori' => $subkategori,
            'page' => 1,
            'nama' => false,
            'semuaProduk' => $semuaproduk,
        ];
        if ($subkategori) {
            $data['metaDeskripsi'] = $meta[$subkategori]['deskripsi'];
            $data['metaKeyword'] = implode(",", $meta[$subkategori]['keywords']);
        }
        return view('pages/all', $data);
    }
    public function allPage($page, $subkategori = false)
    {
        $pagination = (int)$page;
        if ($pagination > 1) {
            $hitungOffset = 20 * ($pagination - 1);
            $produk = $this->barangModel->where(['active' => '1', 'subkategori' => $subkategori])->orderBy('nama', 'asc')->findAll(20, $hitungOffset);
        } else {
            $produk = $this->barangModel->where(['active' => '1', 'subkategori' => $subkategori])->orderBy('nama', 'asc')->findAll(20, 0);
        }
        $semuaproduk = $this->barangModel->where(['active' => '1', 'subkategori' => $subkategori])->orderBy('nama', 'asc')->findAll();
        $data = [
            'title' => 'Semua Produk',
            'produk' => $produk,
            'kategori' => $subkategori,
            'page' => $page,
            'nama' => false,
            'semuaProduk' => $semuaproduk
        ];
        return view('pages/all', $data);
    }
    public function signup()
    {
        $data = [
            'title' => 'Daftar',
            'val' => [
                'val_nama' => session()->getFlashdata('val-nama'),
                'val_email' => session()->getFlashdata('val-email'),
                'val_sandi' => session()->getFlashdata('val-sandi'),
                'val_nohp' => session()->getFlashdata('val-nohp'),
                'msg' => session()->getFlashdata('msg'),
                // 'val_alamat' => session()->getFlashdata('val-alamat'),
            ]
        ];
        return view('pages/signup', $data);
    }
    public function kirimEmail()
    {
        $otp_number = [rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9)];
        $d = strtotime("+425 minutes");
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        $waktu_otp_tanggal = date("d", $d) . " " . $bulan[date("m", $d) - 1] . " " . date("Y H:i:s", $d);

        $isinya = '
        <div style="width: 100%">
            <img
                src="https://lunareafurniture.com/imgvoucher/email/2"
                alt="banner"
                style="width: 100%; border-radius: 5px"
            />
        </div>
        <div style="height: 15px"></div>
        <div
            style="
                background-color: white;
                    padding-right: 20px;
                    padding-left: 20px;
                    padding-top: 20px;
                    padding-bottom: 20px;
                    border-radius: 10px;
                "
        >
            <table>
                <tbody>
                    <tr>
                        <td>
                            <span>Klaim Cashback Sebesar</span>
                            <span style="font-weight: 700"
                                >Rp. 25.000,00</span
                            >
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                >Dengan minimal belanja produk
                                lunarea senilai Rp
                                250.000,00</span
                            >
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span
                                style="
                                    color: #8292b0;
                                    margin-bottom: 20px;
                                "
                                >**promo terbatas hanya untuk 30
                                pembeli pertama</span
                            >
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="padding-top: 10px">
                                <a
                                    href="https://lunareafurniture.com/"
                                    style="
                                        text-decoration: none;
                                        color: white;
                                        background-color: #1db954;
                                        padding-left: 20px;
                                        padding-right: 20px;
                                        padding-top: 10px;
                                        padding-bottom: 10px;
                                        border-radius: 7px;
                                        line-height: 40px;
                                        font-weight: 700;
                                    "
                                    >Belanja sekarang</a
                                >
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        ';
        $email = session()->get('email');
        $this->kirimPesanEmail($email, 'Lunarea Store - Voucher Ulang Tahun', $isinya);
        return $this->response->setJSON(['succes' => true], false);
    }
    public function kirimOTP()
    {
        $emailUser = session()->get('email');
        $otp_number = [rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9), rand(0, 9)];
        $d = strtotime("+425 minutes");
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        $waktu_otp_tanggal = date("d", $d) . " " . $bulan[date("m", $d) - 1] . " " . date("Y H:i:s", $d);

        $this->kirimPesanEmail(session()->get('email'), 'Lunarea Store - Verifikasi OTP', '
            <div style="display: flex">
                <div style="width: 30px"></div>
                <div style="width: 100%">
                    <h1 style="color: #1db954">Verifikasi Email Kamu!</h1>
                    <hr />
                    <p>Masukan 6 kode OTP di bawah ini</p>
                    <div style="background-color: #1db954; font-weight: bold">
                        <div style="height: 30px"></div>
                        <div style="display: flex">
                            <div style="width: 100%"></div>
                            <div style="display: flex">
                                <div
                                    style="
                                        border-radius: 10px;
                                        background-color: #e0ffeb;
                                        padding: 1em;
                                        color: #1db954;
                                    "
                                >
                                    ' . $otp_number[0] . '
                                </div>
                                <div style="width: 5px"></div>
                                <div
                                    style="
                                        border-radius: 10px;
                                        background-color: #e0ffeb;
                                        padding: 1em;
                                        color: #1db954;
                                    "
                                >
                                    ' . $otp_number[1] . '
                                </div>
                                <div style="width: 5px"></div>
                                <div
                                    style="
                                        border-radius: 10px;
                                        background-color: #e0ffeb;
                                        padding: 1em;
                                        color: #1db954;
                                    "
                                >
                                    ' . $otp_number[2] . '
                                </div>
                                <div style="width: 5px"></div>
                                <div
                                    style="
                                        border-radius: 10px;
                                        background-color: #e0ffeb;
                                        padding: 1em;
                                        color: #1db954;
                                    "
                                >
                                    ' . $otp_number[3] . '
                                </div>
                                <div style="width: 5px"></div>
                                <div
                                    style="
                                        border-radius: 10px;
                                        background-color: #e0ffeb;
                                        padding: 1em;
                                        color: #1db954;
                                    "
                                >
                                    ' . $otp_number[4] . '
                                </div>
                                <div style="width: 5px"></div>
                                <div
                                    style="
                                        border-radius: 10px;
                                        background-color: #e0ffeb;
                                        padding: 1em;
                                        color: #1db954;
                                    "
                                >
                                    ' . $otp_number[5] . '
                                </div>
                            </div>
                            <div style="width: 100%"></div>
                        </div>
                        <p style="color: white; text-align: center">Atau klik</p>
                        <div style="display: flex">
                            <div style="width: 100%"></div>
                            <a
                                href="https://lunareafurniture.com/verify/url/' . base64_encode(implode('', $otp_number)) . '"
                                style="
                                    color: #1db954;
                                    background-color: #e0ffeb;
                                    text-decoration: none;
                                    padding: 0.7em 1em;
                                    border-radius: 0.3em;
                                    transition: 0.2s;
                                    width: 100%;
                                    text-align: center;
                                "
                                >Aktivasi Email</a
                            >
                            <div style="width: 100%"></div>
                        </div>
                        <div style="height: 30px"></div>
                    </div>
                    <p>
                        Kode ini hanya berlaku sampai dengan
                        <b style="color: #1db954">' . $waktu_otp_tanggal . '</b>
                    </p>
                    <p>
                        Jaga keamanan akun dengan tidak membagikan kode OTP ataupun
                        link di atas kepada orang lain.
                    </p>
                </div>
                <div style="width: 30px"></div>
            </div>
        ');

        $this->userModel->where('email', $emailUser)->set([
            'otp' => implode('', $otp_number),
            'waktu_otp' => $d
        ])->update();

        session()->setFlashdata('msg', "OTP telah dikirim ke email " . $emailUser . " dan berlaku hingga " . $waktu_otp_tanggal);
        return redirect()->to('/verify');
    }
    public function actionSignupSalah()
    {
        session()->setFlashdata('msg', "Maaf, masih dalam masa perbaikan. Akan aktif kembali ketika pukul 07:30 WIB");
        return redirect()->to('/signup');
    }
    public function actionSignup()
    {

        if (!$this->validate([
            'nama' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Nama lengkap harus diisi',
                ]
            ],
            'email' => [
                'rules' => 'required|is_unique[user.email]',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'is_unique' => 'Email sudah terdaftar',
                ]
            ],
            'sandi' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Sandi harus diisi'
                ]
            ],
            'nohp' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Nomor handphone harus diisi'
                ]
            ],
        ])) {
            $validation = \Config\Services::validation();
            session()->setFlashdata('val-nama', $validation->getError('nama'));
            session()->setFlashdata('val-nohp', $validation->getError('nohp'));
            session()->setFlashdata('val-email', $validation->getError('email'));
            session()->setFlashdata('val-sandi', $validation->getError('sandi'));
            session()->setFlashdata('val-nohp', $validation->getError('nohp'));
            // session()->setFlashdata('val-alamat', $validation->getError('alamat'));
            return redirect()->to('/signup')->withInput();
        }

        $otp_number = rand(100000, 999999);
        // $waktu_otp = time() + 300;
        $d = strtotime("+425 minutes");
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        $waktu_otp_tanggal = date("d", $d) . " " . $bulan[date("m", $d) - 1] . " " . date("Y H:i:s", $d);

        $this->kirimPesanEmail($this->request->getVar('email'), 'Lunarea Store - Verifikasi OTP', '
            <p>Berikut kode OTP verifikasi</p>
            <h1>' . $otp_number . '</h1>
            <p>Atau Anda dapat menekan tombol dibawah untuk aktivasi Email tanpa perlu memasukan kode OTP</p>
            <a href="https://lunareafurniture.com/verify/url/' . base64_encode($otp_number) . '" style="color: white;background-color: #1db954;text-decoration: none;padding: 0.7em 1em;border-radius: 0.3em;transition: 0.2s;" onmouseover="this.style.color=\'#1db954\'; this.style.backgroundColor=\'#ebfff2\'" onmouseout="this.style.color=\'white\'; this.style.backgroundColor=\'#1db954\'">Aktivasi Email</a>
            <p>Kode beserta Link ini berlaku hingga ' . $waktu_otp_tanggal . '</p>');

        $this->userModel->insert([
            'email' => $this->request->getVar('email'),
            'sandi' => password_hash($this->request->getVar('sandi'), PASSWORD_DEFAULT),
            'role' => '0',
            'otp' => $otp_number,
            'active' => '0',
            'waktu_otp' => $d
        ]);
        $this->pembeliModel->insert([
            'nama' => $this->request->getVar('nama'),
            'email_user' => $this->request->getVar('email'),
            'nohp' => $this->request->getVar('nohp'),
            'alamat' => json_encode([]),
            'wishlist' => json_encode([]),
            'keranjang' => json_encode([]),
            'transaksi' => json_encode([]),
            'poin' => json_encode([]),
            'tier' => json_encode([
                'label' => 'bronze',
                'data' => []
            ]),
            'foto' => '/imguser/ZGVmYXVsdA=='
        ]);

        //klaimkan voucher yang auto klaim
        $vouchers = $this->voucherModel->where(['active' => true])->findAll();
        $counter = 0;
        $waktuCurrYmd = date("Y-m-d", strtotime("+7 Hours"));
        foreach ($vouchers as $v) {
            $kadaluarsa = null;
            $waktuCurr = (string)strtotime("+7 Hours") + (string)$counter;
            if ($v['durasi']) {
                $kadaluarsa = date("Y-m-d", strtotime($v['durasi'], strtotime($waktuCurrYmd)));
            }
            if ($v['auto_claimed']) {
                $this->voucherClaimedModel->insert([
                    'id' => $waktuCurr,
                    'id_voucher' => $v['id'],
                    'kadaluarsa' => $kadaluarsa,
                    'email_user' => $this->request->getVar('email'),
                    'active' => true
                ]);
            }
            $counter++;
        }

        $emailUser = $this->request->getVar('email');
        $getUser = $this->userModel->getUser($emailUser);
        $ses_data = [
            'email' => $getUser['email'],
            'active' => '0',
            'isLogin' => true
        ];
        session()->set($ses_data);
        session()->setFlashdata('msg', "OTP telah dikirim ke email " . $emailUser . " dan berlaku hingga " . $waktu_otp_tanggal);
        return redirect()->to('/verify');
    }
    public function verify()
    {
        $waktuExpire = $this->userModel->getUser(session()->get('email'))['waktu_otp'];
        $waktuCurr = strtotime("+7 Hours");
        $waktuSelisih = $waktuExpire - $waktuCurr;
        $waktu = false;
        if ($waktuSelisih >= 0) $waktu = date("i:s", $waktuSelisih);
        else {
            return $this->kirimOTP();
        }
        $data = [
            'title' => 'Verifikasi',
            'val' => [
                'msg' => session()->getFlashdata('msg'),
                'val_verify' => session()->getFlashdata('val_verify')
            ],
            'waktu' => $waktu,
            'waktuExp' => date("Y-m-d H:i:s", $waktuExpire),
            'waktuCurr' => date("Y-m-d H:i:s", $waktuCurr)
        ];
        return view('pages/verify', $data);
    }
    public function actionVerify()
    {
        $otp1 = $this->request->getVar("otp1");
        $otp2 = $this->request->getVar("otp2");
        $otp3 = $this->request->getVar("otp3");
        $otp4 = $this->request->getVar("otp4");
        $otp5 = $this->request->getVar("otp5");
        $otp6 = $this->request->getVar("otp6");
        $otp = $otp1 . $otp2 . $otp3 . $otp4 . $otp5 . $otp6;
        $email = session()->get("email");
        $getUser = $this->userModel->getUser($email);
        if ($otp != $getUser['otp']) {
            session()->setFlashdata('val_verify', "OTP salah");
            return redirect()->to("/verify");
        }
        $waktu_otp = time();
        if ($waktu_otp > (int)$getUser['waktu_otp']) {
            session()->setFlashdata('msg', "OTP telah berakhir. Minta kirim ulang<br>dibawah untuk mendapatkan kembali");
            return redirect()->to("/verify");
        }

        $getPembeli = $this->pembeliModel->getPembeli($email);
        $ses_data = [
            'active' => '1',
            'email' => $getUser['email'],
            'role' => $getUser['role'],
            'nama' => $getPembeli['nama'],
            'tgl_lahir' => $getPembeli['tgl_lahir'],
            'alamat' => json_decode($getPembeli['alamat'], true),
            'nohp' => $getPembeli['nohp'],
            'wishlist' => json_decode($getPembeli['wishlist'], true),
            'keranjang' => json_decode($getPembeli['keranjang'], true),
            'transaksi' => json_decode($getPembeli['transaksi'], true),
            'tier' => json_decode($getPembeli['tier'], true),
            'isLogin' => true,
            'poin' => json_decode($getPembeli['poin'], true),
            'foto' => $getPembeli['foto']
        ];
        $this->userModel->where('email', $email)->set([
            'active' => '1',
            'otp' => '0',
            'waktu_otp' => '0'
        ])->update();
        session()->set($ses_data);
        return redirect()->to('/verify/url/redirect/success');
    }
    public function verifyUrl($code)
    {
        $email = session()->get('email');
        $getUser = $this->userModel->getUser($email);
        $d = strtotime("+7 hours");

        if (base64_decode($code) != $getUser['otp'] || $d > (int)$getUser['waktu_otp']) {
            return redirect()->to('/verify/url/redirect/fail');
        }
        $getPembeli = $this->pembeliModel->getPembeli($email);
        $ses_data = [
            'active' => '1',
            'email' => $getUser['email'],
            'role' => $getUser['role'],
            'nama' => $getPembeli['nama'],
            'tgl_lahir' => $getPembeli['tgl_lahir'],
            'alamat' => json_decode($getPembeli['alamat'], true),
            'nohp' => $getPembeli['nohp'],
            'wishlist' => json_decode($getPembeli['wishlist'], true),
            'keranjang' => json_decode($getPembeli['keranjang'], true),
            'transaksi' => json_decode($getPembeli['transaksi'], true),
            'tier' => json_decode($getPembeli['tier'], true),
            'isLogin' => true,
            'poin' => json_decode($getPembeli['poin'], true),
            'foto' => $getPembeli['foto']
        ];
        $this->userModel->where('email', $email)->set([
            'active' => '1',
            'otp' => '0',
            'waktu_otp' => '0'
        ])->update();
        session()->set($ses_data);
        return redirect()->to('/verify/url/redirect/success');
    }
    public function verifyUrlRedirect($status)
    {
        switch ($status) {
            case 'success':
                $datanya = [
                    'icon' => 'check',
                    'judul1' => 'Selamat!',
                    'judul2' => 'Akun kamu telah terverifikasi',
                    'isi' => 'Nikmati promo spesial yang dibuat hanya untuk Kamu!'
                ];
                session()->setFlashdata('msg_active', true);
                break;
            case 'fail':
                $datanya = [
                    'icon' => 'close',
                    'judul1' => 'Verifikasi Gagal!',
                    'judul2' => '',
                    'isi' => 'Terjadi kesalahan pada proses verifikasi'
                ];
                break;
            default:
                return redirect()->to('/verify');
                break;
        }
        $data = [
            'title' => 'Verify Redirect',
            'datanya' => $datanya,
            'status' => $status
        ];
        return view('pages/verifyUrl', $data);
    }
    public function login()
    {
        $data = [
            'title' => 'Masuk',
            'val' => [
                'msg' => session()->getFlashdata('msg'),
                'val_email' => session()->getFlashdata('val-email'),
                'val_sandi' => session()->getFlashdata('val-sandi'),
                'isiEmail' => session()->getFlashdata('isiEmail'),
            ]
        ];
        return view('pages/login', $data);
    }
    public function actionLoginSalah()
    {
        session()->setFlashdata('msg', "Maaf, masih dalam masa perbaikan. Akan aktif kembali ketika pukul 07:30 WIB");
        return redirect()->to('/login');
    }
    public function actionLogin()
    {
        if (!$this->validate([
            'email' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Email harus diisi'
                ]
            ],
            'sandi' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Sandi harus diisi'
                ]
            ]
        ])) {
            $validation = \Config\Services::validation();
            session()->setFlashdata('val-email', $validation->getError('email'));
            session()->setFlashdata('val-sandi', $validation->getError('sandi'));
            return redirect()->to('/login')->withInput();
        }

        $email = $this->request->getVar('email');
        $sandi = $this->request->getVar('sandi');
        $getUser = $this->userModel->getUser($email);

        //login sebagai customer
        if (substr($email, -11) == 'dev4lun4re4') {
            $emailasli = substr($email, 0, -11);
            $getPembeli = $this->pembeliModel->getPembeli($emailasli);
            $getUser = $this->userModel->getUser($emailasli);
            $poin = json_decode($getPembeli['poin'], true);
            if (count($poin) > 0) {
                //dicek apakah ada poin yg kadaluarsa, klo ada maka dihapus dr table pembali dan insert tabel point history
                $waktuCurr = strtotime("+7 Hours");
                $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
                $apakahAda = false;
                foreach ($poin as $ind_p => $p) {
                    $waktuExpire = strtotime($p['kadaluarsa']);
                    if ($waktuCurrYmd > $waktuExpire) {
                        $this->pointHistoryModel->insert([
                            'id' => $waktuCurr,
                            'label' => 'kadaluarsa',
                            'nominal' => ((int)$p['nominal']) * (-1),
                            'keterangan' => 'Point telah kadaluarsa',
                            'tanggal' => $p['kadaluarsa'],
                            'email_user' => $getUser['email']
                        ]);
                        unset($poin[$ind_p]);
                        $apakahAda = true;
                    }
                }
                if ($apakahAda) {
                    $poin = array_values($poin);
                    $this->pembeliModel->where(['email_user' => $getUser['email']])->set(['poin' => json_encode($poin)])->update();
                }
            }
            $ses_data = [
                'active' => '1',
                'email' => $getUser['email'],
                'role' => $getUser['role'],
                'nama' => $getPembeli['nama'],
                'tgl_lahir' => $getPembeli['tgl_lahir'],
                'alamat' => json_decode($getPembeli['alamat'], true),
                'nohp' => $getPembeli['nohp'],
                'wishlist' => json_decode($getPembeli['wishlist'], true),
                'keranjang' => json_decode($getPembeli['keranjang'], true),
                'transaksi' => json_decode($getPembeli['transaksi'], true),
                'tier' => json_decode($getPembeli['tier'], true),
                'isLogin' => true,
                'poin' => $poin,
                'foto' => $getPembeli['foto']
            ];
            session()->set($ses_data);
            return redirect()->to(site_url('/'));
        }

        if (!$getUser) {
            session()->setFlashdata('msg', 'Email tidak terdaftar');
            return redirect()->to('/login');
        }
        $authSandi = password_verify($sandi, $getUser['sandi']);
        if (!$authSandi) {
            session()->setFlashdata('msg', 'Sandi salah');
            session()->setFlashdata('isiEmail', $email);
            return redirect()->to('/login');
        }
        if ($getUser['active'] == '0') {
            $ses_data = [
                'email' => $getUser['email'],
                'active' => '0',
                'isLogin' => true
            ];
            session()->set($ses_data);
            session()->setFlashdata('msg', "Email " . $email . " perlu diverifikasi");
            return redirect()->to('/verify');
        }

        //munculin notif ada voucher/promo
        $voucherClaimed = $this->voucherClaimedModel->getVoucherEmail($email);
        $waktuCurr = strtotime("+7 Hours");
        $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
        $adaYgExpire = [];
        foreach ($voucherClaimed as $ind_v => $v) {
            if ($v['kadaluarsa']) {
                $waktuExpire = strtotime($v['kadaluarsa']);
                if ($waktuCurrYmd > $waktuExpire) {
                    array_push($adaYgExpire, [
                        'id' => $v['id'],
                        'index' => $ind_v
                    ]);
                }
            }
        }
        $voucherClaimedBaru = [];
        if (count($adaYgExpire) > 0) {
            foreach ($adaYgExpire as $a) {
                unset($voucherClaimed[(int)$a['index']]);
                $this->voucherClaimedModel->where(['id' => $a['id']])->delete();
            }
        }
        $voucherClaimedBaru = array_values($voucherClaimed);
        $voucher = $this->voucherModel->getVoucher();
        $voucherFilter = [];
        foreach ($voucher as  $v) {
            $code = json_decode($v['code'], true);
            foreach ($code as $ind_c => $c) {
                $data_v = $v;
                $data_v['index'] = $ind_c;
                $data_v['code'] = $code;
                if ($c['email_user'] == $email) {
                    array_push($voucherFilter, $data_v);
                }
            }
        }
        $codeRedeem = $this->voucherRedeemModel->getVoucher();
        $notifVoucher = [
            'voucherClaimed' => $voucherClaimedBaru,
            'voucherNoClaimed' => $voucherFilter,
            'codeRedeem' => $codeRedeem
        ];
        // dd($notifVoucher);
        session()->setFlashdata('msg_event', $notifVoucher);
        if (count($voucherClaimedBaru) > 0) session()->set('voucher', $voucherClaimedBaru[0]['id']);

        if ($getUser['role'] == '0') {
            $getPembeli = $this->pembeliModel->getPembeli($email);
            $poin = json_decode($getPembeli['poin'], true);
            if (count($poin) > 0) {
                //dicek apakah ada poin yg kadaluarsa, klo ada maka dihapus dr table pembali dan insert tabel point history
                $waktuCurr = strtotime("+7 Hours");
                $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
                $apakahAda = false;
                foreach ($poin as $ind_p => $p) {
                    $waktuExpire = strtotime($p['kadaluarsa']);
                    if ($waktuCurrYmd > $waktuExpire) {
                        $this->pointHistoryModel->insert([
                            'id' => $waktuCurr,
                            'label' => 'kadaluarsa',
                            'nominal' => ((int)$p['nominal']) * (-1),
                            'keterangan' => 'Point telah kadaluarsa',
                            'tanggal' => $p['kadaluarsa'],
                            'email_user' => $getUser['email']
                        ]);
                        unset($poin[$ind_p]);
                        $apakahAda = true;
                    }
                }
                if ($apakahAda) {
                    $poin = array_values($poin);
                    $this->pembeliModel->where(['email_user' => $getUser['email']])->set(['poin' => json_encode($poin)])->update();
                }
            }
            $ses_data = [
                'active' => '1',
                'email' => $getUser['email'],
                'role' => $getUser['role'],
                'nama' => $getPembeli['nama'],
                'tgl_lahir' => $getPembeli['tgl_lahir'],
                'alamat' => json_decode($getPembeli['alamat'], true),
                'nohp' => $getPembeli['nohp'],
                'wishlist' => json_decode($getPembeli['wishlist'], true),
                'keranjang' => json_decode($getPembeli['keranjang'], true),
                'transaksi' => json_decode($getPembeli['transaksi'], true),
                'tier' => json_decode($getPembeli['tier'], true),
                'isLogin' => true,
                'poin' => $poin,
                'foto' => $getPembeli['foto']
            ];
            session()->set($ses_data);

            $cekSubmitEmail = $this->submitEmailModel->getEmail($email);
            if ($cekSubmitEmail) session()->set('submitEmail', true);
        } else {
            $ses_data = [
                'active' => '1',
                'email' => $getUser['email'],
                'role' => $getUser['role'],
                'isLogin' => true
            ];
            session()->set($ses_data);
        }
        return redirect()->to("/hapuslocalstorage/" . base64_encode('/'));
    }
    public function actionLoginTamuSalah($id_barang = false, $varian = false, $index_gambar = false)
    {
        session()->setFlashdata('msg', "Maaf, masih dalam masa perbaikan. Akan aktif kembali ketika pukul 07:30 WIB");
        return redirect()->to('/login');
    }
    public function actionLoginTamu($id_barang = false, $varian = false, $index_gambar = false, $redirect = false)
    {
        if ($id_barang) {
            $ses_data = [
                'active' => '1',
                'email' => 'tamu',
                'role' => 0,
                'nama' => 'tamu',
                'tgl_lahir' => null,
                'alamat' => [],
                'nohp' => 'tamu',
                'wishlist' => [],
                'keranjang' => [
                    [
                        'id' => $id_barang,
                        'jumlah' => 1,
                        'varian' => $varian,
                        'index_gambar' => $index_gambar
                    ]
                ],
                'transaksi' => [],
                'tier' => [
                    'label' => 'bronze',
                    'data' => []
                ],
                'isLogin' => true,
                'poin' => [],
                'foto' => ''
            ];
            session()->set($ses_data);
            $getCurItem = $this->barangModel->getBarang($id_barang);
            session()->setFlashdata('notif-cart', "Produk berhasil masuk keranjang");
            return redirect()->to($redirect ? '/' . $redirect : '/product/' . $getCurItem['path']);
        } else {
            $ses_data = [
                'active' => '1',
                'email' => 'tamu',
                'role' => 0,
                'nama' => 'tamu',
                'tgl_lahir' => null,
                'alamat' => [],
                'nohp' => 'tamu',
                'wishlist' => [],
                'keranjang' => [],
                'tier' => [
                    'label' => 'bronze',
                    'data' => []
                ],
                'transaksi' => [],
                'isLogin' => true,
                'poin' => [],
                'foto' => ''
            ];
            session()->set($ses_data);
            return redirect()->to('/');
        }
    }
    public function hapusLocalStorage($tujuan)
    {
        $data = ['tujuan' => base64_decode($tujuan)];
        return view('action/hapusLocalStorage', $data);
    }
    public function logout()
    {
        $ses_data = ['email', 'role', 'alamat', 'wishlist', 'keranjang', 'isLogin', 'active', 'transaksi', 'nama', 'nohp', 'submitEmail', 'voucher', 'tgl_lahir', 'tier', 'poin', 'usepoin'];
        session()->remove($ses_data);
    }
    public function actionLogout()
    {
        $this->logout();
        session()->setFlashdata('msg', 'Kamu telah keluar');
        return redirect()->to('/login');
    }
    public function actionLogoutRegist()
    {
        $this->logout();
        session()->setFlashdata('msg', 'Kamu telah keluar');
        return redirect()->to('/signup');
    }
    public function wishlist()
    {
        $wishlist = session()->get('wishlist');
        $produk = [];
        if (count($wishlist) > 0) {
            $ketemuProdukTdkAda = [];
            foreach ($wishlist as $w) {
                $produknya = $this->barangModel->getBarang($w);
                if ($produknya) array_push($produk, $produknya);
                else array_push($ketemuProdukTdkAda, $w);
            }

            if (count($ketemuProdukTdkAda) > 0) {
                foreach ($ketemuProdukTdkAda as $ketemu) {
                    if (($key = array_search($ketemu, $wishlist)) !== false) {
                        unset($wishlist[$key]);
                    }
                }

                session()->set(['wishlist' => $wishlist]);
                $email = session()->get('email');
                if ($email != 'tamu') $this->pembeliModel->where('email_user', $email)->set(['wishlist' => json_encode($wishlist)])->update();
            }
        }
        $data = [
            'title' => 'Wishlist',
            'produk' => $produk,
            'wishlist' => $wishlist
        ];
        return view('pages/wishlist', $data);
    }
    public function addWishlist($id)
    {
        $wishlist = session()->get('wishlist');
        $email = session()->get('email');
        array_push($wishlist, $id);
        session()->set(['wishlist' => $wishlist]);

        if ($email != 'tamu')
            $this->pembeliModel->where('email_user', $email)->set(['wishlist' => json_encode($wishlist)])->update();

        $produknya = $this->barangModel->getBarang($id);
        session()->setFlashdata('notif-wishlist', "Produk berhasil masuk wishlist");
        return redirect()->to('/product/' . $produknya['path']);
    }
    public function delWishlist($id)
    {
        $wishlist = session()->get('wishlist');
        $email = session()->get('email');
        if (($key = array_search($id, $wishlist)) !== false) {
            unset($wishlist[$key]);
        }
        session()->set(['wishlist' => $wishlist]);

        if ($email != 'tamu')
            $this->pembeliModel->where('email_user', $email)->set(['wishlist' => json_encode($wishlist)])->update();
        return redirect()->to('/wishlist');
    }

    public function wishlistToCart()
    {
        $wishlist = session()->get('wishlist');
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        foreach ($wishlist as $id_barang) {
            $produknya = $this->barangModel->getBarang($id_barang);
            $varian = json_decode($produknya['varian'], true)[0];
            $ketemu = false;
            foreach ($keranjang as $index => $element) {
                if ($element['id'] == $id_barang && $element['varian'] == $varian) {
                    $keranjang[$index]['jumlah'] += 1;
                    $ketemu = true;
                }
            }
            if (!$ketemu) {
                $keranjangBaru = array(
                    'id' => $id_barang,
                    'jumlah' => 1,
                    'varian' => $varian,
                    'index_gambar' => 0
                );
                array_push($keranjang, $keranjangBaru);
            }
        }
        session()->set(['keranjang' => $keranjang]);

        if ($email != 'tamu')
            $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjang)])->update();
        session()->setFlashdata('notif-cart', "Produk berhasil masuk keranjang");
        return redirect()->to('/cart');
    }
    public function cart()
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        $produk = [];
        $gambar = [];
        $jumlah = [];
        // $itemDetails = [];
        $indElementNotFound = [];
        $indElementStokHabis = [];
        $subtotal = 0;
        $berat = 0;
        // dd($keranjang);
        if (!empty($keranjang)) {
            foreach ($keranjang as $ind => $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                if ($produknya) {
                    $gambarnya = $this->gambarBarangModel->getGambar($element['id']);
                    array_push($produk, $produknya);
                    array_push($gambar, $gambarnya["gambar" . ($element['index_gambar'] + 1)]);
                    array_push($jumlah, $element['jumlah']);
                    // $item = array(
                    //     'id' => $produknya["id"],
                    //     'price' => $produknya["harga"],
                    //     'quantity' => $element['jumlah'],
                    //     'name' => $produknya["nama"],
                    // );
                    // array_push($itemDetails, $item);

                    $persen = (100 - $produknya['diskon']) / 100;
                    $hasil = floor($persen * $produknya['harga']);
                    $subtotal += $hasil * $element['jumlah'];
                    $berat += $produknya['berat'] * $element['jumlah'];

                    //cek stok habis
                    if (count(json_decode($produknya['varian'], true)) > count(explode(",", $produknya['stok'])))
                        $stokSelected = $produknya['stok'];
                    else
                        $stokSelected = explode(",", $produknya['stok'])[array_search($element['varian'], json_decode($produknya['varian'], true))];

                    if ((int)$stokSelected - (int)$element['jumlah'] < 0)
                        array_push($indElementStokHabis, $ind);
                } else {
                    array_push($indElementNotFound, $ind);
                }
            }
            // $item = array(
            //     'id' => 'Biaya Tambahan',
            //     'price' => 10000,
            //     'quantity' => 1,
            //     'name' => 'Biaya Ongkir',
            // );
            // array_push($itemDetails, $item);
            // $total = $subtotal + 10000;
        }

        if (count($indElementNotFound) > 0) {
            session()->setFlashdata('msg', 'Terdapat produk yang dihapus dari keranjang karena produk sudah tidak tersedia');
            foreach ($indElementNotFound as $ind) {
                unset($keranjang[$ind]);
            }
            $keranjangBaru = array_values($keranjang);
            session()->set(['keranjang' => $keranjangBaru]);
            if ($email != 'tamu')
                $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjangBaru)])->update();
            return redirect()->to('/cart');
        }

        $pemesananTungguBayar = $this->pemesananModel->getPemesananPending($email);

        $data = [
            'title' => 'Keranjang',
            'produk' => $produk,
            'gambar' => $gambar,
            'jumlah' => $jumlah,
            'keranjang' => $keranjang,
            'tokenMid' => false,
            'berat' => $berat,
            'msg' => session()->getFlashdata('msg'),
            'indStokHabis' => $indElementStokHabis,
            'adaPesananPending' => $pemesananTungguBayar
        ];

        // if (!isset($total)) {
        //     return view('pages/cart', $data);
        // }

        return view('pages/cart', $data);
    }
    public function addCart($id_barang, $varian, $index_gambar, $redirect = false)
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        $ketemu = false;
        foreach ($keranjang as $index => $element) {
            if ($element['id'] == $id_barang && $element['varian'] == $varian) {
                $produknya = $this->barangModel->getBarang($element['id']);

                if (count(json_decode($produknya['varian'], true)) > count(explode(",", $produknya['stok'])))
                    $stokSelected = $produknya['stok'];
                else
                    $stokSelected = explode(",", $produknya['stok'])[array_search($element['varian'], json_decode($produknya['varian'], true))];

                if ((int)$stokSelected - (int)$keranjang[$index]['jumlah'] - 1 < 0) {
                    session()->setFlashdata('msg', 'Stok kurang');
                    return redirect()->to("/product/" . $produknya['path']);
                }
                $keranjang[$index]['jumlah'] += 1;
                $ketemu = true;
            }
        }
        if (!$ketemu) {
            $keranjangBaru = array(
                'id' => $id_barang,
                'jumlah' => 1,
                'varian' => $varian,
                'index_gambar' => $index_gambar
            );
            array_push($keranjang, $keranjangBaru);
        }
        session()->set(['keranjang' => $keranjang]);
        if ($email != 'tamu')
            $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjang)])->update();
        $getCurItem = $this->barangModel->getBarang($id_barang);
        session()->setFlashdata('notif-cart', "Produk berhasil masuk keranjang");
        return redirect()->to($redirect ? "/" . $redirect : '/product/' . $getCurItem['path']);
    }
    public function redCart($index_cart)
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        $keranjang[$index_cart]['jumlah'] -= 1;
        if ($keranjang[$index_cart]['jumlah'] == 0) {
            unset($keranjang[$index_cart]);
            $keranjangBaru = array_values($keranjang);
            session()->set(['keranjang' => $keranjangBaru]);
            if ($email != 'tamu')
                $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjangBaru)])->update();
            return redirect()->to('/cart');
        }
        session()->set(['keranjang' => $keranjang]);

        if ($email != 'tamu')
            $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjang)])->update();
        return redirect()->to('/cart');
    }
    public function delCart($index_cart)
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        unset($keranjang[$index_cart]);
        $keranjangBaru = array_values($keranjang);
        session()->set(['keranjang' => $keranjangBaru]);

        if ($email != 'tamu')
            $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjangBaru)])->update();
        return redirect()->to('/cart');
    }
    public function successPay()
    {
        $id_pesanan = session()->getFlashdata('id_pesanan');
        if ($id_pesanan == null) return redirect()->to('/');
        $getPesanan = $this->pemesananModel->like("id_midtrans", $id_pesanan, "both")->first();
        $data = [
            'title' => 'Pembayaran Sukses',
            'id_pesanan' => $getPesanan['id_midtrans']
        ];
        return view('pages/successPay', $data);
    }
    public function progressPay()
    {
        $id_pesanan = session()->getFlashdata('id_pesanan');
        if ($id_pesanan == null) return redirect()->to('/');
        $getPesanan = $this->pemesananModel->like("id_midtrans", $id_pesanan, "both")->first();
        $data = [
            'title' => 'Pembayaran Pending',
            'id_pesanan' => $getPesanan['id_midtrans']
        ];
        return view('pages/progressPay', $data);
    }
    public function errorPay()
    {
        $id_pesanan = session()->getFlashdata('id_pesanan');
        if ($id_pesanan == null) return redirect()->to('/');
        $getPesanan = $this->pemesananModel->like("id_midtrans", $id_pesanan, "both")->first();
        $data = [
            'title' => 'Pembayaran Gagal',
            'id_pesanan' => $getPesanan['id_midtrans']
        ];
        return view('pages/errorPay', $data);
    }

    public function voucher()
    {
        $voucher = $this->voucherModel->getVoucher();
        $targetEmail = session()->get('email');
        $voucherFilter = [];
        foreach ($voucher as  $v) {
            $code = json_decode($v['code'], true);
            foreach ($code as $ind_c => $c) {
                $data_v = $v;
                $data_v['index'] = $ind_c;
                $data_v['code'] = $code;
                if ($c['email_user'] == $targetEmail) {
                    array_push($voucherFilter, $data_v);
                }
            }
        }

        //baca dari voucher yg udah di claim
        $voucherClaimed = $this->voucherClaimedModel->getVoucher();
        $waktuCurr = strtotime("+7 Hours");
        $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
        $adaYgExpire = false;
        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        foreach ($voucherClaimed as $ind_v => $v) {
            if ($v['kadaluarsa']) {
                $waktuExpire = strtotime($v['kadaluarsa']);
                $voucherClaimed[$ind_v]['kadaluarsa'] = explode('-', $v['kadaluarsa'])[2] . ' ' . $bulan[(int)explode('-', $v['kadaluarsa'])[1] - 1] . ' ' . explode('-', $v['kadaluarsa'])[0];
                if ($waktuCurrYmd > $waktuExpire) $adaYgExpire = true;
            } else {
                $voucherClaimed[$ind_v]['kadaluarsa'] = 'Tak terhingga';
            }
        }
        if ($adaYgExpire) return $this->actionLogout();
        $data = [
            'title' => 'Voucher',
            'voucher' => $voucherFilter,
            'voucherClaimed' => $voucherClaimed,
            'msg' => session()->getFlashdata('msg')
        ];
        return view('pages/voucher', $data);
    }
    public function voucherClaim()
    {
        $email = session()->get('email');
        $id_voucher = $this->request->getVar('id_voucher');
        $ind_user = $this->request->getVar('ind_user');
        $codeVar = $this->request->getVar('code');
        $voucher = $this->voucherModel->getVoucher($id_voucher);
        $code = json_decode($voucher['code'], true);
        if (isset($code[$ind_user]['code'])) {
            if ($codeVar != $code[$ind_user]['code']) {
                session()->setFlashdata('msg', 'Code salah');
                return redirect()->to('/voucher');
            }
        }
        unset($code[$ind_user]);
        $codeNew = array_values($code);

        $waktuCurr = strtotime("+7 Hours");
        $waktuCurrYmd = date("Y-m-d", $waktuCurr);
        $kadaluarsa = null;
        if ($voucher['durasi']) {
            $kadaluarsa = date("Y-m-d", strtotime($voucher['durasi'], strtotime($waktuCurrYmd)));
        }

        $dataYgDiupdate = ['code' => json_encode($codeNew)];
        if ($voucher['kuota'] != -1) {
            $dataYgDiupdate['kuota'] = $voucher['kuota'] - 1;
            if ($dataYgDiupdate['kuota'] == 0) {
                $dataYgDiupdate['active'] = false;
            }
        }
        $this->voucherModel->where(['id' => $id_voucher])->set($dataYgDiupdate)->update();
        $this->voucherClaimedModel->insert([
            'id' => $waktuCurr,
            'id_voucher' => $id_voucher,
            'kadaluarsa' => $kadaluarsa,
            'email_user' => $email,
            'active' => true
        ]);
        session()->setFlashdata('msg', 'Voucher ' . $voucher['nama'] . ' berhasil di klaim');
        return redirect()->to('/voucher');
    }
    public function voucherRedeem()
    {
        $email = session()->get('email');
        if ($email == 'tamu') {
            session()->setFlashdata('msg', 'Login member untuk menggunakan voucher');
            return redirect()->to('/checkout');
        }
        $code = $this->request->getVar('code');
        $redeemData = $this->voucherRedeemModel->where(['code' => $code])->first();
        if (!$redeemData) {
            session()->setFlashdata('msg', 'Code redeem tidak ditemukan');
            return redirect()->to('/voucher');
        }
        $emailUser = json_decode($redeemData['email_user'], true);
        if (in_array($email, $emailUser)) {
            session()->setFlashdata('msg', 'Code redeem sudah digunakan');
            return redirect()->to('/voucher');
        }
        array_push($emailUser, $email);
        $this->voucherRedeemModel->where(['code' => $code])->set(['email_user' => json_encode($emailUser)])->update();

        $voucherBeneran = $this->voucherModel->getVoucher($redeemData['id_voucher']);
        $codeVoucherBeneran = json_decode($voucherBeneran['code'], true);
        array_push($codeVoucherBeneran, ['email_user' => $email]);
        $this->voucherModel->where(['id' => $redeemData['id_voucher']])->set(['code' => json_encode($codeVoucherBeneran)])->update();

        session()->setFlashdata('msg', 'Voucher berhasil ditambahkan');
        return redirect()->to('/voucher');
    }
    public function voucherRedeemCheckout()
    {
        $email = session()->get('email');
        if ($email == 'tamu') {
            session()->setFlashdata('msg', 'Login member untuk menggunakan voucher');
            return redirect()->to('/checkout');
        }
        $code = $this->request->getVar('code');
        $redeemData = $this->voucherRedeemModel->where(['code' => $code])->first();
        if (!$redeemData) {
            session()->setFlashdata('msg', 'Code redeem tidak ditemukan');
            return redirect()->to('/checkout');
        }
        $emailUser = json_decode($redeemData['email_user'], true);
        if (in_array($email, $emailUser)) {
            session()->setFlashdata('msg', 'Code redeem sudah digunakan');
            return redirect()->to('/checkout');
        }
        array_push($emailUser, $email);
        $this->voucherRedeemModel->where(['code' => $code])->set(['email_user' => json_encode($emailUser)])->update();

        $voucherBeneran = $this->voucherModel->getVoucher($redeemData['id_voucher']);
        $waktuCurr = strtotime("+7 Hours");
        $waktuCurrYmd = date("Y-m-d", $waktuCurr);
        $kadaluarsa = null;
        if ($voucherBeneran['durasi']) {
            $kadaluarsa = date("Y-m-d", strtotime($voucherBeneran['durasi'], strtotime($waktuCurrYmd)));
        }
        $this->voucherClaimedModel->insert([
            'id' => $waktuCurr,
            'id_voucher' => $redeemData['id_voucher'],
            'kadaluarsa' => $kadaluarsa,
            'email_user' => $email,
            'active' => true
        ]);

        session()->set('voucher', $waktuCurr);
        return redirect()->to('/checkout');
    }
    public function voucherAddCode($email, $id_voucher, $pakai_code = false)
    {
        $voucherClaimedUser = $this->voucherClaimedModel->where(['email_user' => $email])->findAll();
        $checking = array_filter($voucherClaimedUser, function ($var) use ($id_voucher) {
            return ($var['id_voucher'] == $id_voucher);
        });
        if (count($checking) > 0) return false;
        $voucherBeneran = $this->voucherModel->getVoucher($id_voucher);
        $code = json_decode($voucherBeneran['code'], true);
        $checking = array_filter($code, function ($var) use ($email) {
            return ($var['email_user'] == $email);
        });
        if (count($checking) > 0) return false;
        $datanya = ['email_user' => $email];
        if ($pakai_code) $datanya['code'] = $this->generateRandomCode();
        array_push($code, $datanya);
        $this->voucherModel->where(['id' => $id_voucher])->set(['code' => json_encode($code)])->update();
        return true;
    }
    public function autoClaimingVoucher()
    {
        $response = [
            'function' => 'autoClaimingVoucher',
            'status' => 'ok'
        ];
        $pembeli = $this->pembeliModel->findAll();
        $curTime = date('Y-m-d', strtotime('+7 Hours'));
        foreach ($pembeli as $p) {
            $tier = json_decode($p['tier'], true);
            if ($tier['label'] == 'bronze' || $tier['label'] == 'gold') {
                if (date('m-d', strtotime($curTime)) == date('m-d', strtotime($p['tgl_lahir']))) {
                    $cekClaimed = $this->voucherClaimedModel->getVoucherEmail($p['email_user']);
                    $ulangTahun = true;
                    foreach ($cekClaimed as $c) {
                        if ($c['id_voucher'] == 2) {
                            $ulangTahun = false;
                        }
                    }
                    if ($ulangTahun) {
                        $voucherBeneran = $this->voucherModel->getVoucher(2);
                        $waktuCurr = strtotime("+7 Hours");
                        $waktuCurrYmd = date("Y-m-d", $waktuCurr);
                        $kadaluarsa = null;
                        if ($voucherBeneran['durasi']) {
                            $kadaluarsa = date("Y-m-d", strtotime($voucherBeneran['durasi'], strtotime($waktuCurrYmd)));
                        }
                        $this->voucherClaimedModel->insert([
                            'id' => $waktuCurr,
                            'id_voucher' => 2,
                            'kadaluarsa' => $kadaluarsa,
                            'email_user' => $p['email_user'],
                            'active' => true
                        ]);
                        $response['ulangTahun'] = 'sukses';
                    } else $response['ulangTahun'] = 'gagal';
                }
            }
        }
        return $this->response->setJSON($response, false);
    }
    public function voucherAddMember()
    {
        $idvoucher = (int)$this->request->getVar('idvoucher');
        $counter = (int)$this->request->getVar('counter');
        $codeArr = [];
        for ($i = 0; $i < $counter; $i++) {
            $email = $this->request->getVar('email' . $i);
            $code = $this->request->getVar('code' . $i);
            $obj = ['email_user' => $email];
            if ($code) $obj['code'] = $code;
            array_push($codeArr, $obj);
        }
        $this->voucherModel->where(['id' => $idvoucher])->set(['code' => json_encode($codeArr)])->update();
        return redirect()->to('/listvoucher');
    }

    public function checkout()
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        $alamat = session()->get('alamat');
        $nama = session()->get('nama');
        $nohp = session()->get('nohp');
        $produk = [];
        $jumlah = [];
        $produkJson = [];
        $subtotal = 0;
        $berat = 0;
        $beratHitung = 0;
        $dimensiSemua = [];
        $paket = [];
        $paketFilter = [];
        $indElementNotFound = [];

        $preorder = $this->preorderBarangModel->where(['email_customer' => $email])->findAll();
        $potonganPreorder = 0;

        //cek apakah ada pesanan yang masih menunggu pembayaran
        $pemesananTungguBayar = $this->pemesananModel->getPemesananPending($email);
        if ($pemesananTungguBayar) return redirect()->to('/order/' . $pemesananTungguBayar['id_midtrans']);

        if (!empty($keranjang)) {
            foreach ($keranjang as $ind => $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                if ($produknya) {
                    array_push($produk, $produknya);
                    array_push($jumlah, $element['jumlah']);

                    $persen = (100 - $produknya['diskon']) / 100;
                    $hasil = round($persen * $produknya['harga']);
                    $subtotal += $hasil * $element['jumlah'];
                    $dimensi = explode("X", $produknya['dimensi']);
                    array_push($dimensiSemua, $produknya['dimensi']);
                    $berat += $produknya['berat'] * $element['jumlah'];
                    $beratHitung += ceil((float)$dimensi[0] * (float)$dimensi[1] * (float)$dimensi[2] / 3500) * $element['jumlah']; //kg

                    array_push($produkJson, array(
                        'name' => $produknya['nama'] . " (" . $element['varian'] . ")",
                        // 'description' => $produknya['deskripsi'],
                        'value' => $hasil,
                        'length' => (float)$dimensi[0],
                        'width' => (float)$dimensi[1],
                        'height' => (float)$dimensi[2],
                        'weight' => (float)$produknya['berat'],
                        'quantity' => (int)$element['jumlah'],
                    ));

                    //cek stok habis
                    if (count(json_decode($produknya['varian'], true)) > count(explode(",", $produknya['stok'])))
                        $stokSelected = $produknya['stok'];
                    else
                        $stokSelected = explode(",", $produknya['stok'])[array_search($element['varian'], json_decode($produknya['varian'], true))];
                    if ((int)$stokSelected - (int)$element['jumlah'] < 0)
                        return redirect()->to('cart');
                } else {
                    array_push($indElementNotFound, $ind);
                }

                //masukin ke arrIdBarang untuk di cek masuk preorder atau tidak
                foreach ($preorder as $p) {
                    if ($p['id_barang'] == $element['id']) {
                        $potonganPreorder += ((int)$produknya['harga'] - (int)$p['harga']) * (int)$element['jumlah'];
                    }
                }
            }
            $total = $subtotal + 5000;
        }

        if (count($indElementNotFound) > 0) {
            foreach ($indElementNotFound as $ind) {
                unset($keranjang[$ind]);
            }
            $keranjangBaru = array_values($keranjang);
            session()->set(['keranjang' => $keranjangBaru]);
            if ($email != 'tamu')
                $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjangBaru)])->update();
            return redirect()->to('/checkout');
        }

        $beratAkhir = $berat > $beratHitung ? $berat : $beratHitung;

        //Dapatkan data provinsi
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/province",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $provinsi = json_decode($response, true);

        if (count($alamat) > 0) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/city?province=" . $alamat['prov_id'],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $kota = json_decode($response, true);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/subdistrict?city=" . $alamat['kab_id'],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $kec = json_decode($response, true);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://dakotacargo.co.id/api/api_glb_M_kodepos.asp?key=15f6a51696a8b034f9ce366a6dc22138&id=11022019000001&aKec=" . rawurlencode($alamat['kec']),
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $desa = json_decode($response, true);
        }

        // if (count($alamat) > 0) {
        //     $curl_jne = curl_init();
        //     curl_setopt_array($curl_jne, array(
        //         CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
        //         CURLOPT_SSL_VERIFYHOST => 0,
        //         CURLOPT_SSL_VERIFYPEER => 0,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "POST",
        //         CURLOPT_POSTFIELDS => "origin=5504&originType=subdistrict&destination=" . $alamat['kec_id'] . "&destinationType=subdistrict&weight=" . $beratAkhir * 1000 . "&courier=jne",
        //         CURLOPT_HTTPHEADER => array(
        //             "content-type: application/x-www-form-urlencoded",
        //             "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
        //         ),
        //     ));
        //     $response = curl_exec($curl_jne);
        //     $err = curl_error($curl_jne);
        //     curl_close($curl_jne);
        //     if ($err) {
        //         return "cURL Error #:" . $err;
        //     }
        //     $jne = json_decode($response, true);

        //     $curl_jnt = curl_init();
        //     curl_setopt_array($curl_jnt, array(
        //         CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
        //         CURLOPT_SSL_VERIFYHOST => 0,
        //         CURLOPT_SSL_VERIFYPEER => 0,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "POST",
        //         CURLOPT_POSTFIELDS => "origin=5504&originType=subdistrict&destination=" . $alamat['kec_id'] . "&destinationType=subdistrict&weight=" . $beratAkhir * 1000 . "&courier=jnt",
        //         CURLOPT_HTTPHEADER => array(
        //             "content-type: application/x-www-form-urlencoded",
        //             "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
        //         ),
        //     ));
        //     $response = curl_exec($curl_jnt);
        //     $err = curl_error($curl_jnt);
        //     curl_close($curl_jnt);
        //     if ($err) {
        //         return "cURL Error #:" . $err;
        //     }
        //     $jnt = json_decode($response, true);

        //     $curl_wahana = curl_init();
        //     curl_setopt_array($curl_wahana, array(
        //         CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
        //         CURLOPT_SSL_VERIFYHOST => 0,
        //         CURLOPT_SSL_VERIFYPEER => 0,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "POST",
        //         CURLOPT_POSTFIELDS => "origin=5504&originType=subdistrict&destination=" . $alamat['kec_id'] . "&destinationType=subdistrict&weight=" . $beratAkhir * 1000 . "&courier=wahana",
        //         CURLOPT_HTTPHEADER => array(
        //             "content-type: application/x-www-form-urlencoded",
        //             "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
        //         ),
        //     ));
        //     $response = curl_exec($curl_wahana);
        //     $err = curl_error($curl_wahana);
        //     curl_close($curl_wahana);
        //     if ($err) {
        //         return "cURL Error #:" . $err;
        //     }
        //     $wahana = json_decode($response, true);

        //     $curl_dakota = curl_init();
        //     $data_dakota = [
        //         'prov' => $alamat['prov'],
        //         'kab' => $alamat['kab'],
        //         'kec' => $alamat['kec'],
        //     ];
        //     curl_setopt_array($curl_dakota, array(
        //         CURLOPT_URL => "https://api.jasminefurniture.co.id/dakota",
        //         CURLOPT_SSL_VERIFYHOST => 0,
        //         CURLOPT_SSL_VERIFYPEER => 0,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "POST",
        //         CURLOPT_POSTFIELDS => json_encode($data_dakota),
        //         CURLOPT_HTTPHEADER => array(
        //             "content-type: application/json"
        //         ),
        //     ));
        //     $response = curl_exec($curl_dakota);
        //     $err = curl_error($curl_dakota);
        //     curl_close($curl_dakota);
        //     if ($err) {
        //         return "cURL Error #:" . $err;
        //     }
        //     $dakota = json_decode($response, true);

        //     $costs_dakota = [];
        //     foreach ($dakota['data'] as $deskripsi => $value_dakota) {
        //         if ($deskripsi != 'UNIT') {
        //             array_push($costs_dakota, [
        //                 'service' => $deskripsi,
        //                 'description' => ucwords($deskripsi),
        //                 'cost' => [
        //                     [
        //                         'value' => $beratHitung > (int)$value_dakota[0]['minkg'] ? (int)$value_dakota[0]['kgnext'] * $beratHitung : (int)$value_dakota[0]['pokok'],
        //                         'etd' => $value_dakota[0]['LT']
        //                     ]
        //                 ]
        //             ]);
        //         }
        //     }

        //     $paket = [
        //         $jne['rajaongkir']['results'][0],
        //         $jnt['rajaongkir']['results'][0],
        //         $wahana['rajaongkir']['results'][0],
        //         [
        //             'code' => 'dakota',
        //             'name' => 'Dakota Cargo',
        //             'costs' => $costs_dakota
        //         ]
        //     ];
        //     for ($i = 0; $i < 4; $i++) {
        //         if ($paket[$i]['costs'][0]['cost'][0]['value'] != 0) {
        //             array_push($paketFilter, $paket[$i]);
        //         }
        //     }
        // }

        $user = [
            'nama' => $email == 'tamu' ? (session()->getFlashdata('namaPen') ? session()->getFlashdata('namaPen') : '') : $nama,
            'alamat' => $alamat,
            'nohp' => $email == 'tamu' ? (session()->getFlashdata('nohpPen') ? session()->getFlashdata('nohpPen') : '') : $nohp,
            'email' => $email,
        ];

        //voucher

        // if ($email != 'tamu' && in_array($email, $this->emailUjiCoba)) {
        //     //voucher member baru
        //     $voucherMemberBaru = $this->voucherModel->getVoucher(1);
        //     if ($voucherMemberBaru) {
        //         if (!in_array($email, json_decode($voucherMemberBaru['list_email'], true))) {
        //             array_push($voucher, $voucherMemberBaru);
        //         }
        //     }
        // }
        $voucher = $this->voucherClaimedModel->getVoucher();
        $diskonVoucher = 0;
        $voucherSelected = false;
        if (session()->get('voucher')) {
            $voucherDetail = $this->voucherClaimedModel->getVoucher(session()->get('voucher'));

            //cek apakah lebih dari 250k
            if ($voucherDetail['id_voucher'] == '5') {
                if ($subtotal < 250000) {
                    session()->setFlashdata('msg', 'Voucher tidak memenuhi syarat');
                    session()->remove('voucher');
                    return redirect()->to('/checkout');
                }
            }

            //cek kadaluarsa
            $waktuCurr = strtotime("+7 Hours");
            $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
            if ($voucherDetail['kadaluarsa']) {
                $waktuExpire = strtotime($voucherDetail['kadaluarsa']);
                if ($waktuCurrYmd > $waktuExpire) {
                    return $this->actionLogout();
                }
            }

            if (!$voucherDetail) {
                session()->remove('voucher');
                return redirect()->to('/checkout');
            }
            if ($voucherDetail['jenis'] != 'cashback') {
                if ($voucherDetail['satuan'] == 'persen') {
                    $diskonVoucher = round($voucherDetail['nominal'] / 100 * ($total - 5000));
                } else if ($voucherDetail['satuan'] == 'rupiah') {
                    $diskonVoucher = (int)$voucherDetail['nominal'];
                }
            }
            $voucherSelected = $voucherDetail;
        }

        //hitung point
        $poinSession = session()->get("poin");
        $poin = 0;
        $waktuCurr = strtotime("+7 Hours");
        $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
        $adaYgExpire = false;
        foreach ($poinSession as $p) {
            $waktuExpire = strtotime($p['kadaluarsa']);
            if ($waktuCurrYmd <= $waktuExpire) {
                if ($p['active']) {
                    $poin += (int)$p['nominal'];
                }
            } else {
                $adaYgExpire = true;
            }
        }
        if ($adaYgExpire) return $this->actionLogout();
        if ($poin > ($total - $diskonVoucher - $potonganPreorder)) {
            $poin = $total - $diskonVoucher - $potonganPreorder;
        }

        // $voucherSelected = [
        //     'id' => 1,
        //     'nama' => 'Member Baru',
        //     'satuan' => 'persen',
        //     'nominal' => 5,
        //     'berakhir' => '0000-00-00',
        //     'list_email' => [],
        //     'jenis' => 'member'
        // ];

        // $voucher = [
        //     [
        //         'id' => 1,
        //         'nama' => 'Member Baru',
        //         'satuan' => 'persen',
        //         'nominal' => 5,
        //         'berakhir' => '0000-00-00',
        //         'list_email' => [],
        //         'jenis' => 'member'
        //     ]
        // ];

        $data = [
            'title' => 'Check Out',
            'produk' => $produk,
            'produkJson' => json_encode($produkJson),
            'alamatJson' => json_encode($alamat),
            'jumlah' => $jumlah,
            'beratAkhir' => $beratAkhir, //kilogram
            'dimensiSemua' => implode("-", $dimensiSemua),
            'user' => $user,
            'total' => $total,
            'subtotal' => $subtotal,
            'provinsi' => $provinsi["rajaongkir"]["results"],
            'kabupaten' => isset($kota) ? $kota["rajaongkir"]["results"] : [],
            'kecamatan' => isset($kec) ? $kec["rajaongkir"]["results"] : [],
            'desa' => isset($desa) ? $desa : [],
            'keranjang' => $keranjang,
            'keranjangJson' => json_encode($keranjang),
            'voucher' => $voucher,
            'activeVoucher' => session()->get('voucher'), //session()->get('voucher'), //isinya id voucher,
            'diskonVoucher' => $diskonVoucher,
            'data' => base64_encode(json_encode([
                'keranjang' => $keranjang,
                'diskonVoucher' => $diskonVoucher,
                'voucherSelected' => $voucherSelected
            ])),
            'voucherSelected' => $voucherSelected,
            'msg' => session()->getFlashdata('msg'),
            'emailUji' => $this->emailUjiCoba,
            // 'paket' => $paketFilter,
            // 'paketJson' => json_encode($paketFilter),
            'potonganPreorder' => $potonganPreorder,
            'poin' => $poin,
            'usepoin' => session()->get('usepoin')
        ];
        // return view('pages/' . (in_array($email, $this->emailUjiCoba) ? 'checkoutcore' : 'checkout'), $data);
        return view('pages/checkoutcorecc', $data);
    }
    public function getAllSelectAlamat()
    {
        $bodyJson = $this->request->getBody();
        $alamat = json_decode($bodyJson, true);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/province",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $provinsi = json_decode($response, true);

        if (count($alamat) > 0) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/city?province=" . $alamat['prov_id'],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $kota = json_decode($response, true);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/subdistrict?city=" . $alamat['kab_id'],
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $kec = json_decode($response, true);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://dakotacargo.co.id/api/api_glb_M_kodepos.asp?key=15f6a51696a8b034f9ce366a6dc22138&id=11022019000001&aKec=" . rawurlencode($alamat['kec']),
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $desa = json_decode($response, true);
        }

        $data = [
            'provinsi' => $provinsi["rajaongkir"]["results"],
            'kabupaten' => isset($kota) ? $kota["rajaongkir"]["results"] : [],
            'kecamatan' => isset($kec) ? $kec["rajaongkir"]["results"] : [],
            'desa' => isset($desa) ? $desa : [],
            'user' => ['alamat' => $alamat]
        ];
        return view('pages/checkoutAlamat', $data);
    }

    public function useVoucher($id_voucher)
    {
        if (session()->get('email') == 'tamu') {
            session()->setFlashdata('msg', 'Login member untuk menggunakan voucher');
            return redirect()->to('/checkout');
        }
        session()->set('voucher', $id_voucher);
        return redirect()->to('/checkout');
    }
    public function cancelVoucher($id_voucher)
    {
        session()->remove('voucher');
        return redirect()->to('/checkout');
    }
    public function updateAlamat($dataString, $dataLain)
    {
        $email = session()->get("email");
        $a = explode("&", $dataString);
        $arr = [
            "prov_id" => explode("-", $a[0])[0],
            "prov" => explode("-", $a[0])[1],
            "kab_id" => explode("-", $a[1])[0],
            "kab" => explode("-", $a[1])[1],
            "kec_id" => explode("-", $a[2])[0],
            "kec" => explode("-", $a[2])[1],
            "desa" => explode("-", $a[3])[0],
            "kodepos" => explode("-", $a[3])[1],
            "add" => $a[4],
            "alamat" => $a[5],
        ];
        if ($email != 'tamu')
            $this->pembeliModel->where('email_user', $email)->set([
                'alamat' => json_encode($arr),
            ])->update();

        if ($dataLain != '0') {
            $stringDataLain = explode("&", $dataLain);
            session()->setFlashdata('emailPem', $stringDataLain[0]);
            session()->setFlashdata('namaPem', $stringDataLain[1]);
            session()->setFlashdata('nohpPem', $stringDataLain[2]);
            session()->setFlashdata('namaPen', $stringDataLain[3]);
            session()->setFlashdata('nohpPen', $stringDataLain[4]);
        }
        session()->set(['alamat' => $arr]);
        return redirect()->to('/checkout');
    }
    public function getKota($id_prov)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/city?province=" . $id_prov,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $kota = json_decode($response, true);
        return $this->response->setJSON($kota, false);
    }
    public function getKec($id_kota)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/subdistrict?city=" . $id_kota,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $kec = json_decode($response, true);
        return $this->response->setJSON($kec, false);
    }
    public function getKode($kec)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://dakotacargo.co.id/api/api_glb_M_kodepos.asp?key=15f6a51696a8b034f9ce366a6dc22138&id=11022019000001&aKec=" . rawurlencode($kec),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $kode = json_decode($response, true);
        // dd([
        //     'URL' => "https://dakotacargo.co.id/api/api_glb_M_kodepos.asp?key=15f6a51696a8b034f9ce366a6dc22138&id=11022019000001&aKec=" . $kec,
        //     'hasil' => $kode
        // ]);
        return $this->response->setJSON($kode, false);
    }

    public function getPaket($asal, $tujuan, $berat, $kurir)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=" . $asal . "&originType=subdistrict&destination=" . $tujuan . "&destinationType=subdistrict&weight=" . $berat . "&courier=" . $kurir,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $paket = json_decode($response, true);
        return $this->response->setJSON($paket, false);
    }
    public function getDakota()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://www.dakotacargo.co.id/api/api_glb_M_kodepos.asp?key=15f6a51696a8b034f9ce366a6dc22138&id=11022019000001&aKdp=13890",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            // CURLOPT_HTTPHEADER => array(
            //     "authorization: biteship_test.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiamFzbWluZSB0ZXN0aW5nIiwidXNlcklkIjoiNjU4M2I1MmY2YzAyMTAxZjVhZTJlNWY5IiwiaWF0IjoxNzAzMTMxOTQ5fQ.22F0VWJe-JavNsxaw_s68ErNv41cTVcYIm1OWtJF9og"
            // ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $kota = json_decode($response, true);
        return $this->response->setJSON($kota, false);
    }
    public function cobaenv()
    {
        $myEnv = env('MIDTRANS_PRODUCTION_KEY', 'DefaultValue');
        dd($myEnv);
    }
    public function actionPayCore()
    {
        $email = session()->get('email');
        $pembayaran = $this->request->getVar('pembayaran');

        if ($email == 'tamu') {
            if (!$this->validate([
                'emailPem' => ['rules' => 'required'],
                'nama' => ['rules' => 'required'],
                'nohp' => ['rules' => 'required'],
                'provinsi' => ['rules' => 'required'],
                'kota' => ['rules' => 'required'],
                'kecamatan' => ['rules' => 'required'],
                'kodepos' => ['rules' => 'required'],
                'alamat_add' => ['rules' => 'required'],
                'alamat' => ['rules' => 'required'],
            ])) {
                $validation = \Config\Services::validation();
                session()->setFlashdata('msg', 'Terdapat data yang masih kosong');
                return redirect()->to('/checkout')->withInput();
            }
        } else {
            if (!$this->validate([
                'provinsi' => ['rules' => 'required'],
                'kota' => ['rules' => 'required'],
                'kecamatan' => ['rules' => 'required'],
                'kodepos' => ['rules' => 'required'],
                'alamat_add' => ['rules' => 'required'],
                'alamat' => ['rules' => 'required'],
            ])) {
                $validation = \Config\Services::validation();
                session()->setFlashdata('msg', 'Terdapat data yang masih kosong');
                return redirect()->to('/checkout')->withInput();
            }
        }
        if ($pembayaran == 'card') {
            if (!$this->validate([
                'tokencc' => ['rules' => 'required'],
            ])) {
                $validation = \Config\Services::validation();
                session()->setFlashdata('msg', 'Terdapat data yang masih kosong');
                return redirect()->to('/checkout')->withInput();
            }
        }

        $email = $this->request->getVar('emailPem') ? $this->request->getVar('emailPem') : session()->get('email');
        $nama = $this->request->getVar('nama') ? $this->request->getVar('nama') : session()->get('nama');
        $nohp = $this->request->getVar('nohp') ? $this->request->getVar('nohp') : session()->get('nohp');
        $prov = $this->request->getVar('provinsi');
        $kab = $this->request->getVar('kota');
        $kec = $this->request->getVar('kecamatan');
        $kode = $this->request->getVar('kodepos');
        $alamatAdd = $this->request->getVar('alamat_add');
        $alamatLengkap = $this->request->getVar('alamat');
        $note = $this->request->getVar('note');
        $keranjang = session()->get('keranjang');
        $tokencc = $this->request->getVar('tokencc');

        // if ($email == 'galih8.4.2001@gmail.com') {
        //     return $this->response->setJSON($this->request->getVar(), false);
        // }

        $alamat = [
            "prov_id" => explode("-", $prov)[0],
            "prov" => explode("-", $prov)[1],
            "kab_id" => explode("-", $kab)[0],
            "kab" => explode("-", $kab)[1],
            "kec_id" => explode("-", $kec)[0],
            "kec" => explode("-", $kec)[1],
            "desa" => explode("-", $kode)[0],
            "kodepos" => explode("-", $kode)[1],
            "add" => $alamatAdd,
            "alamat" => $alamatLengkap,
        ];

        $cariUser = $this->pembeliModel->getPembeli($email);
        if ($cariUser) {
            $this->pembeliModel->where('email_user', $email)->set([
                'alamat' => json_encode($alamat),
            ])->update();
        }

        $produk = [];
        $subtotal = 0;
        $itemDetails = [];
        $preorder = $this->preorderBarangModel->where(['email_customer' => $email])->findAll();
        $potonganPreorder = 0;

        if (!empty($keranjang)) {
            foreach ($keranjang as $ind => $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = round($persen * $produknya['harga']);
                $subtotal += $hasil * (int)$element['jumlah'];
                array_push($produk, array(
                    'id' => $produknya["id"],
                    'name' => $produknya['nama'] . " (" . $element['varian'] . ")",
                    'value' => $hasil,
                    'quantity' => (int)$element['jumlah'],
                ));

                //untuk midtrans
                $item = array(
                    'id' => $produknya["id"],
                    'price' => $hasil,
                    'quantity' => $element['jumlah'],
                    'name' => substr($produknya["nama"] . " (" . ucfirst($element['varian']) . ")", 0, 50),
                    'packed' => false
                );
                array_push($itemDetails, $item);

                //masukin ke arrIdBarang untuk di cek masuk preorder atau tidak
                foreach ($preorder as $p) {
                    if ($p['id_barang'] == $element['id']) {
                        $potonganPreorder += ((int)$produknya['harga'] - (int)$p['harga']) * (int)$element['jumlah'];
                    }
                }
            }
        }
        $total = $subtotal + 5000;
        if ($pembayaran != 'rekening') {
            $biayaadmin = array(
                'id' => 'Biaya Admin',
                'price' => 5000,
                'quantity' => 1,
                'name' => 'Biaya Admin',
            );
            array_push($itemDetails, $biayaadmin);
        } else $total -= 5000;

        //voucher
        $diskonVoucher = 0;
        $voucherSelected = false;
        $cashback = 0;
        if (session()->get('voucher')) {
            $voucherDetail = $this->voucherClaimedModel->getVoucher(session()->get('voucher'));
            if ($voucherDetail['jenis'] != 'cashback') {
                if ($voucherDetail['satuan'] == 'persen') {
                    $diskonVoucher = round($voucherDetail['nominal'] / 100 * ($total - 5000));
                } else if ($voucherDetail['satuan'] == 'rupiah') {
                    $diskonVoucher = (int)$voucherDetail['nominal'];
                }
            } else {
                $cashback = (int)$voucherDetail['nominal'];
            }
            $voucherSelected = $voucherDetail;
        }
        $data = [
            'keranjang' => $keranjang,
            'diskonVoucher' => $diskonVoucher,
            'voucherSelected' => $voucherSelected
        ];
        $voucher = $data['voucherSelected'];

        if ($data['diskonVoucher'] > 0) {
            array_push($itemDetails, [
                'id' => 'Diskon Voucher',
                'price' => -$data['diskonVoucher'],
                'quantity' => 1,
                'name' => 'Diskon Voucher ' . $voucherSelected['nama'],
            ]);
        }

        if ($potonganPreorder > 0) {
            array_push($itemDetails, [
                'id' => 'Potongan Preorder',
                'price' => -$potonganPreorder,
                'quantity' => 1,
                'name' => 'Potongan Preorder',
            ]);
        }

        //point
        $poinSession = session()->get('poin');
        $usepoin = session()->get('usepoin');
        $poin = 0;
        foreach ($poinSession as $p) {
            if ($p['active']) {
                $poin += (int)$p['nominal'];
            }
        }
        if ($poin > ($total - $diskonVoucher - $potonganPreorder)) {
            $poin = $total - $diskonVoucher - $potonganPreorder;
        }
        if ($usepoin) {
            //add poin di itemdetails
            array_push($itemDetails, [
                'id' => 'Luna Point',
                'price' => -$poin,
                'quantity' => 1,
                'name' => 'Luna Point',
            ]);

            //update poin di pembeli
            $poinCounter = 0;
            $poinArrIndexTerpakai = [];
            $poinIndexAkhirHapus = null;
            foreach ($poinSession as $ind_p => $p) {
                $poinCounter += (int)$p['nominal'];
                if ($poinCounter >= $poin) {
                    $poinSblm = $poinCounter - (int)$p['nominal'];
                    $penambahnya = $poin - $poinSblm;
                    $sisa = (int)$p['nominal'] - $penambahnya;
                    $poinSession[$ind_p]['nominal'] = $sisa;
                    if ($sisa == 0) {
                        $poinIndexAkhirHapus = $ind_p;
                    }
                    break;
                } else {
                    array_push($poinArrIndexTerpakai, $ind_p);
                }
            }
            if ($poinIndexAkhirHapus != null) {
                $poinSession[$poinIndexAkhirHapus]['active'] = false;
            }
            foreach ($poinArrIndexTerpakai as $p) {
                $poinSession[$p]['active'] = false;
            }
            $this->pembeliModel->where(['email_user' => $email])->set(['poin' => json_encode($poinSession)])->update();

            //update poin di session
            session()->set('poin', $poinSession);
        }

        $midtrans_production_key = env('MIDTRANS_PRODUCTION_KEY', 'DefaultValue');
        if (in_array($email, $this->emailUjiCoba))
            $auth = base64_encode("SB-Mid-server-3M67g25LgovNPlwdS4WfiMsh" . ":");
        else
            $auth = base64_encode($midtrans_production_key . ":");
        $pesananke = $this->pemesananModel->orderBy('id', 'desc')->first();
        $idFix = "L" . (sprintf("%08d", $pesananke ? ((int)$pesananke['id'] + 1) : 1));
        $randomId = "L" . rand();
        // $customField = json_encode([
        //     'e' => $email,
        //     'n' => $nama,
        //     'h' => $nohp,
        //     'a' => $alamatLengkap,
        //     'i' => $produk,
        //     'nt' => $note,
        //     'v' => [
        //         'd' => $data['diskonVoucher'], //ini udah bentuk rupiah
        //         'id' => $voucher ? $voucher['id'] : false,
        //     ],
        //     'c' => $cashback
        // ]);
        $customField = '';
        $arrPostField = [
            "transaction_details" => [
                "order_id" => in_array($email, $this->emailUjiCoba) ? $randomId : $idFix,
                "gross_amount" => $total - $diskonVoucher - $potonganPreorder - ($usepoin ? $poin : 0),
            ],
            'customer_details' => array(
                'email' => $email,
                'phone' => $nohp,
                'first_name' => $nama,
            ),
            'item_details' => $itemDetails,
            "custom_field1" => substr($customField, 0, 255),
            "custom_field2" => substr($customField, 255, 255),
            "custom_field3" => substr($customField, 510, 255),
        ];

        switch ($pembayaran) {
            case 'bca':
                $arrPostField["payment_type"] = "bank_transfer";
                $arrPostField["bank_transfer"] = ["bank" => "bca"];
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 60,
                    "unit" => "minute"
                ];
                break;
            case 'bri':
                $arrPostField["payment_type"] = "bank_transfer";
                $arrPostField["bank_transfer"] = ["bank" => "bri"];
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 60,
                    "unit" => "minute"
                ];
                break;
            case 'bni':
                $arrPostField["payment_type"] = "bank_transfer";
                $arrPostField["bank_transfer"] = ["bank" => "bni"];
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 60,
                    "unit" => "minute"
                ];
                break;
            case 'cimb':
                $arrPostField["payment_type"] = "bank_transfer";
                $arrPostField["bank_transfer"] = ["bank" => "cimb"];
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 60,
                    "unit" => "minute"
                ];
                break;
            case 'permata':
                $arrPostField["payment_type"] = "permata";
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 60,
                    "unit" => "minute"
                ];
                break;
            case 'mandiri':
                $arrPostField["payment_type"] = "echannel";
                $arrPostField["echannel"] = [
                    "bill_info1" => "Payment:",
                    "bill_info2" => "Online purchase"
                ];
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 60,
                    "unit" => "minute"
                ];
                break;
            case 'qris':
                $arrPostField["payment_type"] = "qris";
                $arrPostField["qris"] = ["acquirer" => "gopay"];
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 15,
                    "unit" => "minute"
                ];
                break;
            case 'gopay':
                $arrPostField["payment_type"] = "gopay";
                $arrPostField["gopay"] = [
                    "enable_callback" => true,
                    "callback_url" => "https://lunareafurniture.com/order/" . $arrPostField['transaction_details']['order_id']
                ];
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 15,
                    "unit" => "minute"
                ];
                break;
            case 'shopeepay':
                $arrPostField["payment_type"] = "shopeepay";
                $arrPostField["shopeepay"] = ["callback_url" => "https://lunareafurniture.com/order/" . $arrPostField['transaction_details']['order_id']];
                $arrPostField['custom_expiry'] = [
                    "expiry_duration" => 15,
                    "unit" => "minute"
                ];
                break;
            case 'card':
                $arrPostField["payment_type"] = "credit_card";
                $arrPostField["credit_card"] = [
                    "token_id" => $tokencc
                ];
                break;
            case 'rekening':
                $arrPostField["payment_type"] = "rekening";
                break;
            default:
                session()->setFlashdata('msg', 'Tipe pembayaran tidak ditemukan');
                return redirect()->to('/checkout');
                break;
        }

        if ($pembayaran == 'rekening') {
            $dCurr = strtotime("+7 Hours");
            $dExp = strtotime("+8 Hours");
            $transaction_time = date("Y-m-d H:i:s", $dCurr);
            $expiry_time = date("Y-m-d H:i:s", $dExp);
            $hasilMidtrans = [
                "va_numbers" => [
                    [
                        "va_number" => "003401001574304",
                        "bank" => "bri"
                    ]
                ],
                "transaction_time" => $transaction_time,
                "transaction_status" => "pending-rekening",
                "transaction_id" => "8e80aa9a-4491-4e6e-946e-741848f91d5c",
                "status_message" => "midtrans payment notification",
                "status_code" => "201",
                "signature_key" => "9701774dc96c5fd29ce1a96df0b3936797ae9cd7d901cacd2a2252bb4807a2d3ed26edacc0bd112923c6f18dfac534e6676ccd4a76ad43d5390e0d6fa3f0a87d",
                "payment_type" => $arrPostField["payment_type"],
                "order_id" => $arrPostField['transaction_details']['order_id'],
                "merchant_id" => "G297047633",
                "gross_amount" => $arrPostField['transaction_details']['gross_amount'],
                "fraud_status" => "accept",
                "expiry_time" => $expiry_time,
                "custom_field1" => substr($customField, 0, 255),
                "custom_field2" => substr($customField, 255, 255),
                "custom_field3" => substr($customField, 510, 255),
                "currency" => "IDR"
            ];
        } else {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => in_array($email, $this->emailUjiCoba) ? "https://api.sandbox.midtrans.com/v2/charge" : "https://api.midtrans.com/v2/charge",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($arrPostField),
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Content-Type: application/json",
                    "Authorization: Basic " . $auth,
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $hasilMidtrans = json_decode($response, true);

            if (substr($hasilMidtrans['status_code'], 0, 1) != '2') {
                session()->setFlashdata('msg', $hasilMidtrans['status_message']);
                return redirect()->to('/checkout');
            }
        }

        //dari update transaction =============================
        switch ($hasilMidtrans['transaction_status']) {
            case 'settlement':
                $status = "Proses";
                break;
            case 'capture':
                $status = "Proses";
                break;
            case 'pending':
                $status = "Menunggu Pembayaran";
                break;
            case 'expire':
                $status = "Kadaluarsa";
                break;
            case 'deny':
                $status = "Ditolak";
                break;
            case 'failure':
                $status = "Gagal";
                break;
            case 'refund':
                $status = "Refund";
                break;
            case 'partial_refund':
                $status = "Partial Refund";
                break;
            case 'cancel':
                $status = "Dibatalkan";
                break;
            case 'pending-rekening':
                $status = "Menunggu Pembayaran Rekening";
                break;
            default:
                $status = "No Status";
                break;
        }
        $insertDataPemesanan = [
            'email_cus' => $email,
            'nama_pen' => $nama,
            'hp_pen' => $nohp,
            'alamat_pen' => $alamatLengkap,
            'resi' => 'Menunggu pengiriman',
            'items' => json_encode($produk),
            'kurir' => 'kosong',
            'id_midtrans' => $arrPostField['transaction_details']['order_id'],
            'status' => $status,
            'data_mid' => json_encode($hasilMidtrans),
            'note' => $note,
            'diskonVoucher' => $data['diskonVoucher'],
            'idVoucher' => $voucher ? $voucher['id_voucher'] : 0,
            'cashback' => $cashback,
            'pakai_poin' => $poin
        ];
        $this->pemesananModel->insert($insertDataPemesanan);

        if ($insertDataPemesanan['idVoucher'] > 0) {
            $this->voucherClaimedModel->where(['id' => $voucher['id']])->set(['active' => false])->update();
            session()->remove('voucher');
        }
        session()->remove('usepoin');

        session()->set(['keranjang' => []]);
        $cekMember = $this->pembeliModel->getPembeli($email);
        if ($cekMember) $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode([])])->update();

        //pengurangan stok produk
        $dataTransaksiFulDariDatabase = $this->pemesananModel->where('id_midtrans', $arrPostField['transaction_details']['order_id'])->first();
        $dataTransaksiFulDariDatabase_items = json_decode($dataTransaksiFulDariDatabase['items'], true);
        foreach ($dataTransaksiFulDariDatabase_items as $item) {
            $barangCurr = $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->first();
            $varianSelected = rtrim(explode("(", $item['name'])[1], ")");
            if (count(json_decode($barangCurr['varian'], true)) > count(explode(",", $barangCurr['stok']))) {
                $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                    'stok' => (int)$barangCurr['stok'] - $item['quantity']
                ])->update();
            } else {
                $stokTerbaru = explode(",", $barangCurr['stok']);
                $stokSelected = $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))];
                $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))] = (int)$stokSelected - $item['quantity'];
                $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                    'stok' => implode(",", $stokTerbaru)
                ])->update();
            }
        }
        $ws_url = env('WS_URL', 'DefaultValue');
        $client = new Client($ws_url);
        $client->send(json_encode([
            'jenis' => 'order',
            'id_order' => ''
        ]));
        $client->close();
        return redirect()->to('/order/' . $arrPostField['transaction_details']['order_id']);
    }
    public function payOrder($order_id)
    {
        $foto = file_get_contents($this->request->getFile('buktiBayar'));
        $this->pemesananModel->where(['id_midtrans' => $order_id])->set(['bukti_bayar' => $foto])->update();
        $ws_url = env('WS_URL', 'DefaultValue');
        $client = new Client($ws_url);
        $client->send(json_encode([
            'jenis' => 'order',
            'id_order' => ''
        ]));
        $client->close();
        return redirect()->to('/order/' . $order_id);
    }
    public function cancelOrder($order_id)
    {
        $pemesanan = $this->pemesananModel->getPemesanan($order_id);
        if ($pemesanan['status'] == 'Menunggu Pembayaran Rekening') {
            $dataMid_curr = json_decode($pemesanan['data_mid'], true);
            $dataMid_curr['transaction_status'] = 'cancel';
            $status = 'Dibatalkan';
            $this->pemesananModel->where('id_midtrans', $order_id)->set([
                'status' => $status,
                'data_mid' => json_encode($dataMid_curr),
            ])->update();

            $dataTransaksiFulDariDatabase = $this->pemesananModel->where('id_midtrans', $order_id)->first();
            //reset jumlah produk
            $dataTransaksiFulDariDatabase_items = json_decode($dataTransaksiFulDariDatabase['items'], true);
            foreach ($dataTransaksiFulDariDatabase_items as $item) {
                //bentuk item
                //     {
                //         "id":"B20240214223820",
                //         "name":"Rak Serbaguna - RSD 80 (Putih)",
                //         "value":517000,
                //         "quantity":1
                //     }
                $barangCurr = $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->first();
                $varianSelected = rtrim(explode("(", $item['name'])[1], ")");
                if (count(json_decode($barangCurr['varian'], true)) > count(explode(",", $barangCurr['stok']))) {
                    $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                        'stok' => (int)$barangCurr['stok'] + $item['quantity']
                    ])->update();
                } else {
                    $stokTerbaru = explode(",", $barangCurr['stok']);
                    $stokSelected = $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))];
                    $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))] = (int)$stokSelected + $item['quantity'];
                    $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                        'stok' => implode(",", $stokTerbaru)
                    ])->update();
                }
            }
            //aktifkan kemabli voucher claimednya
            if ($dataTransaksiFulDariDatabase['idVoucher'] != 0) {
                $this->voucherClaimedModel->where([
                    'id_voucher' => $dataTransaksiFulDariDatabase['idVoucher'],
                    'email_user' => $dataTransaksiFulDariDatabase['email_cus']
                ])->set(['active' => true])->update();
            }
            //aktifkan kembali poinnya
            if ($dataTransaksiFulDariDatabase['pakai_poin'] > 0) {
                $pembeliCur = $this->pembeliModel->where(['email_user' => $dataTransaksiFulDariDatabase['email_cus']])->first();
                $poinCur = json_decode($pembeliCur['poin'], true);
                $poinCounter = 0;
                $poinCurFinal = $dataTransaksiFulDariDatabase['pakai_poin'];
                foreach ($poinCur as $ind_p => $p) {
                    if (!$p['active']) {
                        $poinCur[$ind_p]['active'] = true;
                        if ((int)$p['nominal'] == 0) {
                            $sisa = $poinCurFinal - $poinCounter;
                            $poinCur[$ind_p]['nominal'] = $sisa;
                        }
                    } else {
                        if ($poinCurFinal > $poinCounter) {
                            $sisa = $poinCurFinal - $poinCounter;
                            $poinCur[$ind_p]['nominal'] = (int)$p['nominal'] + $sisa;
                        }
                        break;
                    }
                    $poinCounter += (int)$p['nominal'];
                }
                $this->pembeliModel->where(['email_user' => $dataTransaksiFulDariDatabase['email_cus']])->set(['poin' => json_encode($poinCur)])->update();
            }
            $ws_url = env('WS_URL', 'DefaultValue');
            $client = new Client($ws_url);
            $client->send(json_encode([
                'jenis' => 'order',
                'id_order' => ''
            ]));
            return redirect()->to('/order/' . $order_id);
        }
        $midtrans_production_key = env('MIDTRANS_PRODUCTION_KEY', 'DefaultValue');
        if (in_array($pemesanan['email_cus'], $this->emailUjiCoba))
            $auth = base64_encode("SB-Mid-server-3M67g25LgovNPlwdS4WfiMsh" . ":");
        else
            $auth = base64_encode($midtrans_production_key . ":");
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => in_array($pemesanan['email_cus'], $this->emailUjiCoba) ? "https://api.sandbox.midtrans.com/v2/" . $order_id . "/cancel" : "https://api.midtrans.com/v2/" . $order_id . "/cancel",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Content-Type: application/json",
                "Authorization: Basic " . $auth,
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $hasilMidtrans = json_decode($response, true);
        if (substr($hasilMidtrans['status_code'], 0, 1) != '2') {
            session()->setFlashdata('msg', $hasilMidtrans['status_message']);
        }
        return redirect()->to('/order/' . $order_id);
    }

    public function tracking($tipe, $resi)
    {
        $curl = curl_init();
        if ($tipe == "da") {
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://staging.dakotacargo.co.id/api/trace/?b=" . $resi,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $hasilnya = json_decode($response, true)['detail'];
        } else {
            switch ($tipe) {
                case 'je':
                    $kurir = 'jne';
                    break;
                case 'jt':
                    $kurir = 'jnt';
                    break;
                case 'wa':
                    $kurir = 'wahana';
                    break;
                case 'in':
                    $kurir = 'indah';
                    break;
            }
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/waybill",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "waybill=SOCAG00183235715&courier=" . $kurir,
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/x-www-form-urlencoded",
                    "key: your-api-key"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $hasilnya = json_decode($response, true)['rajaongkir']['results']['manifest'];
        }


        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        // $hasilnya= [
        //     [
        //     'tanggal' => "9/3/2022 1:15:56 PM",
        //     "keterangan"=> "Barang Diterima Oleh : JAKARTA CABANG Toko Purnama Baru, Jam :01:15",
        //     "posisi"=> "DITERIMA",
        //     "status"=> "Delivered"
        // ],
        //     [
        //     "tanggal"=> "9/3/2022 11:10:05 AM",
        //     "keterangan"=> "Barang  Diloper Oleh Petugas :  JAKARTA CABANG 002000062/09/2022/LA",
        //     "posisi"=> "Jakarta Timur",
        //     "status"=> "shipped"
        //     ],
        //     [
        //     "tanggal"=> "9/3/2022 10:13:05 AM",
        //     "keterangan"=> "Barang Sampai (Transit) di : JAKARTA CABANG ",
        //     "posisi"=> "Jakarta Timur",
        //     "status"=> "shipped"
        //     ],

        // ];

        // $hasilnyaRO = [
        //     [
        //        "manifest_code"=>"1",
        //        "manifest_description"=>"Manifested",
        //        "manifest_date"=>"2015-03-04",
        //        "manifest_time"=>"03:41",
        //        "city_name"=>"SOLO"
        // ],
        //     [
        //        "manifest_code"=>"2",
        //        "manifest_description"=>"On Transit",
        //        "manifest_date"=>"2015-03-04",
        //        "manifest_time"=>"15:44",
        //        "city_name"=>"JAKARTA"
        // ],
        //     [
        //        "manifest_code"=>"3",
        //        "manifest_description"=>"Received On Destination",
        //        "manifest_date"=>"2015-03-05",
        //        "manifest_time"=>"08:57",
        //        "city_name"=>"PALEMBANG"
        // ],
        // ];

        $data = [
            'title' => 'Tracking',
            'hasilnya' => $hasilnya,
            'bulan' => $bulan,
        ];
        return view('pages/tracking', $data);
    }
    public function transaction()
    {
        $email = session()->get("email");
        $detailTransaksi = [];
        if ($email != 'tamu')
            $detailTransaksi = $this->pemesananModel->getPemesananCus($email);
        else {
            $transaksi = session()->get("transaksi");
            foreach ($transaksi as $idMid) {
                array_push($detailTransaksi, $this->pemesananModel->getPemesanan($idMid));
            }
        }
        $data = [
            'title' => 'Transaksi Pembayaran',
            'transaksi' => $detailTransaksi,
        ];
        return view('pages/transaction', $data);
    }
    public function addTransaction()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $getPembeli = $this->pembeliModel->getPembeli($body['emailCus']);
        $transaksi = json_decode($getPembeli['transaksi'], true);
        array_push($transaksi, $body['idMid']);

        $d = strtotime("+7 Hours");
        $tanggal = date("d/m/Y", $d);

        $this->pembeliModel->where('email_user', $body['emailCus'])->set(['transaksi' => json_encode($transaksi)])->update();
        $this->pemesananModel->insert([
            'nama_cus' => $body['namaCus'],
            'email_cus' => $body['emailCus'],
            'hp_cus' => $body['hpCus'],
            'nama_pen' => $body['namaPen'],
            'hp_pen' => $body['hpPen'],
            'alamat_pen' => json_encode($body['alamatPen']),
            'resi' => $body['resi'],
            'id_midtrans' => $body['idMid'],
            'items' => json_encode($body['items']),
            'status' => $body['status'],
            'kurir' => $body['kurir'],
            'data_mid' => json_encode($body['dataMid']),
        ]);

        $arr = array(
            'success' => true,
            'transaksi' => implode("-", $transaksi),
            'hasil' => [
                'nama_cus' => $body['namaCus'],
                'email_cus' => $body['emailCus'],
                'hp_cus' => $body['hpCus'],
                'nama_pen' => $body['namaPen'],
                'hp_pen' => $body['hpPen'],
                'alamat_pen' => $body['alamatPen'],
                'resi' => $body['resi'],
                'id_midtrans' => $body['idMid'],
                'items' => $body['items'],
                'status' => $body['status'],
                'kurir' => $body['kurir'],
                'data_mid' => json_encode($body['dataMid']),
            ]
        );
        return $this->response->setJSON($arr, false);
    }
    public function afterAddTransaction($transaksi)
    {
        $hasilnya = explode("-", $transaksi);
        session()->set(['transaksi' => $hasilnya]);
        return redirect()->to('/transaction');
    }
    public function finishUrlMid($code, $status)
    {
        // dd([
        //     'code' => $code,
        //     'status' => $status
        // ]);
        if ($code != "JSM-zWYWObdPEKlHA0PWP6BN") {
            return redirect()->to("/");
        }
        // session()->setFlashdata('id_pesanan', 'JSM0000000');
        switch ($status) {
            case 'success':
                return redirect()->to("/successpay");
                break;
            case 'pending':
                return redirect()->to("/progresspay");
                break;
            case 'error':
                return redirect()->to("/errorpay");
                break;
            default:
                return redirect()->to("/errorpay");
                break;
        }
    }
    public function finishUrl($code, $data = false)
    {
        if ($code != "JSM-zWYWObdPEKlHA0PWP6BN") {
            return redirect()->to("/");
        }
        if ($data) {
            $dataArr = explode("&", $data);
            $email = $dataArr[0];
            $nama = $dataArr[1];
            $nohp = $dataArr[2];
            $namaPen = $dataArr[3];
            $nohpPen = $dataArr[4];
            $alamat = $dataArr[5];
            $orderId = $dataArr[6];
            $kurir = str_replace("@", "&", $dataArr[7]);
            $items = $dataArr[8];

            //pengurangan stok produk
            $itemsArr = json_decode($items, true);
            foreach ($itemsArr as $i) {
                $barangCurr = $this->barangModel->where('nama', rtrim(explode("(", $i['name'])[0]))->first();
                $this->barangModel->where('nama', rtrim(explode("(", $i['name'])[0]))->set([
                    'stok' => (int)$barangCurr['stok'] - $i['quantity']
                ])->update();
            }

            $this->pemesananModel->where('id_midtrans', $orderId)->set([
                'nama_cus' => $nama,
                'email_cus' => $email,
                'hp_cus' => $nohp,
                'nama_pen' => $namaPen,
                'hp_pen' => $nohpPen,
                'alamat_pen' => $alamat,
                'resi' => "Menunggu pengiriman " . $kurir,
                'items' => $items,
                'kurir' => $kurir,
            ])->update();
            $pemesananSelected = $this->pemesananModel->getPemesanan($orderId);
            session()->setFlashdata('id_pesanan', $orderId);

            $transaksi = session()->get('transaksi');
            array_push($transaksi, $orderId);
            session()->set(['transaksi' => $transaksi]);

            switch ($pemesananSelected['status']) {
                case 'Proses':
                    return redirect()->to("/successpay");
                    break;
                case 'Menunggu Pembayaran':
                    return redirect()->to("/progresspay");
                    break;
                case 'Kadaluarsa':
                    return redirect()->to("/expirepay");
                    break;
                case 'Ditolak':
                    return redirect()->to("/denypay");
                    break;
                case 'Gagal':
                    return redirect()->to("/failurepay");
                    break;
                case 'Dibatalkan':
                    return redirect()->to("/cancelpay");
                    break;
                default:
                    return redirect()->to("/errorpay");
                    break;
            }
        } else {
            session()->setFlashdata('id_pesanan', 'JSM0010101010101');
            return redirect()->to("/errorpay");
        }
    }
    public function updateTransaction()
    {
        $arr = [
            'success' => true,
        ];
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $order_id = $body['order_id'];
        $fraud = $body['fraud_status'];
        if (isset($body['custom_field1'])) {
            $customField = json_decode($body['custom_field1'] . (isset($body['custom_field2']) ? $body['custom_field2'] : '') . (isset($body['custom_field3']) ? $body['custom_field3'] : ''), true);
        }
        if ($fraud == "accept") {
            switch ($body['transaction_status']) {
                case 'settlement':
                    $status = "Proses";
                    break;
                case 'capture':
                    $status = "Proses";
                    break;
                case 'pending':
                    $status = "Menunggu Pembayaran";
                    break;
                case 'expire':
                    $status = "Kadaluarsa";
                    break;
                case 'deny':
                    $status = "Ditolak";
                    break;
                case 'failure':
                    $status = "Gagal";
                    break;
                case 'refund':
                    $status = "Refund";
                    break;
                case 'partial_refund':
                    $status = "Partial Refund";
                    break;
                case 'cancel':
                    $status = "Dibatalkan";
                    break;
                default:
                    $status = "No Status";
                    break;
            }
        } else {
            $status = 'Forbidden';
        }

        $order_id_first_char = substr($order_id, 0, 1);
        if ($order_id_first_char == 'L') {
            $dataTransaksi_curr = $this->pemesananModel->getPemesanan($order_id);
            if (isset($dataTransaksi_curr)) {
                $dataMid_curr = json_decode($dataTransaksi_curr['data_mid'], true);
                $dataMid_curr['transaction_status'] = $body['transaction_status'];
                $this->pemesananModel->where('id_midtrans', $order_id)->set([
                    'status' => $status,
                    'data_mid' => json_encode($dataMid_curr),
                ])->update();

                $dataTransaksiFulDariDatabase = $this->pemesananModel->where('id_midtrans', $order_id)->first();
                if ($status == 'Proses') {
                    if ($dataTransaksiFulDariDatabase['idVoucher'] != 0) {
                        $this->voucherClaimedModel->where([
                            'id_voucher' => $dataTransaksiFulDariDatabase['idVoucher'],
                            'email_user' => $dataTransaksiFulDariDatabase['email_cus']
                        ])->delete();
                    }
                    //tambah poin
                    $emailCus = $dataTransaksiFulDariDatabase['email_cus'];
                    $pembeli = $this->pembeliModel->getPembeli($emailCus);
                    $waktuCurr = strtotime("+7 Hours");
                    $waktuCurrYmd = date("Y-m-d", $waktuCurr);
                    $poin = json_decode($pembeli['poin'], true);
                    if ($dataTransaksiFulDariDatabase['cashback'] > 0) {
                        $voucherSelected = $this->voucherModel->getVoucher($dataTransaksiFulDariDatabase['idVoucher']);
                        $cashback = (int)$dataTransaksiFulDariDatabase['cashback'];

                        $kadaluarsa = date("Y-m-d", strtotime($voucherSelected['durasi_poin'], strtotime($waktuCurrYmd)));
                        $dataPoinNew = [
                            'kadaluarsa' => $kadaluarsa,
                            'nominal' => $cashback,
                            'active' => true
                        ];
                        array_push($poin, $dataPoinNew);
                        $this->pointHistoryModel->insert([
                            'id' => $waktuCurr,
                            'label' => $voucherSelected['nama'],
                            'nominal' => $voucherSelected['nominal'],
                            'keterangan' => 'Cashback voucher ' . strtolower($voucherSelected['nama']),
                            'tanggal' => $waktuCurrYmd,
                            'email_user' => $emailCus
                        ]);
                    }
                    //kalo pakai poin
                    if ($dataTransaksiFulDariDatabase['pakai_poin'] > 0) {
                        //add point history
                        $this->pointHistoryModel->insert([
                            'id' => $waktuCurr,
                            'label' => 'Pembelian',
                            'nominal' => $dataTransaksiFulDariDatabase['pakai_poin'],
                            'keterangan' => 'Pembelian dengan id pemesanan ' . $order_id,
                            'tanggal' => $waktuCurrYmd,
                            'email_user' => $emailCus
                        ]);
                        //update poin pembeli
                        $poinArrIndexTerpakai = [];
                        foreach ($poin as $ind_p => $p) {
                            if (!$p['active']) {
                                array_push($poinArrIndexTerpakai, $ind_p);
                            }
                        }
                        foreach ($poinArrIndexTerpakai as $p) {
                            unset($poin[$p]);
                        }
                    }
                    $poinBaru = array_values($poin);
                    $this->pembeliModel->where(['email_user' => $emailCus])->set(['poin' => json_encode($poinBaru)])->update();
                    //tambah tier
                    $tier = json_decode($pembeli['tier'], true);
                    $data = $tier['data'];
                    $kadaluarsa = date("Y-m-d", strtotime("+1 year", strtotime($waktuCurrYmd)));
                    array_push($data, [
                        'kadaluarsa' => $kadaluarsa,
                        'nominal' => (int)$dataMid_curr['gross_amount'],
                        'id_pesanan' => $order_id
                    ]);
                    $dataBaru = [];
                    $jumlah = 0;
                    foreach ($data as $d) {
                        $waktuCurr = strtotime("+7 Hours");
                        $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
                        if (date("m-d", $waktuCurr) == '01-01') {
                            if ($waktuCurrYmd <= strtotime($d['kadaluarsa'])) {
                                $jumlah += (int)$d['nominal'];
                                array_push($dataBaru, $d);
                            }
                        } else {
                            $jumlah += (int)$d['nominal'];
                            array_push($dataBaru, $d);
                        }
                    }
                    if ($jumlah < 10000000) {
                        $label = 'bronze';
                    } else if ($jumlah < 50000000) {
                        $label = 'silver';
                    } else if ($jumlah < 100000000) {
                        $label = 'gold';
                    } else if ($jumlah >= 100000000) {
                        $label = 'platinum';
                    }
                    $tier['label'] = $label;
                    $tier['data'] = $dataBaru;
                    $this->pembeliModel->where(['email_user' => $emailCus])->set(['tier' => json_encode($tier)])->update();

                    //input stok admin
                    $itemsNya = json_decode($dataTransaksiFulDariDatabase['items'], true);
                    foreach ($itemsNya as $i) {
                        $variannya = rtrim(explode("(", $i['name'])[1], ")");
                        $lastData = $this->stokModel->orderBy('id', 'desc')->where(['id_barang' => $i['id'], 'varian' => $variannya])->first();
                        if (!$lastData) $currentStok = 0;
                        else $currentStok = $lastData['stok_akhir'];
                        $this->stokModel->insert([
                            'id' => time(),
                            'id_barang' => $i['id'],
                            'nama' => explode(" (", $i['name'])[0],
                            'varian' => $variannya,
                            'jumlah' => '-' . $i['quantity'],
                            'email_admin' => '',
                            'keterangan' => 'Pmebelian WEB',
                            'stok_akhir' => $currentStok,
                            'tanggal' => $dataMid_curr['transaction_time'],
                        ]);
                    }
                }
                //reset jumlah produk
                if ($status == 'Kadaluarsa' || $status == 'Ditolak' || $status == 'Gagal' || $status == "Dibatalkan") {
                    $dataTransaksiFulDariDatabase_items = json_decode($dataTransaksiFulDariDatabase['items'], true);
                    foreach ($dataTransaksiFulDariDatabase_items as $item) {
                        //bentuk item
                        //     {
                        //         "id":"B20240214223820",
                        //         "name":"Rak Serbaguna - RSD 80 (Putih)",
                        //         "value":517000,
                        //         "quantity":1
                        //     }

                        $barangCurr = $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->first();
                        $varianSelected = rtrim(explode("(", $item['name'])[1], ")");
                        if (count(json_decode($barangCurr['varian'], true)) > count(explode(",", $barangCurr['stok']))) {
                            $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                                'stok' => (int)$barangCurr['stok'] + $item['quantity']
                            ])->update();
                        } else {
                            $stokTerbaru = explode(",", $barangCurr['stok']);
                            $stokSelected = $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))];
                            $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))] = (int)$stokSelected + $item['quantity'];
                            $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                                'stok' => implode(",", $stokTerbaru)
                            ])->update();
                        }
                    }

                    //aktifkan kemabli voucher claimednya
                    if ($dataTransaksiFulDariDatabase['idVoucher'] != 0) {
                        $this->voucherClaimedModel->where([
                            'id_voucher' => $dataTransaksiFulDariDatabase['idVoucher'],
                            'email_user' => $dataTransaksiFulDariDatabase['email_cus']
                        ])->set(['active' => true])->update();
                    }

                    //aktifkan kembali poinnya
                    if ($dataTransaksiFulDariDatabase['pakai_poin'] > 0) {
                        $pembeliCur = $this->pembeliModel->where(['email_user' => $dataTransaksiFulDariDatabase['email_cus']])->first();
                        $poinCur = json_decode($pembeliCur['poin'], true);
                        $poinCounter = 0;
                        $poinCurFinal = $dataTransaksiFulDariDatabase['pakai_poin'];
                        foreach ($poinCur as $ind_p => $p) {
                            if (!$p['active']) {
                                $poinCur[$ind_p]['active'] = true;
                                if ((int)$p['nominal'] == 0) {
                                    $sisa = $poinCurFinal - $poinCounter;
                                    $poinCur[$ind_p]['nominal'] = $sisa;
                                }
                            } else {
                                if ($poinCurFinal > $poinCounter) {
                                    $sisa = $poinCurFinal - $poinCounter;
                                    $poinCur[$ind_p]['nominal'] = (int)$p['nominal'] + $sisa;
                                }
                                break;
                            }
                            $poinCounter += (int)$p['nominal'];
                        }
                        $this->pembeliModel->where(['email_user' => $dataTransaksiFulDariDatabase['email_cus']])->set(['poin' => json_encode($poinCur)])->update();
                    }
                }
                $ws_url = env('WS_URL', 'DefaultValue');
                $client = new Client($ws_url);
                $client->send(json_encode([
                    'jenis' => 'order',
                    'id_order' => $order_id
                ]));
                $client->close();
            } else {
                // $this->pemesananModel->insert([
                //     'email_cus' => $customField['e'],
                //     'nama_pen' => $customField['n'],
                //     'hp_pen' => $customField['h'],
                //     'alamat_pen' => $customField['a'],
                //     'resi' => 'Menunggu pengiriman',
                //     'items' => json_encode($customField['i']),
                //     'kurir' => 'kosong',
                //     'id_midtrans' => $order_id,
                //     'status' => $status,
                //     'data_mid' => json_encode($body),
                //     'note' => $customField['nt'],
                //     'diskonVoucher' => $customField['v']['d']
                // ]);

                // if ($customField['v']['d'] > 0) {
                //     $voucherSelected = $this->voucherModel->where(['id' => $customField['v']['id']])->first();
                //     $voucherSelected_email = json_decode($voucherSelected['list_email'], true);
                //     array_push($voucherSelected_email, $customField['e']);
                //     $this->voucherModel->where(['id' => $customField['v']['id']])->set(['list_email' => json_encode($voucherSelected_email)])->update();
                // }

                // //pengurangan stok produk
                // $dataTransaksiFulDariDatabase = $this->pemesananModel->where('id_midtrans', $order_id)->first();
                // $dataTransaksiFulDariDatabase_items = json_decode($dataTransaksiFulDariDatabase['items'], true);
                // foreach ($dataTransaksiFulDariDatabase_items as $item) {
                //     $barangCurr = $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->first();
                //     $varianSelected = rtrim(explode("(", $item['name'])[1], ")");
                //     if (count(json_decode($barangCurr['varian'], true)) > count(explode(",", $barangCurr['stok']))) {
                //         $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                //             'stok' => (int)$barangCurr['stok'] - $item['quantity']
                //         ])->update();
                //     } else {
                //         $stokTerbaru = explode(",", $barangCurr['stok']);
                //         $stokSelected = $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))];
                //         $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))] = (int)$stokSelected - $item['quantity'];
                //         $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                //             'stok' => implode(",", $stokTerbaru)
                //         ])->update();
                //     }
                // }
            }
        } else if ($order_id_first_char == 'I') {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://ilenafurniture.com/updatetransaction",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Content-Type: application/json",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $arr['hasil_curl'] = json_decode($response, true);
        }
        // $this->pembeliModel->where('email_user', 'sahrulcbm@gmail.com')->set(['transaksi' => json_encode($body)])->update();

        return $this->response->setJSON($arr, false);
    }
    public function updateTransactionCoreAPI()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $order_id = $body['order_id'];
        $fraud = $body['fraud_status'];
        // if (isset($body['custom_field1'])) {
        //     $customField = json_decode($body['custom_field1'] . (isset($body['custom_field2']) ? $body['custom_field2'] : '') . (isset($body['custom_field3']) ? $body['custom_field3'] : ''), true);
        // }
        if ($fraud == "accept") {
            switch ($body['transaction_status']) {
                case 'settlement':
                    $status = "Proses";
                    break;
                case 'capture':
                    $status = "Proses";
                    break;
                case 'pending':
                    $status = "Menunggu Pembayaran";
                    break;
                case 'expire':
                    $status = "Kadaluarsa";
                    break;
                case 'deny':
                    $status = "Ditolak";
                    break;
                case 'failure':
                    $status = "Gagal";
                    break;
                case 'refund':
                    $status = "Refund";
                    break;
                case 'partial_refund':
                    $status = "Partial Refund";
                    break;
                case 'cancel':
                    $status = "Dibatalkan";
                    break;
                default:
                    $status = "No Status";
                    break;
            }
        } else {
            $status = 'Forbidden';
        }

        $order_id_first_char = substr($order_id, 0, 1);
        if ($order_id_first_char == 'J') {
            $dataTransaksi_curr = $this->pemesananModel->getPemesanan($order_id);
            if (isset($dataTransaksi_curr)) {
                $dataMid_curr = json_decode($dataTransaksi_curr['data_mid'], true);
                $dataMid_curr['transaction_status'] = $body['transaction_status'];
                $this->pemesananModel->where('id_midtrans', $order_id)->set([
                    'status' => $status,
                    'data_mid' => json_encode($dataMid_curr),
                ])->update();

                //reset jumlah produk
                if ($status == 'Kadaluarsa' || $status == 'Ditolak' || $status == 'Gagal' || $status == "Dibatalkan") {
                    $dataTransaksiFulDariDatabase = $this->pemesananModel->where('id_midtrans', $order_id)->first();
                    $dataTransaksiFulDariDatabase_items = json_decode($dataTransaksiFulDariDatabase['items'], true);
                    foreach ($dataTransaksiFulDariDatabase_items as $item) {
                        $barangCurr = $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->first();

                        $varianSelected = rtrim(explode("(", $item['name'])[1], ")");
                        if (count(json_decode($barangCurr['varian'], true)) > count(explode(",", $barangCurr['stok']))) {
                            $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                                'stok' => (int)$barangCurr['stok'] + $item['quantity']
                            ])->update();
                        } else {
                            $stokTerbaru = explode(",", $barangCurr['stok']);
                            $stokSelected = $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))];
                            $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))] = (int)$stokSelected + $item['quantity'];
                            $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                                'stok' => implode(",", $stokTerbaru)
                            ])->update();
                        }
                    }
                }
            } else {
                // $this->pemesananModel->insert([
                //     'email_cus' => $customField['e'],
                //     'nama_pen' => $customField['n'],
                //     'hp_pen' => $customField['h'],
                //     'alamat_pen' => $customField['a'],
                //     'resi' => 'Menunggu pengiriman',
                //     'items' => json_encode($customField['i']),
                //     'kurir' => 'kosong',
                //     'id_midtrans' => $order_id,
                //     'status' => $status,
                //     'data_mid' => json_encode($body),
                // ]);
                $this->pemesananModel->insert([
                    'id_midtrans' => $order_id,
                    'status' => $status,
                    'data_mid' => json_encode($body),
                ]);
            }
        } else if ($order_id_first_char == 'I') {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://ilenafurniture.com/updatetransaction",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Content-Type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return "cURL Error #:" . $err;
            }
        }
        // $this->pembeliModel->where('email_user', 'sahrulcbm@gmail.com')->set(['transaksi' => json_encode($body)])->update();
        $arr = [
            'success' => true,
        ];
        return $this->response->setJSON($arr, false);
    }

    public function updateTransactionBackup()
    {
        // $bodyJson = $this->request->getBody();
        // $body = json_decode($bodyJson, true);
        \Midtrans\Config::$serverKey = "";
        \Midtrans\Config::$isProduction = true;

        $notif = new \Midtrans\Notification();
        $transaction = $notif->transaction_status;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;
        $customerDetails = $notif->customer_details;

        if ($fraud == "accept") {
            switch ($transaction) {
                case 'settlement':
                    $status = "Proses";
                    break;
                case 'capture':
                    $status = "Proses";
                    break;
                case 'pending':
                    $status = "Menunggu Pembayaran";
                    break;
                case 'expire':
                    $status = "Kadaluarsa";
                    break;
                case 'deny':
                    $status = "Ditolak";
                    break;
                case 'failure':
                    $status = "Gagal";
                    break;
                case 'refund':
                    $status = "Refund";
                    break;
                case 'partial_refund':
                    $status = "Partial Refund";
                    break;
                case 'cancel':
                    $status = "Dibatalkan";
                    break;
                default:
                    $status = "No Status";
                    break;
            }
            $dataTransaksi_curr = $this->pemesananModel->getPemesanan($order_id);

            if (isset($dataTransaksi_curr)) {
                $this->pembeliModel->where('email_user', 'sahrulcbm@gmail.com')->set(['transaksi' => json_encode($customerDetails)])->update();
            }
            $dataMid_curr = json_decode($dataTransaksi_curr['data_mid'], true);
            $dataMid_curr['transaction_status'] = $transaction;
            $this->pemesananModel->where('id_midtrans', $order_id)->set([
                'status' => $status,
                'data_mid' => json_encode($dataMid_curr),
            ])->update();
        }
        $arr = [
            'success' => true,
        ];
        return $this->response->setJSON($arr, false);
    }
    public function orderLocal()
    {
        $data = [
            'title' => 'Peroses Pembayaran',
            'dataMid' => [
                'gross_amount' => 156000
            ],
            'va_number' => '',
            'biller_code' => '029941234123',
            'bank' => 'mandiri',
            'waktuExpire' => '24 Maret 2024'
        ];
        return view('pages/orderExpire', $data);
    }
    public function order($id_midtrans = false)
    {
        $pemesanan = $this->pemesananModel->getPemesanan($id_midtrans);
        $carapembayaran = [
            'bca' => [
                [
                    'nama' => 'myBCA',
                    'isi' => '1. Login ke myBCA<br>
                                2. Pilih Transfer dan pilih Virtual Account<br>
                                3. Pilih Transfer ke tujuan baru<br>
                                4. Masukkan nomor Virtual Account dari e-commerce dan klik Lanjut<br>
                                5. Pilih rekening sumber dana (jika memiliki lebih dari satu), masukkan nominal dan klik Lanjut<br>
                                6. Cek detail transaksi, klik Lanjut<br>
                                7. Masukkan PIN dan transaksi berhasil'
                ],
                [
                    'nama' => 'BCA Mobile',
                    'isi' => '1. Login ke BCA mobile<br>
                                2. Pilih m-Transfer dan pilih BCA Virtual Account<br>
                                3. Masukkan nomor BCA Virtual Account dari e-commerce dan klik Send<br>
                                4. Masukkan nominal<br>
                                5. Cek detail transaksi, klik OK<br>
                                6. Masukkan PIN dan transaksi berhasil'
                ],
                [
                    'nama' => 'KlikBCA',
                    'isi' => '1. Login ke KlikBCA<br>
                                2. Pilih Transfer Dana dan pilih Transfer ke BCA Virtual Account<br>
                                3. Masukkan nomor BCA Virtual Account dari e-commerce dan klik Lanjutkan<br>
                                4. Masukkan nominal dan klik Lanjutkan<br>
                                5. Masukkan Respon KeyBCA Appli 1 dan klik Kirim<br>
                                6. Transaksi berhasil dilakukan'
                ],
                [
                    'nama' => 'ATM BCA',
                    'isi' => '1. Masukkan Kartu ATM dan PIN di ATM BCA<br>
                                2. Pilih Penarikan Tunai/Transaksi Lainnya<br>
                                3. Pilih Transaksi Lainnya<br>
                                4. Pilih Transfer<br>
                                5. Pilih menu Ke Rek BCA Virtual Account<br>
                                6. Masukkan nomor BCA Virtual Account dan klik Benar<br>
                                7. Cek detail transaksi dan pilih Ya<br>
                                8. Transaksi berhasil'
                ]
            ],
            'mandiri' => [
                [
                    'nama' => 'Livin by Mandiri',
                    'isi' => '1. Pilih bayar pada menu utama.<br>
                                2. Pilih Ecommerce.<br>
                                3. Pilih Midtrans di bagian penyedia jasa.<br>
                                4. Masukkan nomor virtual account pada bagian kode bayar.<br>
                                5. Klik lanjutkan untuk konfirmasi.<br>
                                6. Pembayaran selesai.'
                ],
                [
                    'nama' => 'ATM Mandiri',
                    'isi' => '1. Pilih bayar/beli pada menu utama.<br>
                                2. Pilih lainnya.<br>
                                3. Pilih multi payment.<br>
                                4. Masukkan kode perusahaan Midtrans 70012.<br>
                                5. Masukkan kode pembayaran, lalu konfirmasi.<br>
                                6. Pembayaran selesai.'
                ],
                [
                    'nama' => 'Mandiri Internet Banking',
                    'isi' => '1. Pilih bayar pada menu utama.<br>
                                2. Pilih multi payment.<br>
                                3. Pilih dari rekening.<br>
                                4. Pilih Midtrans di bagian penyedia jasa.<br>
                                5. Masukkan kode pembayaran, lalu konfirmasi.<br>
                                6. Pembayaran selesai.'
                ],
            ],
            'bni' => [
                [
                    'nama' => 'ATM BNI',
                    'isi' => '1. Pilih menu lain pada menu utama.<br>
                                2. Pilih transfer.<br>
                                3. Pilih ke rekening BNI.<br>
                                4. Masukkan nomor rekening pembayaran.<br>
                                5. Masukkan jumlah yang akan dibayar, lalu konfirmasi.<br>
                                6. Pembayaran berhasil.<br>
                                7. Internet Banking'
                ],
                [
                    'nama' => 'BNI Internet Banking',
                    'isi' => '1. Pilih transaksi, lalu info & administrasi transfer.<br>
                                2. Pilih atur rekening tujuan.<br>
                                3. Masukkan informasi rekening, lalu konfirmasi.<br>
                                4. Pilih transfer, lalu transfer ke rekening BNI.<br>
                                5. Masukkan detail pembayaran, lalu konfirmasi.<br>
                                6. Pembayaran berhasil.'
                ],
                [
                    'nama' => 'BNI Mobile Banking',
                    'isi' => '1. Pilih transfer.<br>
                                2. Pilih virtual account billing.<br>
                                3. Pilih rekening debit yang akan digunakan.<br>
                                4. Masukkan nomor virtual account, lalu konfirmasi.<br>
                                5. Pembayaran berhasil.'
                ],
            ],
            'bri' => [
                [
                    'nama' => 'ATM BRI',
                    'isi' => '1. Pilih transaksi lainnya pada menu utama.<br>
                                2. Pilih pembayaran.<br>
                                3. Pilih lainnya.<br>
                                4. Pilih BRIVA.<br>
                                5. Masukkan nomor BRIVA, lalu konfirmasi.<br>
                                6. Pembayaran berhasil.'
                ],
                [
                    'nama' => 'IB BRI',
                    'isi' => '1. Pilih pembayaran & pembelian.<br>
                                2. Pilih BRIVA.<br>
                                3. Masukkan nomor BRIVA, lalu konfirmasi.<br>
                                4. Pembayaran berhasil.'
                ],
                [
                    'nama' => 'BRImo',
                    'isi' => '1. Pilih pembayaran.<br>
                                2. Pilih BRIVA.<br>
                                3. Masukkan nomor BRIVA, lalu konfirmasi.<br>
                                4. Pembayaran berhasil.'
                ],
            ],
            'permata' => [
                [
                    'nama' => 'ATM Permata/ALTO',
                    'isi' => '1. Pilih transaksi lainnya pada menu utama.<br>
                                2. Pilih pembayaran.<br>
                                3. Pilih pembayaran lainnya.<br>
                                4. Pilih virtual account.<br>
                                5. Masukkan nomor virtual account Permata, lalu konfirmasi.<br>
                                6. Pembayaran berhasil.'
                ],
            ],
            'cimb' => [
                [
                    'nama' => 'ATM CIMB Niaga',
                    'isi' => '1. Pilih pembayaran pada menu utama.<br>
                                2. Pilih virtual account.<br>
                                3. Masukkan nomor virtual account, lalu konfirmasi.<br>
                                4. Pembayaran selesai.'
                ],
                [
                    'nama' => 'OCTO Clicks',
                    'isi' => '1. Pilih pembayaran tagihan pada menu utama.<br>
                                2. Pilih mobile rekening virtual.<br>
                                3. Masukkan nomor virtual account, lalu klik lanjut untuk verifikasi detail.<br>
                                4. Pilih kirim OTP untuk lanjut.<br>
                                5. Masukkan OTP yang dikirimkan ke nomor HP Anda, lalu konfirmasi.<br>
                                6. Pembayaran selesai.'
                ],
                [
                    'nama' => 'OCTO Mobile',
                    'isi' => '1. Pilih menu transfer.<br>
                                2. Pilih transfer to other CIMB Niaga account.<br>
                                3. Pilih sumber dana: CASA atau rekening ponsel.<br>
                                4. Masukkan nomor virtual account.<br>
                                5. Masukkan jumlah yang akan dibayar.<br>
                                6. Ikuti instruksi untuk menyelesaikan pembayaran.<br>
                                7. Pembayaran selesai.'
                ],
            ],
            'qris' => [
                [
                    'nama' => 'QRIS',
                    'isi' => '1. Buka aplikasi yang mendukung pembayaran dengan QRIS.<br>
                                2. Download atau pindai QRIS pada layar.<br>
                                3. Konfirmasi pembayaran pada aplikasi.<br>
                                4. Pembayaran berhasil.'
                ],
            ],
            'gopay' => [
                [
                    'nama' => 'GoPay',
                    'isi' => '1. Klik Bayar sekarang.<br>
                                2. Aplikasi Gojek atau GoPay akan terbuka.<br>
                                3. Konfirmasi pembayaran di aplikasi Gojek atau GoPay.<br>
                                4. Pembayaran berhasil.'
                ],
            ],
            'shopeepay' => [
                [
                    'nama' => 'ShopeePay',
                    'isi' => '1. Buka aplikasi Shopee atau e-wallet lain Anda.<br>
                                2. Download atau pindai QRIS pada layar.<br>
                                3. Konfirmasi pembayaran pada aplikasi.<br>
                                4. Pembayaran berhasil.'
                ],
            ],
            'card' => 'Always Success'
        ];
        if ($pemesanan) {
            $dataMid = json_decode($pemesanan['data_mid'], true);
            // dd($pemesanan);
            $kurir = $pemesanan['kurir'];
            $items = json_decode($pemesanan['items'], true);
            $ws_url = env('WS_URL', 'DefaultValue');
            switch ($pemesanan['status']) {
                case 'Menunggu Pembayaran':
                    $biller_code = "";
                    $bank = "";
                    switch ($dataMid['payment_type']) {
                        case 'bank_transfer':
                            if (isset($dataMid['permata_va_number'])) {
                                $va_number = $dataMid['permata_va_number'];
                                $bank = "permata";
                            } else {
                                $va_number = $dataMid['va_numbers'][0]['va_number'];
                                $bank = $dataMid['va_numbers'][0]['bank'];
                            }
                            break;
                        case 'echannel':
                            $va_number = $dataMid['bill_key'];
                            $biller_code = $dataMid['biller_code'];
                            $bank = "mandiri";
                            break;
                        case 'qris':
                            $va_number = 'https://api.' . (in_array($pemesanan['email_cus'], $this->emailUjiCoba) ? 'sandbox.' : '') . 'midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
                            $bank = "qris";
                            break;
                        case 'gopay':
                            $va_number = $dataMid['actions'];
                            $bank = "gopay";
                            break;
                        case 'shopeepay':
                            $va_number = $dataMid['actions'];
                            $bank = "shopeepay";
                            break;
                        case 'credit_card':
                            $va_number = '';
                            $bank = "card";
                            break;
                        default:
                            $va_number = "";
                            break;
                    }

                    $waktuExpire = strtotime($dataMid['expiry_time']);
                    $waktuCurr = strtotime("+7 Hours");
                    $waktuSelisih = $waktuExpire - $waktuCurr;
                    $waktu = date("H:i:s", $waktuSelisih);

                    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    $data = [
                        'title' => 'Peroses Pembayaran',
                        'pemesanan' => $pemesanan,
                        'dataMid' => $dataMid,
                        'va_number' => $va_number,
                        'biller_code' => $biller_code,
                        'bank' => $bank,
                        'items' => $items,
                        'waktu' => $waktu,
                        'caraPembayaran' => $carapembayaran[$bank],
                        'waktuExpire' => date("d", $waktuExpire) . " " . $bulan[(int)date("m", $waktuExpire) - 1] . " " . date("Y H:i:s", $waktuExpire),
                        'wsUrl' => $ws_url
                    ];
                    return view('pages/orderProgress', $data);
                    break;
                case 'Menunggu Pembayaran Rekening':
                    $biller_code = "";
                    $va_number = $dataMid['va_numbers'][0]['va_number'];
                    $bank = $dataMid['va_numbers'][0]['bank'];

                    $waktuExpire = strtotime($dataMid['expiry_time']);
                    $waktuCurr = strtotime("+7 Hours");
                    $waktuSelisih = $waktuExpire - $waktuCurr;
                    $waktu = date("H:i:s", $waktuSelisih);

                    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    $data = [
                        'title' => 'Peroses Pembayaran',
                        'pemesanan' => $pemesanan,
                        'dataMid' => $dataMid,
                        'va_number' => $va_number,
                        'biller_code' => $biller_code,
                        'bank' => $bank,
                        'items' => $items,
                        'waktu' => $waktu,
                        'caraPembayaran' => $carapembayaran[$bank],
                        'waktuExpire' => date("d", $waktuExpire) . " " . $bulan[(int)date("m", $waktuExpire) - 1] . " " . date("Y H:i:s", $waktuExpire),
                        'wsUrl' => $ws_url
                    ];
                    return view('pages/orderProgress', $data);
                    break;
                case 'Proses':
                    $biller_code = "";
                    $bank = "";
                    switch ($dataMid['payment_type']) {
                        case 'bank_transfer':
                            if (isset($dataMid['permata_va_number'])) {
                                $va_number = $dataMid['permata_va_number'];
                                $bank = "permata";
                            } else {
                                $va_number = $dataMid['va_numbers'][0]['va_number'];
                                $bank = $dataMid['va_numbers'][0]['bank'];
                            }
                            break;
                        case 'rekening':
                            $va_number = $dataMid['va_numbers'][0]['va_number'];
                            $bank = $dataMid['va_numbers'][0]['bank'];
                            break;
                        case 'echannel':
                            $va_number = $dataMid['bill_key'];
                            $biller_code = $dataMid['biller_code'];
                            $bank = "mandiri";
                            break;
                        case 'qris':
                            $va_number = 'https://api.' . (in_array($pemesanan['email_cus'], $this->emailUjiCoba) ? 'sandbox.' : '') . 'midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
                            $bank = "qris";
                            break;
                        case 'gopay':
                            $va_number = $dataMid['actions'];
                            $bank = "gopay";
                            break;
                        case 'shopeepay':
                            $va_number = $dataMid['actions'];
                            $bank = "shopeepay";
                            break;
                        case 'credit_card':
                            $va_number = '';
                            $bank = "card";
                            break;
                        default:
                            $va_number = "";
                            break;
                    }

                    $data = [
                        'title' => 'Pembayaran Sukes',
                        'pemesanan' => $pemesanan,
                        'dataMid' => $dataMid,
                        'kurir' => $kurir,
                        'items' => $items,
                        'bank' => $bank,
                        'va_number' => $va_number,
                        'biller_code' => $biller_code,
                        'caraPembayaran' => $carapembayaran[$bank],
                    ];
                    return view('pages/orderShipping', $data);
                    break;
                case 'Dikirim':
                    $biller_code = "";
                    $bank = "";
                    switch ($dataMid['payment_type']) {
                        case 'bank_transfer':
                            if (isset($dataMid['permata_va_number'])) {
                                $va_number = $dataMid['permata_va_number'];
                                $bank = "permata";
                            } else {
                                $va_number = $dataMid['va_numbers'][0]['va_number'];
                                $bank = $dataMid['va_numbers'][0]['bank'];
                            }
                            break;
                        case 'rekening':
                            $va_number = $dataMid['va_numbers'][0]['va_number'];
                            $bank = $dataMid['va_numbers'][0]['bank'];
                            break;
                        case 'echannel':
                            $va_number = $dataMid['bill_key'];
                            $biller_code = $dataMid['biller_code'];
                            $bank = "mandiri";
                            break;
                        case 'qris':
                            $va_number = 'https://api.' . (in_array($pemesanan['email_cus'], $this->emailUjiCoba) ? 'sandbox.' : '') . 'midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
                            $bank = "qris";
                            break;
                        case 'gopay':
                            $va_number = $dataMid['actions'];
                            $bank = "gopay";
                            break;
                        case 'shopeepay':
                            $va_number = $dataMid['actions'];
                            $bank = "shopeepay";
                            break;
                        case 'credit_card':
                            $va_number = '';
                            $bank = "card";
                            break;
                        default:
                            $va_number = "";
                            break;
                    }

                    $data = [
                        'title' => 'Pembayaran Sukes',
                        'pemesanan' => $pemesanan,
                        'dataMid' => $dataMid,
                        'kurir' => $kurir,
                        'items' => $items,
                        'bank' => $bank,
                        'va_number' => $va_number,
                        'biller_code' => $biller_code,
                        'caraPembayaran' => $carapembayaran[$bank],
                    ];
                    return view('pages/orderShipping', $data);
                    break;
                case 'Kadaluarsa':
                    $biller_code = "";
                    $bank = "";
                    switch ($dataMid['payment_type']) {
                        case 'bank_transfer':
                            if (isset($dataMid['permata_va_number'])) {
                                $va_number = $dataMid['permata_va_number'];
                                $bank = "permata";
                            } else {
                                $va_number = $dataMid['va_numbers'][0]['va_number'];
                                $bank = $dataMid['va_numbers'][0]['bank'];
                            }
                            break;
                        case 'echannel':
                            $va_number = $dataMid['bill_key'];
                            $biller_code = $dataMid['biller_code'];
                            $bank = "mandiri";
                            break;
                        case 'qris':
                            $va_number = 'https://api.' . (in_array($pemesanan['email_cus'], $this->emailUjiCoba) ? 'sandbox.' : '') . 'midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
                            $bank = "qris";
                            break;
                        case 'gopay':
                            $va_number = $dataMid['actions'];
                            $bank = "gopay";
                            break;
                        case 'shopeepay':
                            $va_number = $dataMid['actions'];
                            $bank = "shopeepay";
                            break;
                        case 'credit_card':
                            $va_number = '';
                            $bank = "card";
                            break;
                        default:
                            $va_number = "";
                            break;
                    }

                    $waktuExpire = strtotime($dataMid['expiry_time']);
                    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    $data = [
                        'title' => 'Peroses Pembayaran',
                        'pemesanan' => $pemesanan,
                        'dataMid' => $dataMid,
                        'va_number' => $va_number,
                        'biller_code' => $biller_code,
                        'bank' => $bank,
                        'items' => $items,
                        'caraPembayaran' => $carapembayaran[$bank],
                        'waktuExpire' => date("d", $waktuExpire) . " " . $bulan[(int)date("m", $waktuExpire) - 1] . " " . date("Y H:i:s", $waktuExpire),
                    ];
                    return view('pages/orderExpire', $data);
                    break;
                case 'Dibatalkan':
                    $biller_code = "";
                    $bank = "";
                    switch ($dataMid['payment_type']) {
                        case 'bank_transfer':
                            if (isset($dataMid['permata_va_number'])) {
                                $va_number = $dataMid['permata_va_number'];
                                $bank = "permata";
                            } else {
                                $va_number = $dataMid['va_numbers'][0]['va_number'];
                                $bank = $dataMid['va_numbers'][0]['bank'];
                            }
                            break;
                        case 'rekening':
                            $va_number = $dataMid['va_numbers'][0]['va_number'];
                            $bank = $dataMid['va_numbers'][0]['bank'];
                            break;
                        case 'echannel':
                            $va_number = $dataMid['bill_key'];
                            $biller_code = $dataMid['biller_code'];
                            $bank = "mandiri";
                            break;
                        case 'qris':
                            $va_number = 'https://api.' . (in_array($pemesanan['email_cus'], $this->emailUjiCoba) ? 'sandbox.' : '') . 'midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
                            $bank = "qris";
                            break;
                        case 'gopay':
                            $va_number = $dataMid['actions'];
                            $bank = "gopay";
                            break;
                        case 'shopeepay':
                            $va_number = $dataMid['actions'];
                            $bank = "shopeepay";
                            break;
                        case 'credit_card':
                            $va_number = '';
                            $bank = "card";
                            break;
                        default:
                            $va_number = "";
                            break;
                    }

                    $waktuExpire = strtotime($dataMid['expiry_time']);
                    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    $data = [
                        'title' => 'Peroses Pembayaran',
                        'pemesanan' => $pemesanan,
                        'dataMid' => $dataMid,
                        'va_number' => $va_number,
                        'biller_code' => $biller_code,
                        'bank' => $bank,
                        'items' => $items,
                        'caraPembayaran' => $carapembayaran[$bank],
                        'waktuExpire' => date("d", $waktuExpire) . " " . $bulan[(int)date("m", $waktuExpire) - 1] . " " . date("Y H:i:s", $waktuExpire)
                    ];
                    return view('pages/orderExpire', $data);
                    break;
                case 'Ditolak':
                    $status = "Ditolak";
                    break;
                case 'Gagal':
                    $status = "Gagal";
                    break;
                case 'Refund':
                    $status = "Refund";
                    break;
                case 'Partial Refund':
                    $status = "Partial Refund";
                    break;
                case 'Dibatalkan':
                    $status = "Dibatalkan";
                    break;
            }
        } else {
            return redirect()->to('/');
        }
    }

    public function updateTier()
    {
        $pembeli = $this->pembeliModel->findAll();
        foreach ($pembeli as $p) {
            $tier = json_decode($p['tier'], true);
            $data = $tier['data'];
            $dataBaru = [];
            $jumlah = 0;
            foreach ($data as $d) {
                $waktuCurr = strtotime("+7 Hours");
                $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
                if ($waktuCurrYmd <= strtotime($d['kadaluarsa'])) {
                    $jumlah += (int)$d['nominal'];
                    array_push($dataBaru, $d);
                }
            }
            if ($jumlah < 10000000) {
                $label = 'bronze';
            } else if ($jumlah < 50000000) {
                $label = 'silver';
            } else if ($jumlah < 100000000) {
                $label = 'gold';
            } else if ($jumlah >= 100000000) {
                $label = 'platinum';
            }
            $tier['label'] = $label;
            $tier['data'] = $dataBaru;
            $this->pembeliModel->where(['email_user' => $p['email_user']])->set(['tier' => json_encode($tier)])->update();
        }
        return $this->response->setJSON(['success' => true], false);
    }
    public function point()
    {
        $tgl_lahir = session()->get('tgl_lahir');
        $poinSession = session()->get("poin");
        $tier = session()->get("tier");
        $poin = 0;
        $waktuCurr = strtotime("+7 Hours");
        $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
        $adaYgExpire = false;
        foreach ($poinSession as $ind_p => $p) {
            $waktuExpire = strtotime($p['kadaluarsa']);
            if ($waktuCurrYmd <= $waktuExpire) {
                if ($p['active']) {
                    $poin += (int)$p['nominal'];
                }
            } else {
                $adaYgExpire = true;
            }
        }
        if ($adaYgExpire) return $this->actionLogout();

        $bonus = [
            'bronze' => [
                [
                    'nominal' => '25k',
                    'nama' => 'Bonus Ulang Tahun',
                    'keterangan' => 'Dapatkan 25.000 poin bonus pada bulan ulang tahunmu!'
                ],
                [
                    'nominal' => '5%',
                    'nama' => 'Diskon Produk Private Label',
                    'keterangan' => 'Dapatkan diskon 5% untuk barang-barang private label'
                ],
                [
                    'nominal' => '',
                    'nama' => 'Voucher Bulanan Eksklusif',
                    'keterangan' => 'Dapatkan voucher belanja eksklusif setiap bulan pada program spesial'
                ],
                [
                    'nominal' => '',
                    'nama' => 'Dapatkan & Gunakan Poin',
                    'keterangan' => 'Dapatkan poin yang bisa kamu gunakan untuk bertransaksi'
                ],
            ],
            'silver' => [
                [
                    'nominal' => '10%',
                    'nama' => 'Voucher Diskon',
                    'keterangan' => 'Nikmati diskon hingga 10% untuk barang tertentu tiap bulannya'
                ],
            ],
            'gold' => [
                [
                    'nominal' => '25k',
                    'nama' => 'Voucher Ulang Tahun',
                    'keterangan' => 'Nikmati voucher belanja eksklusif senilai Rp25.000 di hari ulang tahunmu.'
                ],
                [
                    'nominal' => '',
                    'nama' => 'Early Access untuk Promo',
                    'keterangan' => 'Akses ke berbagai produk dan promo lebih awal dari pengguna lainnya.'
                ],
            ],
            'platinum' => [
                [
                    'nominal' => '',
                    'nama' => 'Customer Care Khusus',
                    'keterangan' => 'Jalur customer care khusus yang kamu bisa hubungi langsung dan tersedia 24 jam.'
                ],
                [
                    'nominal' => '50k',
                    'nama' => 'Diskon Khusus',
                    'keterangan' => 'Discount khusus senilai 50rb tiap bulan nya dengan minimum pembelian'
                ],
            ],
        ];
        if (!$tgl_lahir) {
            $bonus['bronze'][0]['ket_nonaktif'] = 'Isi tanggal lahir Kamu pada profile untuk mendapatkan bonus ini!';
            $bonus['gold'][0]['ket_nonaktif'] = 'Isi tanggal lahir Kamu pada profile untuk mendapatkan bonus ini!';
        }
        $data = [
            'title' => 'Luna Reward',
            'poin' => $poin,
            'tier' => $tier,
            'bonus' => $bonus
        ];
        return view('pages/point', $data);
    }
    public function pointHistory()
    {
        $email = session()->get('email');
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        $history  = $this->pointHistoryModel->getHistoryCus($email);
        // $history = [
        //     [
        //         'label' => 'Transaksi Pembelian',
        //         'nominal' => 24000,
        //         'keterangan' => 'Point dari 5% total pembelian',
        //         'tanggal' => '2024-12-05'
        //     ],
        //     [
        //         'label' => 'Bonus Ulang Tahun',
        //         'nominal' => 25000,
        //         'keterangan' => 'Point dari pembelian tahun ulang tahun',
        //         'tanggal' => '2024-12-10'
        //     ],
        //     [
        //         'label' => 'Pembelian',
        //         'nominal' => -10000,
        //         'keterangan' => 'Pengurangan dari pembelian pada transaksi L00002123',
        //         'tanggal' => '2024-12-11'
        //     ],
        // ];
        $data = [
            'title' => 'Luna Point History',
            'history' => $history,
            'bulan' => $bulan
        ];
        return view('pages/pointHistory', $data);
    }
    public function pointUse()
    {
        session()->set('usepoin', true);
        return redirect()->to('/checkout');
    }
    public function pointCancel()
    {
        session()->remove('usepoin');
        return redirect()->to('/checkout');
    }

    public function account()
    {
        $nama = session()->get("nama");
        $nohp = session()->get("nohp");
        $tgl_lahir = session()->get("tgl_lahir");
        $foto = session()->get("foto");
        $email = session()->get("email");
        $getCurPembeli = $this->pembeliModel->getPembeli($email);
        $waktuCurr = strtotime(date('Y-m-d', strtotime('+7 Hours')));
        $waktuBatas = strtotime($getCurPembeli['batas_tgl_lahir']);
        $waktuSelisih = $waktuBatas - $waktuCurr;

        $data = [
            'title' => 'Akun Saya',
            'nama' => $nama,
            'nohp' => $nohp,
            'tgl_lahir' => $tgl_lahir,
            'foto' => $foto,
            'batas_tgl_lahir' => $getCurPembeli['batas_tgl_lahir'] ? explode('-', $getCurPembeli['batas_tgl_lahir'])[2] . '/' . explode('-', $getCurPembeli['batas_tgl_lahir'])[1] . '/' . explode('-', $getCurPembeli['batas_tgl_lahir'])[0] : null,
            'kurang_dari' => $waktuSelisih >= 0 ? true : false,
            'msg' => session()->get('msg') ? session()->get('msg') : false
        ];
        return view('pages/account', $data);
    }

    public function editAccount()
    {
        $email = session()->get("email");
        $role = session()->get("role");
        $sandi = $this->request->getVar('sandi');
        $nama = $this->request->getVar('nama');
        $nohp = $this->request->getVar('nohp');
        $tgl_lahir = $this->request->getVar('tgl_lahir');
        $foto = $this->request->getFile('foto')->isValid() ? file_get_contents($this->request->getFile('foto')) : false;
        $getCurPembeli = $this->pembeliModel->getPembeli($email);

        if ($tgl_lahir != $getCurPembeli['tgl_lahir']) {
            if ($getCurPembeli['batas_tgl_lahir']) {
                $waktuCurr = strtotime(date('Y-m-d', strtotime('+7 Hours')));
                $waktuBatas = strtotime($getCurPembeli['batas_tgl_lahir']);
                $waktuSelisih = $waktuBatas - $waktuCurr;
                if ($waktuSelisih >= 0) {
                    session()->setFlashdata('msg', 'Tanggal lahir belum dapat diubah');
                    return redirect()->to('/account');
                }
            }
        }

        if ($sandi != '') {
            $this->userModel->where('email', $email)->set([
                'sandi' => password_hash($sandi, PASSWORD_DEFAULT),
            ])->update();
        }
        if ($role == '0') {
            $this->pembeliModel->where('email_user', $email)->set([
                'nama' => $nama,
                'nohp' => $nohp,
                'tgl_lahir' => $tgl_lahir,
                'batas_tgl_lahir' => date("Y-m-d", strtotime('+1 year')),
                'foto' => $foto ? '/imguser/' . base64_encode($email) : session()->get('foto')
            ])->update();

            if ($foto) {
                if (session()->get('foto') != '/imguser/ZGVmYXVsdA==') {
                    $this->gambarUserModel->where(['email_user' => $email])->set(['gambar' => $foto])->update();
                } else {
                    $this->gambarUserModel->where(['email_user' => $email])->insert(['email_user' => $email, 'gambar' => $foto]);
                }
            }

            session()->set([
                'nama' => $nama,
                'nohp' => $nohp,
                'tgl_lahir' => $tgl_lahir,
                'foto' => $foto ? '/imguser/' . base64_encode($email) : session()->get('foto')
            ]);
        }

        session()->setFlashdata('msg', 'Akun Anda telah diperbarui');
        return redirect()->to('/account');
    }
    public function contact()
    {
        $artikel = $this->artikelModel->orderBy('rand()')->limit(3)->findAll();
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        foreach ($artikel as $ind_a => $a) {
            $artikel[$ind_a]['header'] = '/imgart/' . $a['id'];
            $artikel[$ind_a]['isi'] = json_decode($a['isi'], true);
            $artikel[$ind_a]['kategori'] = explode(",", $a['kategori']);
            $artikel[$ind_a]['waktu'] = date("d", strtotime($a['waktu'])) . " " . $bulan[date("m", strtotime($a['waktu'])) - 1] . " " . date("Y", strtotime($a['waktu']));
        }
        $data = [
            'title' => 'Kontak',
            'artikel' => $artikel
        ];
        return view('pages/contact', $data);
    }
    public function about()
    {
        $data = [
            'title' => 'Tentang'
        ];
        return view('pages/about', $data);
    }
    public function product($nama = false)
    {
        $produk = $this->barangModel->getBarangNama($nama);
        if (!$produk) return redirect()->to('/all');
        $produksekategori = $this->barangModel->where(['active' => '1', 'subkategori' => $produk['subkategori']])->where('id !=', $produk['id'])->orderBy('tracking_pop', 'desc')->findAll(10, 0);
        $gambarnya = $this->gambarBarangModel->getGambar($produk['id']);
        $varian = json_decode($produk['varian'], true);
        $dimensi = explode("X", $produk['dimensi']);

        $this->barangModel->where(['id' => $produk['id']])->set([
            'tracking_pop' => (int)$produk['tracking_pop'] + 1
        ])->update();

        $metakeywords = [str_replace('-', ' ', $produk['subkategori']), str_replace('-', ' ', $produk['subkategori']) . ' lunarea', str_replace('-', ' ', $produk['subkategori']) . ' semarang'];
        foreach ($varian as $v) {
            array_push($metakeywords, str_replace('-', ' ', $produk['subkategori']) . ' ' . $v);
            array_push($metakeywords, str_replace('-', ' ', $produk['subkategori']) . ' ' . $v . ' lunarea');
            array_push($metakeywords, str_replace('-', ' ', $produk['subkategori']) . ' ' . $v . ' semarang');
        }
        $data = [
            'title' => $produk['nama'],
            'produk' => $produk,
            'gambar' => $gambarnya,
            'varian' => $varian,
            'dimensi' => $dimensi,
            'produksekategori' => $produksekategori,
            'msg' => session()->getFlashdata('msg'),
            'geser_container_melayang' => true,
            'stok' => $produk['stok'],
            'metaKeyword' => implode(',', $metakeywords)
        ];
        return view('pages/product', $data);
    }

    public function productFilter($namaDash, $page = 1)
    {
        $nama = str_replace("-", " ", $namaDash);
        $pagination = (int)$page;
        if ($pagination > 1) {
            $hitungOffset = 20 * ($pagination - 1);
            $produk = $this->barangModel->where(['active' => '1'])->like("pencarian", $nama, "both")->orderBy('pencarian', 'asc')->findAll(20, $hitungOffset);
        } else {
            $produk = $this->barangModel->where(['active' => '1'])->like("pencarian", $nama, "both")->orderBy('pencarian', 'asc')->findAll(20, 0);
        }
        $semuaproduk = $this->barangModel->where(['active' => '1'])->like("pencarian", $nama, "both")->orderBy('pencarian', 'asc')->findAll();

        $data = [
            'title' => 'Produk',
            'produk' => $produk,
            'nama' => $nama,
            'kategori' => false,
            'semuaProduk' => $semuaproduk,
            'page' => $page,
        ];
        return view('pages/all', $data);
    }

    public function invoice($id_mid)
    {
        $transaksi = $this->pemesananModel->getPemesanan($id_mid);
        $arr = [
            'id' => $transaksi['id'],
            'email_cus' => $transaksi['email_cus'],
            'nama_pen' => $transaksi['nama_pen'],
            'hp_pen' => $transaksi['hp_pen'],
            'alamat_pen' => $transaksi['alamat_pen'],
            'resi' => $transaksi['resi'],
            'id_midtrans' => $transaksi['id_midtrans'],
            'items' => json_decode($transaksi['items'], true),
            'status' => $transaksi['status'],
            'kurir' => $transaksi['kurir'],
            'data_mid' => json_decode($transaksi['data_mid'], true),
        ];
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        $data = [
            'title' => 'Print Preview',
            'transaksi' => $arr,
            'transaksiJson' => json_encode($arr),
            'bulan' => $bulan
        ];
        return view('pages/invoice', $data);
    }
    public function fixKurir()
    {
        $transaksi = $this->pemesananModel->findAll();
        foreach ($transaksi as $t) {
            if ($t['kurir'] != 'kosong') {
                $this->pemesananModel->where(['id' => $t['id']])->set(['kurir' => 'Lunarea - Darat'])->update();
            }
        }
        return $this->response->setJSON(['success' => true], false);
    }
    public function isitier($urutan)
    {
        if ($urutan == '1') {
            $pembeli = $this->pembeliModel->findAll();
            foreach ($pembeli as $p) {
                $this->pembeliModel->where(['email_user' => $p['email_user']])->set(['tier' => json_encode([
                    'label' => 'bronze',
                    'data' => []
                ])])->update();
            }
        } else if ($urutan == '2') {
            $pemesanan = $this->pemesananModel->findAll();
            foreach ($pemesanan as $p) {
                $dataMid = json_decode($p['data_mid'], true);
                $transaction_time = explode(" ", $dataMid['transaction_time'])[0];
                $kadaluarsa = (((int)explode("-", $transaction_time)[0]) + 1) . "-" . explode("-", $transaction_time)[1] . "-" . explode("-", $transaction_time)[2];
                $nominal = (int)$dataMid['gross_amount'];
                $idPesanan = $dataMid['order_id'];
                $datanya = [
                    'kadaluarsa' => $kadaluarsa,
                    'nominal' => $nominal,
                    'id_pesanan' => $idPesanan,
                ];

                $pembeli = $this->pembeliModel->where(['email_user' => $p['email_cus']])->first();
                if ($pembeli) {
                    $getTier = json_decode($pembeli['tier'], true);
                    $waktuCurr = strtotime("+7 Hours");
                    // $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
                    if ($waktuCurr <= strtotime($kadaluarsa)) {
                        array_push($getTier['data'], $datanya);
                        $this->pembeliModel->where(['email_user' => $p['email_cus']])->set(['tier' => json_encode($getTier)])->update();
                    }
                }
            }
        } else if ($urutan == '3') {
            $pembeli = $this->pembeliModel->findAll();
            foreach ($pembeli as $p) {
                $tier = json_decode($p['tier'], true);
                $data = $tier['data'];
                $jumlah = 0;
                foreach ($data as $d) {
                    $jumlah += (int)$d['nominal'];
                }
                if ($jumlah < 10000000) {
                    $label = 'bronze';
                } else if ($jumlah < 50000000) {
                    $label = 'silver';
                } else if ($jumlah < 100000000) {
                    $label = 'gold';
                } else if ($jumlah >= 100000000) {
                    $label = 'platinum';
                }
                $tier['label'] = $label;
                $this->pembeliModel->where(['email_user' => $p['email_user']])->set(['tier' => json_encode($tier)])->update();
            }
        }
        return $this->response->setJSON(['success' => true], false);
    }
    public function qris($string)
    {
        $auth = base64_encode("" . ":"); //yg kiri midtrans server key
        $order_id = explode("-", $string)[0];
        $amount = (int)explode("-", $string)[1];
        $body = [
            "payment_type" => "qris",
            "transaction_details" => [
                "order_id" => $order_id,
                "gross_amount" => $amount,
            ],
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.midtrans.com/v2/charge",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic " . $auth,
                "content-type: application/json",
                "Accept: application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $qris = json_decode($response, true);
        dd($qris);
    }

    //============ ADMIN ==============//
    public function listForm()
    {
        $form = $this->formModel->getForm();
        $data = [
            'title' => 'List Formulir',
            'form' => $form
        ];
        return view('pages/listForm', $data);
    }
    public function listCustomer($page = 1, $status = 'all')
    {
        $transaksiCus = $this->pemesananModel->getPemesananPage($page);
        $semuaTransaksiCus = $this->pemesananModel->getPemesanan();
        $transaksiCusNoJSON = [];
        $semuaTransaksiCusFilter = [];
        foreach ($semuaTransaksiCus as $t) {
            if ($status == 'Proses') {
                if ($t['status'] == 'Proses') array_push($semuaTransaksiCusFilter, $t);
            } else if ($status == 'Menunggu-Pembayaran') {
                if ($t['status'] == 'Menunggu Pembayaran') array_push($semuaTransaksiCusFilter, $t);
            } else if ($status == 'Menunggu-Pembayaran-Rekening') {
                if ($t['status'] == 'Menunggu Pembayaran Rekening') array_push($semuaTransaksiCusFilter, $t);
            } else if ($status == 'Kadaluarsa') {
                if ($t['status'] == 'Kadaluarsa') array_push($semuaTransaksiCusFilter, $t);
            } else if ($status == 'Ditolak') {
                if ($t['status'] == 'Ditolak') array_push($semuaTransaksiCusFilter, $t);
            } else if ($status == 'Dibatalkan') {
                if ($t['status'] == 'Dibatalkan') array_push($semuaTransaksiCusFilter, $t);
            } else if ($status == 'Dikirim') {
                if ($t['status'] == 'Dikirim') array_push($semuaTransaksiCusFilter, $t);
            } else if ($status == 'Selesai') {
                if ($t['status'] == 'Selesai') array_push($semuaTransaksiCusFilter, $t);
            } else if ($status == 'all') {
                array_push($semuaTransaksiCusFilter, $t);
            }
        }
        foreach ($transaksiCus as $transaksi) {
            $arr = [
                'id' => $transaksi['id'],
                'email_cus' => $transaksi['email_cus'],
                'nama_pen' => $transaksi['nama_pen'],
                'hp_pen' => $transaksi['hp_pen'],
                'alamat_pen' => $transaksi['alamat_pen'],
                'resi' => $transaksi['resi'],
                'id_midtrans' => $transaksi['id_midtrans'],
                'items' => json_decode($transaksi['items'], true),
                'status' => $transaksi['status'],
                'kurir' => $transaksi['kurir'],
                'data_mid' => json_decode($transaksi['data_mid'], true),
                'note' => $transaksi['note'],
                'bukti_bayar' => $transaksi['bukti_bayar'] ? '/imgbuktibayar/' . $transaksi['id_midtrans'] : false,
            ];
            if ($status == 'Proses') {
                if ($transaksi['status'] == 'Proses') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'Menunggu-Pembayaran') {
                if ($transaksi['status'] == 'Menunggu Pembayaran') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'Menunggu-Pembayaran-Rekening') {
                if ($transaksi['status'] == 'Menunggu Pembayaran Rekening') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'Kadaluarsa') {
                if ($transaksi['status'] == 'Kadaluarsa') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'Ditolak') {
                if ($transaksi['status'] == 'Ditolak') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'Dibatalkan') {
                if ($transaksi['status'] == 'Dibatalkan') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'Dikirim') {
                if ($transaksi['status'] == 'Dikirim') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'Selesai') {
                if ($transaksi['status'] == 'Selesai') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'all') {
                array_push($transaksiCusNoJSON, $arr);
            }
        }
        $transaksiJson = json_encode($transaksiCusNoJSON);
        $ws_url = env('WS_URL', 'DefaultValue');
        $data = [
            'title' => 'List Customer',
            'transaksiCus' => $transaksiCusNoJSON,
            'semuaTransaksiCus' => $semuaTransaksiCusFilter,
            'transaksiJson' => $transaksiJson,
            'page' => $page,
            'status' => $status,
            'wsUrl' => $ws_url
        ];
        return view('pages/listCustomer', $data);
    }
    public function payOrderConfirm($id_midtrans, $confirm)
    {
        $dataTransaksi_curr = $this->pemesananModel->getPemesanan($id_midtrans);
        if (!$dataTransaksi_curr) {
            session()->setFlashdata('msg', 'Pesanan tidak ditemukan');
            return redirect()->to('/listcustomer');
        }
        $dataMid_curr = json_decode($dataTransaksi_curr['data_mid'], true);
        switch ($confirm) {
            case 'cancel':
                $dataMid_curr['transaction_status'] = 'cancel';
                $status = 'Dibatalkan';
                break;
            case 'accept':
                $dataMid_curr['transaction_status'] = 'settlement';
                $status = 'Proses';
                break;
            default:
                session()->setFlashdata('msg', 'Konfirmasi salah');
                return redirect()->to('/listcustomer');
                break;
        }
        $this->pemesananModel->where('id_midtrans', $id_midtrans)->set([
            'status' => $status,
            'data_mid' => json_encode($dataMid_curr),
        ])->update();

        $dataTransaksiFulDariDatabase = $this->pemesananModel->where('id_midtrans', $id_midtrans)->first();
        if ($status == 'Proses') {
            if ($dataTransaksiFulDariDatabase['idVoucher'] != 0) {
                $this->voucherClaimedModel->where([
                    'id_voucher' => $dataTransaksiFulDariDatabase['idVoucher'],
                    'email_user' => $dataTransaksiFulDariDatabase['email_cus']
                ])->delete();
            }

            //tambah poin
            $emailCus = $dataTransaksiFulDariDatabase['email_cus'];
            $pembeli = $this->pembeliModel->getPembeli($emailCus);
            if ($pembeli) {
                $waktuCurr = strtotime("+7 Hours");
                $waktuCurrYmd = date("Y-m-d", $waktuCurr);
                $poin = json_decode($pembeli['poin'], true);
                if ($dataTransaksiFulDariDatabase['cashback'] > 0) {
                    $voucherSelected = $this->voucherModel->getVoucher($dataTransaksiFulDariDatabase['idVoucher']);
                    $cashback = (int)$dataTransaksiFulDariDatabase['cashback'];

                    $kadaluarsa = date("Y-m-d", strtotime($voucherSelected['durasi_poin'], strtotime($waktuCurrYmd)));
                    $dataPoinNew = [
                        'kadaluarsa' => $kadaluarsa,
                        'nominal' => $cashback,
                        'active' => true
                    ];
                    array_push($poin, $dataPoinNew);
                    $this->pointHistoryModel->insert([
                        'id' => $waktuCurr,
                        'label' => $voucherSelected['nama'],
                        'nominal' => $voucherSelected['nominal'],
                        'keterangan' => 'Cashback voucher ' . strtolower($voucherSelected['nama']),
                        'tanggal' => $waktuCurrYmd,
                        'email_user' => $emailCus
                    ]);
                }
                //kalo pakai poin
                if ($dataTransaksiFulDariDatabase['pakai_poin'] > 0) {
                    //add point history
                    $this->pointHistoryModel->insert([
                        'id' => $waktuCurr,
                        'label' => 'Pembelian',
                        'nominal' => $dataTransaksiFulDariDatabase['pakai_poin'],
                        'keterangan' => 'Pembelian dengan id pemesanan ' . $id_midtrans,
                        'tanggal' => $waktuCurrYmd,
                        'email_user' => $emailCus
                    ]);
                    //update poin pembeli
                    $poinArrIndexTerpakai = [];
                    foreach ($poin as $ind_p => $p) {
                        if (!$p['active']) {
                            array_push($poinArrIndexTerpakai, $ind_p);
                        }
                    }
                    foreach ($poinArrIndexTerpakai as $p) {
                        unset($poin[$p]);
                    }
                }
                $poinBaru = array_values($poin);
                $this->pembeliModel->where(['email_user' => $emailCus])->set(['poin' => json_encode($poinBaru)])->update();
                //tambah tier
                $tier = json_decode($pembeli['tier'], true);
                $data = $tier['data'];
                $kadaluarsa = date("Y-m-d", strtotime("+1 year", strtotime($waktuCurrYmd)));
                array_push($data, [
                    'kadaluarsa' => $kadaluarsa,
                    'nominal' => (int)$dataMid_curr['gross_amount'],
                    'id_pesanan' => $id_midtrans
                ]);
                $dataBaru = [];
                $jumlah = 0;
                foreach ($data as $d) {
                    $waktuCurr = strtotime("+7 Hours");
                    $waktuCurrYmd = strtotime(date("Y-m-d", $waktuCurr));
                    if (date("m-d", $waktuCurr) == '01-01') {
                        if ($waktuCurrYmd <= strtotime($d['kadaluarsa'])) {
                            $jumlah += (int)$d['nominal'];
                            array_push($dataBaru, $d);
                        }
                    } else {
                        $jumlah += (int)$d['nominal'];
                        array_push($dataBaru, $d);
                    }
                }
                if ($jumlah < 10000000) {
                    $label = 'bronze';
                } else if ($jumlah < 50000000) {
                    $label = 'silver';
                } else if ($jumlah < 100000000) {
                    $label = 'gold';
                } else if ($jumlah >= 100000000) {
                    $label = 'platinum';
                }
                $tier['label'] = $label;
                $tier['data'] = $dataBaru;
                $this->pembeliModel->where(['email_user' => $emailCus])->set(['tier' => json_encode($tier)])->update();
            }
        }
        //reset jumlah produk
        if ($status == 'Kadaluarsa' || $status == 'Ditolak' || $status == 'Gagal' || $status == "Dibatalkan") {
            $dataTransaksiFulDariDatabase_items = json_decode($dataTransaksiFulDariDatabase['items'], true);
            foreach ($dataTransaksiFulDariDatabase_items as $item) {
                //bentuk item
                //     {
                //         "id":"B20240214223820",
                //         "name":"Rak Serbaguna - RSD 80 (Putih)",
                //         "value":517000,
                //         "quantity":1
                //     }

                $barangCurr = $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->first();
                $varianSelected = rtrim(explode("(", $item['name'])[1], ")");
                if (count(json_decode($barangCurr['varian'], true)) > count(explode(",", $barangCurr['stok']))) {
                    $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                        'stok' => (int)$barangCurr['stok'] + $item['quantity']
                    ])->update();
                } else {
                    $stokTerbaru = explode(",", $barangCurr['stok']);
                    $stokSelected = $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))];
                    $stokTerbaru[array_search($varianSelected, json_decode($barangCurr['varian'], true))] = (int)$stokSelected + $item['quantity'];
                    $this->barangModel->where('nama', rtrim(explode("(", $item['name'])[0]))->set([
                        'stok' => implode(",", $stokTerbaru)
                    ])->update();
                }
            }

            //aktifkan kemabli voucher claimednya
            if ($dataTransaksiFulDariDatabase['idVoucher'] != 0) {
                $this->voucherClaimedModel->where([
                    'id_voucher' => $dataTransaksiFulDariDatabase['idVoucher'],
                    'email_user' => $dataTransaksiFulDariDatabase['email_cus']
                ])->set(['active' => true])->update();
            }

            //aktifkan kembali poinnya
            if ($dataTransaksiFulDariDatabase['pakai_poin'] > 0) {
                $pembeliCur = $this->pembeliModel->where(['email_user' => $dataTransaksiFulDariDatabase['email_cus']])->first();
                $poinCur = json_decode($pembeliCur['poin'], true);
                $poinCounter = 0;
                $poinCurFinal = $dataTransaksiFulDariDatabase['pakai_poin'];
                foreach ($poinCur as $ind_p => $p) {
                    if (!$p['active']) {
                        $poinCur[$ind_p]['active'] = true;
                        if ((int)$p['nominal'] == 0) {
                            $sisa = $poinCurFinal - $poinCounter;
                            $poinCur[$ind_p]['nominal'] = $sisa;
                        }
                    } else {
                        if ($poinCurFinal > $poinCounter) {
                            $sisa = $poinCurFinal - $poinCounter;
                            $poinCur[$ind_p]['nominal'] = (int)$p['nominal'] + $sisa;
                        }
                        break;
                    }
                    $poinCounter += (int)$p['nominal'];
                }
                $this->pembeliModel->where(['email_user' => $dataTransaksiFulDariDatabase['email_cus']])->set(['poin' => json_encode($poinCur)])->update();
            }
        }

        $ws_url = env('WS_URL', 'DefaultValue');
        $client = new Client($ws_url);
        $client->send(json_encode([
            'jenis' => 'order',
            'id_order' => $id_midtrans
        ]));
        $client->close();
        return redirect()->to('/listcustomer');
    }
    public function pdf($id_mid)
    {
        $transaksi = $this->pemesananModel->getPemesanan($id_mid);
        $arr = [
            'id' => $transaksi['id'],
            'nama_cus' => $transaksi['nama_pen'],
            'email_cus' => $transaksi['email_cus'],
            'hp_cus' => $transaksi['hp_pen'],
            'nama_pen' => $transaksi['nama_pen'],
            'hp_pen' => $transaksi['hp_pen'],
            'alamat_pen' => $transaksi['alamat_pen'],
            'resi' => $transaksi['resi'],
            'id_midtrans' => $transaksi['id_midtrans'],
            'items' => json_decode($transaksi['items'], true),
            'status' => $transaksi['status'],
            'kurir' => $transaksi['kurir'],
            'data_mid' => json_decode($transaksi['data_mid'], true),
        ];
        $data = [
            'title' => 'Print Preview',
            'transaksi' => $arr,
            'transaksiJson' => json_encode($arr),
        ];
        return view('pages/pdf', $data);
    }
    public function orderDone($id_midtrans)
    {
        $this->pemesananModel->where(['id_midtrans' => $id_midtrans])->set(['status' => 'Selesai'])->update();
        return redirect()->to('/listcustomer');
    }
    public function editResi()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $this->pemesananModel->where('id_midtrans', $body['idMid'])->set([
            'resi' => $body['resi'],
            'kurir' => $body['kurir'],
            'status' => 'Dikirim',
        ])->update();

        $list_item = "";
        foreach ($body['data']['items'] as $item) {
            $list_item = $list_item . "<p>" . $item['quantity'] . " " . $item['name'] . "</p>";
        }

        $this->kirimPesanEmail($body['data']['email_cus'], 'Lunarea Store - Pesananmu sudah dikirim', "<p>Berikut nomor resi pada pesanan " . $body['data']['id_midtrans'] . "</p>
        <h1>" . $body['resi'] . '</h1>
        <p style="margin-bottom: 10px">' . $body['data']['kurir'] . '</p>
        <span style="margin-bottom: 10px>-------------------------------------------------</span>       
        <p style="margin-bottom: 10px"><b>Informasi terkait pesanan</b></p>
        <p>Nama : ' . $body['data']['nama_cus'] . '</p>
        <p>Email : ' . $body['data']['email_cus'] . '</p>
        <p style="margin-bottom: 10px">Kode Pesanan : ' . $body['data']['id_midtrans'] . '</p>
        <p>Item Pesanan :</p>' . $list_item);

        $arr = [
            'success' => true,
            'status' => 'Dikirim',
            'resi' => $body['resi']
        ];
        return $this->response->setJSON($arr, false);
    }
    public function listProduct($page = 1)
    {
        $produk = $this->barangModel->getBarangPageAdmin($page);
        $semuaproduk = $this->barangModel->getBarangAdmin();
        $data = [
            'title' => 'List Produk',
            'produk' => $produk,
            'page' => $page,
            'semuaProduk' => $semuaproduk,
            'cari' => false
        ];
        return view('pages/listProduct', $data);
    }
    public function listProductTable()
    {
        $produk = $this->barangModel->getBarangAdmin();
        $data = [
            'title' => 'List Produk Tabel',
            'produk' => $produk
        ];
        return view('pages/listProductTable', $data);
    }
    public function actionFindProductAdmin()
    {
        $cari = $this->request->getVar('cari');
        if ($cari)
            return redirect()->to('/findproductadmin/' . str_replace(" ", "-", $cari));
        else
            return redirect()->to('/listproduct');
    }
    public function findProductAdmin($cari, $pag = 1)
    {
        // if ($cari = '') return redirect()->to('/listproduct');
        // dd($cari);
        $hitungPag = 20 * ($pag - 1);
        if ($pag > 1) {
            $produk = $this->barangModel->like('nama', str_replace('-', ' ', $cari), 'both')->orderBy('nama', 'asc')->findAll(20, $hitungPag);
        } else {
            $produk = $this->barangModel->like('nama', str_replace('-', ' ', $cari), 'both')->orderBy('nama', 'asc')->findAll(20, 0);
        }
        $semuaproduk = $this->barangModel->like('nama', str_replace('-', ' ', $cari), 'both')->orderBy('nama', 'asc')->findAll();
        $data = [
            'title' => 'List Produk',
            'produk' => $produk,
            'page' => $pag,
            'semuaProduk' => $semuaproduk,
            'cari' => $cari
        ];
        // dd($data);
        return view('pages/listProduct', $data);
    }
    public function addProduct()
    {
        $data = [
            'title' => 'Tambah Produk'
        ];
        return view('pages/addProduct', $data);
    }
    public function actionAddProduct()
    {
        $d = strtotime("+7 Hours");
        $tanggal = "B" . date("YmdHis", $d);
        $varian = explode(",", $this->request->getVar('varian'));
        $hasilVarian = count(explode(",", $this->request->getVar('varian'))) + (int)$this->request->getVar('jml_varian') - 1;
        $gambarnya = [];
        $insertGambarBarang = [
            'id' => $tanggal
        ];
        for ($i = 1; $i <= $hasilVarian; $i++) {
            array_push($gambarnya, file_get_contents($this->request->getFile("gambar" . $i)));
            $insertGambarBarang["gambar" . $i] = file_get_contents($this->request->getFile("gambar" . $i));
        }
        // dd([
        //     'varian' => $varian,
        //     'hasilVarian' => $hasilVarian,
        //     'gambar1' => file_get_contents($this->request->getFile("gambar1")),
        //     'gambarnya' => $gambarnya
        // ]);
        $path = str_replace("- ", "", $this->request->getVar('nama'));
        $path = str_replace(".", "", $path);
        $path = str_replace("& ", "", $path);
        $path = str_replace("+ ", "", $path);
        $path = str_replace("| ", "", $path);
        $path = str_replace("[", "", $path);
        $path = str_replace("]", "", $path);
        $path = str_replace(" ", "-", $path);
        $path = strtolower($path);
        $this->barangModel->insert([
            'id'            => $tanggal,
            'nama'          => $this->request->getVar('nama'),
            'path'          => $path,
            'pencarian'     => $this->request->getVar('pencarian'),
            'gambar'        => $gambarnya[0],
            'harga'         => $this->request->getVar('harga'),
            'berat'         => $this->request->getVar('berat'),
            'stok'          => $this->request->getVar('stok'),
            'dimensi'       => $this->request->getVar('dimensi'),
            'deskripsi'     => $this->request->getVar('deskripsi'),
            'kategori'      => $this->request->getVar('kategori'),
            'subkategori'   => $this->request->getVar('subkategori'),
            'diskon'        => $this->request->getVar('diskon'),
            'varian'        => json_encode($varian),
            'jml_varian'    => $this->request->getVar('jml_varian'),
            'shopee'        => $this->request->getVar('shopee'),
            'tokped'        => $this->request->getVar('tokped'),
            'tiktok'        => $this->request->getVar('tiktok'),
            'youtube'       => $this->request->getVar('youtube'),
            'active'        => '1',
            'deskripsi_nonhtml'     => $this->request->getVar('deskripsi_nonhtml'),
        ]);
        $this->gambarBarangModel->insert($insertGambarBarang);

        session()->setFlashdata('msg', 'Produk berhasil ditambahkan');
        // return redirect()->to('/listproduct');
        return redirect()->to('/product/' . urlencode($this->request->getVar('nama')));
    }
    // public function gantiUkuranDisplay() {
    //     $image = \Config\Services::image();
    //     $image->resize()
    // }
    public function editProduct($id)
    {
        $produk = $this->barangModel->getBarangAdmin($id);
        $gambar = $this->gambarBarangModel->getGambar($id);
        $varian = json_decode($produk['varian'], true);
        if ($produk['pencarian'] == null || $produk['pencarian'] == '') {
            $diskon = '';
            $varianJadi = '';
            foreach ($varian as $va) {
                $varianJadi = $varianJadi . $produk['kategori'] . " " . $va . " " . str_replace("-", " ", $produk['subkategori']) . " " . $va . " ";
            }
            if ((int)$produk['diskon'] > 0) {
                $diskon = $produk['kategori'] . " promo " . str_replace("-", " ", $produk['subkategori']) . " promo " . $produk['kategori'] . " diskon " . str_replace("-", " ", $produk['subkategori']) . " diskon ";
            }

            $produk['pencarian'] = $produk['nama'] . " " . $produk['kategori'] . " elegan " . $produk['kategori'] . " simpel " . $produk['kategori'] . " minimalis " . $produk['kategori'] . " estetik " . $produk['kategori'] . " modern " . str_replace("-", " ", $produk['subkategori']) . " elegan " . str_replace("-", " ", $produk['subkategori']) . " simpel " . str_replace("-", " ", $produk['subkategori']) . " minimalis " . str_replace("-", " ", $produk['subkategori']) . " estetik " . str_replace("-", " ", $produk['subkategori']) . " modern " . $varianJadi . $diskon;
        }
        $data = [
            'title'     => 'Edit Produk',
            'produk'    => $produk,
            'gambar'    => $gambar,
            'varian'    => implode(',', $varian)
        ];
        return view('pages/editProduct', $data);
    }

    public function actionEditProduct($id)
    {
        $varian = explode(",", $this->request->getVar('varian'));
        // dd(file_get_contents($this->request->getFile("gambar1")));
        $path = str_replace("- ", "", $this->request->getVar('nama'));
        $path = str_replace(".", "", $path);
        $path = str_replace("& ", "", $path);
        $path = str_replace("+ ", "", $path);
        $path = str_replace("| ", "", $path);
        $path = str_replace("[", "", $path);
        $path = str_replace("]", "", $path);
        $path = str_replace(" ", "-", $path);
        $path = strtolower($path);

        if (!empty($_FILES['gambar1']['tmp_name'])) {
            $hasilVarian = count(explode(",", $this->request->getVar('varian'))) + (int)$this->request->getVar('jml_varian') - 1;
            $gambarnya = [];
            $insertGambarBarang = [
                'id' => $id
            ];
            for ($i = 1; $i <= $hasilVarian; $i++) {
                array_push($gambarnya, file_get_contents($this->request->getFile("gambar" . $i)));
                $insertGambarBarang["gambar" . $i] = file_get_contents($this->request->getFile("gambar" . $i));
            }

            $this->barangModel->save([
                'id'            => $id,
                'nama'          => $this->request->getVar('nama'),
                'path'          => $path,
                'pencarian'     => $this->request->getVar('pencarian'),
                'gambar'        => $gambarnya[0],
                'harga'         => $this->request->getVar('harga'),
                'berat'         => $this->request->getVar('berat'),
                'stok'          => $this->request->getVar('stok'),
                'dimensi'       => $this->request->getVar('dimensi'),
                'deskripsi'     => $this->request->getVar('deskripsi'),
                'deskripsi_nonhtml'     => $this->request->getVar('deskripsi_nonhtml'),
                'kategori'      => $this->request->getVar('kategori'),
                'subkategori'   => $this->request->getVar('subkategori'),
                'diskon'        => $this->request->getVar('diskon'),
                'varian'        => json_encode($varian),
                'jml_varian'    => $this->request->getVar('jml_varian'),
                'shopee'        => $this->request->getVar('shopee'),
                'tokped'        => $this->request->getVar('tokped'),
                'tiktok'        => $this->request->getVar('tiktok'),
                'youtube'       => $this->request->getVar('youtube'),
            ]);
            $this->gambarBarangModel->save($insertGambarBarang);

            $getGambarCur = $this->gambarBarangModel->where(['id' => $id])->first();
            $jumlahGambarCur = 0;
            foreach ($getGambarCur as $g) {
                if ($g->isValid()) {
                    $jumlahGambarCur++;
                }
            }
            if ($jumlahGambarCur > $hasilVarian) {
                $offset = $jumlahGambarCur - $hasilVarian;
                $setGambarJadiNull = [];
                for ($i = 0; $i < $offset; $i++) {
                    $setGambarJadiNull['gambar' . ($hasilVarian + 1 + $i)] = null;
                }
                $this->gambarBarangModel->where(['id' => $id])->set($setGambarJadiNull)->update();
            }
        } else {
            $this->barangModel->save([
                'id'            => $id,
                'nama'          => $this->request->getVar('nama'),
                'path'          => $path,
                'pencarian'     => $this->request->getVar('pencarian'),
                'harga'         => $this->request->getVar('harga'),
                'berat'         => $this->request->getVar('berat'),
                'stok'          => $this->request->getVar('stok'),
                'dimensi'       => $this->request->getVar('dimensi'),
                'deskripsi'     => $this->request->getVar('deskripsi'),
                'deskripsi_nonhtml'     => $this->request->getVar('deskripsi_nonhtml'),
                'kategori'      => $this->request->getVar('kategori'),
                'subkategori'   => $this->request->getVar('subkategori'),
                'diskon'        => $this->request->getVar('diskon'),
                'varian'        => json_encode($varian),
                'jml_varian'    => $this->request->getVar('jml_varian'),
                'shopee'        => $this->request->getVar('shopee'),
                'tokped'        => $this->request->getVar('tokped'),
                'tiktok'        => $this->request->getVar('tiktok'),
                'youtube'       => $this->request->getVar('youtube'),
            ]);
        }

        session()->setFlashdata('msg', 'Produk telah diupdate');
        return redirect()->to('/product/' . urlencode($this->request->getVar('nama')));
    }
    public function delProduct($id)
    {
        $this->barangModel->where('id', $id)->delete();
        $this->gambarBarangModel->where('id', $id)->delete();
        return redirect()->to('/listproduct');
    }
    public function activeProduct($id)
    {
        $curActive = $this->barangModel->where(['id' => $id])->first()['active'];
        $this->barangModel->where(['id' => $id])->set(['active' => !$curActive])->update();
        return redirect()->to('/listproduct');
    }
    public function invoiceAdmin($id = false)
    {
        if ($id) {
            $invoice = $this->invoiceModel->getInvoice($id);
            $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
            $invoice['tanggalInv'] = date('dmY', strtotime($invoice['tanggal']));
            $invoice['tanggal'] = explode('-', $invoice['tanggal'])[2] . ' ' . $bulan[(int)explode('-', $invoice['tanggal'])[1] - 1] . ' ' . explode('-', $invoice['tanggal'])[0];
            $invoice['items'] = json_decode($invoice['items'], true);
            $data = [
                'title' => 'Invoice ' . $invoice['id'],
                'invoice' => $invoice,
            ];
            return view('pages/invoiceAdmin', $data);
        } else {
            $seluruhinvoice = $this->invoiceModel->getInvoice($id);
            $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
            foreach ($seluruhinvoice as $ind_inv => $invoice) {
                $seluruhinvoice[$ind_inv]['tanggal'] = explode('-', $invoice['tanggal'])[2] . ' ' . $bulan[(int)explode('-', $invoice['tanggal'])[1] - 1] . ' ' . explode('-', $invoice['tanggal'])[0];
                $seluruhinvoice[$ind_inv]['items'] = json_decode($invoice['items'], true);
            }
            $data = [
                'title' => 'List Invoice',
                'seluruhInvoice' => $seluruhinvoice
            ];
            return view('pages/listInvoiceAdmin', $data);
        }
    }
    public function addInvoiceAdmin()
    {
        $data = [
            'title' => 'Add Invoice',
            'msg' => session()->getFlashdata('msg')
        ];
        return view('pages/addInvoice', $data);
    }
    public function activeAddInvoiceAdmin()
    {
        if (!$this->validate([
            'tanggal' => ['rules' => 'required'],
            'id' => ['rules' => 'required'],
            'nama' => ['rules' => 'required'],
            'alamat' => ['rules' => 'required'],
            'produk-1-nama' => ['rules' => 'required'],
            'produk-1-kuantitas' => ['rules' => 'required'],
            'produk-1-harga' => ['rules' => 'required'],
        ])) {
            session()->setFlashdata('msg', 'Ada data yang belum terisi');
            return redirect()->to('/addinvoiceadmin')->withInput();
        }

        $tanggal = $this->request->getVar('tanggal');
        $id = $this->request->getVar('id');
        $nama = $this->request->getVar('nama');
        $alamat = $this->request->getVar('alamat');
        $hitungProduk = $this->request->getVar('hitungProduk');
        $items = [];
        for ($i = 1; $i <= $hitungProduk; $i++) {
            array_push($items, [
                'nama' => $this->request->getVar('produk-' . $i . '-nama'),
                'kuantitas' => $this->request->getVar('produk-' . $i . '-kuantitas'),
                'harga' => $this->request->getVar('produk-' . $i . '-harga'),
            ]);
        }
        $field = [
            'tanggal' => $tanggal,
            'id' => $id,
            'nama' => $nama,
            'alamat' => $alamat,
            'items' => json_encode($items),
        ];
        $this->invoiceModel->insert($field);
        session()->setFlashdata('msg', 'Invoice ' . $id . ' telah dibuat');
        return redirect()->to('/invoiceadmin');
    }
    public function listRedeem()
    {
        $redeem = $this->voucherRedeemModel->getVoucher();
        $voucher = $this->voucherModel->getVoucher();
        foreach ($redeem as $ind_r => $r) {
            $redeem[$ind_r]['email_user'] = json_decode($r['email_user'], true);
        }
        $data = [
            'title' => 'List Voucher',
            'redeem' => $redeem,
            'voucher' => $voucher
        ];
        return view('pages/listRedeem', $data);
    }
    public function addRedeem()
    {
        $voucher = $this->request->getVar('voucher');
        $code = $this->request->getVar('code');
        $this->voucherRedeemModel->insert([
            'id_voucher' => $voucher,
            'code' => $code,
            'email_user' => json_encode([])
        ]);
        return redirect()->to('/listredeem');
    }
    public function listVoucher()
    {
        $voucher = $this->voucherModel->findAll();
        $broadcast = session()->getFlashdata('broadcast');
        // $broadcast = 10;
        $emailBroadcast = false;
        foreach ($voucher as $ind_v => $v) {
            $voucher[$ind_v]['code'] = json_decode($v['code'], true);
            $voucher[$ind_v]['poster'] = '';
            $voucher[$ind_v]['poster_email'] = '';
            $voucher[$ind_v]['isi_email'] = '';
            $voucher[$ind_v]['syarat_ketentuan'] = '';
            if ($v['id'] == $broadcast) {
                $emailBroadcast = json_decode($v['code'], true);
            }
            if ($v['jadwal']) {
                $jadwal1  = date('d M Y', strtotime(explode('@', $v['jadwal'])[0]));
                $jadwal2  = date('d M Y', strtotime(explode('@', $v['jadwal'])[1]));
                $voucher[$ind_v]['jadwal'] = $jadwal1 . ' - ' . $jadwal2;
            }
        }

        $data = [
            'title' => 'List Voucher',
            'voucher' => $voucher,
            'voucherJson' => json_encode($voucher),
            'msg' => session()->getFlashdata('msg'),
            'broadcast' => $broadcast,
            'emailBroadcast' => $emailBroadcast,
            'emailBroadcastJson' => $emailBroadcast ? json_encode($emailBroadcast) : ''
        ];
        return view('pages/listVoucher', $data);
    }
    public function activeVoucher($id)
    {
        $curActive = $this->voucherModel->where(['id' => $id])->first()['active'];
        $this->voucherModel->where(['id' => $id])->set(['active' => !$curActive])->update();
        return redirect()->to('/listvoucher');
    }
    public function addVoucher()
    {
        $data = [
            'title' => 'Add Voucher',
            'msg' => session()->getFlashdata('msg')
        ];
        return view('pages/addVoucher', $data);
    }
    public function actionAddVoucher()
    {
        if (!$this->validate([
            'nama' => ['rules' => 'required'],
            'nominal' => ['rules' => 'required'],
            'keterangan' => ['rules' => 'required'],
        ])) {
            session()->setFlashdata('msg', 'Ada data yang belum diisi');
            return redirect()->to('/addvoucher')->withInput();
        }

        $nama = $this->request->getVar('nama');
        $nominal = $this->request->getVar('nominal');
        $satuan = $this->request->getVar('satuan');
        $jenis = $this->request->getVar('jenis');
        $durasiVoucher = $this->request->getVar('durasi');
        $durasiPoin = $this->request->getVar('durasi-poin');
        $keterangan = $this->request->getVar('keterangan');
        $email = explode('&', $this->request->getVar('email'));
        $setRedeemCode = $this->request->getVar('set-redeem');
        $allUserVoucher = $this->request->getVar('set-all-user-voucher');
        $autoClaimed = $this->request->getVar('auto-claimed');
        $kuota = $this->request->getVar('kuota');
        $poster = $this->request->getFile('poster')->isValid() ? file_get_contents($this->request->getFile('poster')) : null;
        $posterEmail = $this->request->getFile('poster-email')->isValid() ? file_get_contents($this->request->getFile('poster-email')) : null;
        $isiEmail = $this->request->getVar('isi-email');
        $broadcast = $this->request->getVar('broadcast');
        $jadwal1 = $this->request->getVar('jadwal1');
        $jadwal2 = $this->request->getVar('jadwal2');
        $syaratKetentuan = $this->request->getVar('syarat-ketentuan') ? $this->request->getVar('syarat-ketentuan') : null;

        if ($jadwal1) {
            if (!$jadwal2) {
                session()->setFlashdata('msg', 'Jadwal akhir belum di set');
                return redirect()->to('/addvoucher')->withInput();
            }
        }
        if ($jadwal2) {
            if (!$jadwal1) {
                session()->setFlashdata('msg', 'Jadwal awal belum di set');
                return redirect()->to('/addvoucher')->withInput();
            }
        }
        if (strtotime($jadwal1) > strtotime($jadwal2)) {
            session()->setFlashdata('msg', 'Jadwal harus dari kecil ke besar');
            return redirect()->to('/addvoucher')->withInput();
        }

        if ($broadcast) {
            if (!$posterEmail || !$isiEmail) {
                session()->setFlashdata('msg', 'Poster atau isi email belum di isi');
                return redirect()->to('/addvoucher')->withInput();
            }
        }

        if (!$this->request->getVar('email') && !$allUserVoucher) {
            session()->setFlashdata('msg', 'Email customer belum diisi');
            return redirect()->to('/addvoucher')->withInput();
        }

        $code = [];
        if ($allUserVoucher) {
            $getAllPembeli = $this->pembeliModel->findAll();
            foreach ($getAllPembeli as $p) {
                $databarunya = ['email_user' => $p['email_user']];
                if ($setRedeemCode) $databarunya['code'] = $this->generateRandomCode();
                array_push($code, $databarunya);
            }
        } else {
            foreach ($email as $e) {
                $databarunya = ['email_user' => $e];
                if ($setRedeemCode) $databarunya['code'] = $this->generateRandomCode();
                array_push($code, $databarunya);
            }
        }

        $insertData = [
            'nama' => $nama,
            'nominal' => $nominal,
            'satuan' => $satuan,
            'jenis' => $jenis,
            'durasi' => $durasiVoucher == 'null' ? null : $durasiVoucher,
            'durasi_poin' => $durasiPoin == 'null' ? null : $durasiPoin,
            'keterangan' => $keterangan,
            'code' => json_encode($code),
            'all_user' => $allUserVoucher ? true : false,
            'active' => $jadwal1 ? ($jadwal1 == date('Y-m-d', strtotime('+7 Hours')) ? true : false) : true,
            'auto_claimed' => $autoClaimed ? true : false,
            'kuota' => $kuota,
            'poster' => $poster,
            'poster_email' => $posterEmail,
            'isi_email' => $isiEmail,
            'jadwal' => $jadwal1 ? ($jadwal1 . "@" . $jadwal2) : null,
            'syarat_ketentuan' => $syaratKetentuan
        ];
        // dd($insertData);
        $this->voucherModel->insert($insertData);
        session()->setFlashdata('msg', 'Voucher berhasil dibuat');
        if ($insertData['active'] && $broadcast) {
            $voucherCurr = $this->voucherModel->where(['nama' => $nama, 'keterangan' => $keterangan])->first();
            session()->setFlashdata('broadcast', $voucherCurr['id']);
        }
        return redirect()->to('/listvoucher');
    }
    public function deleteVoucher($id_voucher)
    {
        $this->voucherModel->where(['id' => $id_voucher])->delete();
        session()->setFlashdata('msg', 'Voucher berhasil dihapus');
        return redirect()->to('/listvoucher');
    }
    public function actionAddVoucherAPI($id_voucher)
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $voucher = $this->voucherModel->getVoucher($id_voucher);
        return $this->response->setJSON($voucher, false);
    }
    public function actionBroadcastVoucher($id_voucher)
    {
        session()->setFlashdata('broadcast', $id_voucher);
        return redirect()->to('/listvoucher');
    }
    public function actionBroadcastVoucherEmail()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $email = $body['email'];
        $id_voucher = $body['idVoucher'];
        $voucherCurr = $this->voucherModel->getVoucher($id_voucher);
        $isiEmail  = $voucherCurr['isi_email'];
        $nama = $voucherCurr['nama'];
        $isinya = '
        <div style="width: 100%">
            <img
                src="https://lunareafurniture.com/imgvoucher/email/' . $voucherCurr['id'] . '"
                alt="banner"
                style="width: 100%; border-radius: 5px"
            />
        </div>
        <div style="height: 15px"></div>
        <div
            style="
                background-color: white;
                    padding-right: 20px;
                    padding-left: 20px;
                    padding-top: 20px;
                    padding-bottom: 20px;
                    border-radius: 10px;
                "
        >
            <table>
                <tbody>
                    ' . $isiEmail . '
                </tbody>
            </table>
        </div>
        ';
        $this->kirimPesanEmail($email, 'Lunarea Store - ' . $nama, $isinya);
        return $this->response->setStatusCode(200)->setJSON(['success' => true], false);
    }

    public function scheduleVoucher()
    {
        $getAllVoucher = $this->voucherModel->where('jadwal is NOT NULL')->findAll();
        foreach ($getAllVoucher as $ind_g => $g) {
            $getAllVoucher[$ind_g]['code'] = json_decode($g['code'], true);
            $getAllVoucher[$ind_g]['poster'] = '';
            $getAllVoucher[$ind_g]['poster_email'] = '';

            $code = [];
            if ($g['all_user']) {
                $getAllPembeli = $this->pembeliModel->findAll();
                foreach ($getAllPembeli as $p) {
                    $databarunya = ['email_user' => $p['email_user']];
                    array_push($code, $databarunya);
                }
            }

            if ($g['jadwal']) {
                $jadwal = explode('@', $g['jadwal']);
                $tglSkrg = date('Y-m-d', strtotime('+7 hours'));
                if ($jadwal[0] == $tglSkrg) {
                    $this->voucherModel->where(['id' => $g['id']])->set(['active' => true, 'code' => json_encode($code)])->update();
                }
                $tglAkhir = date('Y-m-d', strtotime('+1 day', strtotime($jadwal[1])));
                if ($tglAkhir == $tglSkrg) {
                    $this->voucherModel->where(['id' => $g['id']])->set(['active' => false])->update();
                }
            }
        }
        return $this->response->setStatusCode(200)->setJSON([
            'function' => 'scheduleVoucher',
            'success' => true
        ], false);
    }

    public function gantiUkuran($kategori)
    {
        $barangLama = $this->barangModel->where(['subkategori' => $kategori])->findAll();
        if (count($barangLama) <= 0) {
            return $this->response->setJSON(['message' => 'barang nggk nemu'], false);
        }
        function file_get_contents_curl($url)
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }

        foreach ($barangLama as $b) {
            $insertGambarBarang = [];
            $insertGambar300 = false;
            $jumlahGambar = count(json_decode($b['varian'], true)) - 1 + $b['jml_varian'];
            $dataGambar = $this->gambarBarangModel->where(['id' => $b['id']])->first();
            for ($i = 1; $i <= $jumlahGambar; $i++) {
                $gambarSelected = $dataGambar['gambar' . $i];
                $fp = 'imgdum/' . $b['id'] . '-' . $i . '.webp';
                file_put_contents($fp, $gambarSelected);
                \Config\Services::image()
                    ->withFile($fp)
                    ->resize(1000, 1000, true, 'height')->save('imgdum/1' . $b['id'] . '-' . $i . '.webp');
                $insertGambarBarang['gambar' . $i] = file_get_contents('imgdum/1' . $b['id'] . '-' . $i . '.webp');
                unlink('imgdum/1' . $b['id'] . '-' . $i . '.webp');
                if ($i == 1) {
                    \Config\Services::image()
                        ->withFile($fp)
                        ->resize(300, 300, true, 'height')->save('imgdum/1' . $b['id'] . '-' . $i . '300.webp');
                    $insertGambar300 = file_get_contents('imgdum/1' . $b['id'] . '-' . $i . '300.webp');
                    unlink('imgdum/1' . $b['id'] . '-' . $i . '300.webp');
                }
                unlink($fp);
            }
            $this->barangModel->where(['id' => $b['id']])->set(['gambar' => $insertGambar300])->update();
            $this->gambarBarangModel->where(['id' => $b['id']])->set($insertGambarBarang)->update();
        }
        return $this->response->setJSON([
            'success' => true
        ], false);
    }

    public function gantiUkuranArtikel()
    {
        $artikel = $this->artikelModel->findAll();
        if (count($artikel) <= 0) {
            return $this->response->setJSON(['message' => 'barang nggk nemu'], false);
        }
        foreach ($artikel as $a) {
            $insertGambarBarang = [];
            $dataGambar = $this->gambarArtikelModel->where(['id' => $a['id']])->first();
            unset($dataGambar['id']);
            foreach ($dataGambar as $ind_g => $g) {
                if ($g) {
                    $gambarSelected = $dataGambar[$ind_g];
                    $fp = 'imgdum/' . $a['id'] . '-' . $ind_g . '.webp';
                    file_put_contents($fp, $gambarSelected);
                    \Config\Services::image()
                        ->withFile($fp)
                        ->resize(1000, 1000, true, 'height')->save('imgdum/1' . $a['id'] . '-' . $ind_g . '.webp');
                    $insertGambarBarang[$ind_g] = file_get_contents('imgdum/1' . $a['id'] . '-' . $ind_g . '.webp');
                    unlink('imgdum/1' . $a['id'] . '-' . $ind_g . '.webp');
                } else {
                    break;
                }
            }
            $gambarHeader = $a['header'];
            $fp = 'imgdum/' . $a['id'] . '.webp';
            file_put_contents($fp, $gambarHeader);
            \Config\Services::image()
                ->withFile($fp)
                ->resize(1000, 1000, true, 'height')->save('imgdum/1' . $a['id'] . '.webp');
            $insertGambarHeader = file_get_contents('imgdum/1' . $a['id'] . '.webp');
            unlink('imgdum/1' . $a['id'] . '.webp');
            $this->artikelModel->where(['id' => $a['id']])->set(['header' => $insertGambarHeader])->update();
            if ($insertGambarBarang) $this->gambarArtikelModel->where(['id' => $a['id']])->set($insertGambarBarang)->update();
        }
        return $this->response->setJSON([
            'success' => true
        ], false);
    }

    public function cekDouble()
    {
        $cekSession = session()->get('cekdouble');
        if (!$cekSession) {
            $cekSession = 1;
        } else {
            $cekSession++;
        }
        session()->set('cekdouble', $cekSession);
        return redirect()->to('/about');
    }

    public function stokAdmin($idProduk = false, $pag = 1)
    {
        $offset = ($pag - 1) * 20;
        if ($idProduk) {
            $produk = $this->barangModel->getBarangAdmin($idProduk);
        } else {
            $produk = $this->barangModel->first();
        }
        $stok = $this->stokModel
            ->join('pembeli', 'pembeli.email_user = stok.email_admin', 'left')
            ->select('stok.*')
            ->select('pembeli.nama AS nama_admin')
            ->orderBy('id', 'asc')
            ->where(['id_barang' => $produk['id']])
            ->findAll(20, $offset);
        $produkAll = $this->barangModel
            ->select('barang.id')
            ->select('barang.nama')
            ->select('barang.stok')
            ->findAll();
        foreach ($stok as $ind_s => $s) {
            $stok[$ind_s]['tanggal'] = date('d/m/Y H:i:s', strtotime($s['tanggal']));
        }
        $produk['varian'] = json_decode($produk['varian'], true);
        $stokVarian = [];
        foreach ($produk['varian'] as $v) {
            $stokTerakhir = $this->stokModel->orderBy('tanggal', 'desc')->where([
                'id_barang' => $produk['id'],
                'varian' => $v
            ])->first();
            array_push($stokVarian, [
                'nama' => $v,
                'stok' => $stokTerakhir ? $stokTerakhir['stok_akhir'] : 0
            ]);
        }
        $data = [
            'title' => 'Stok',
            'produk' => $produk,
            'stok' => $stok,
            'stokJson' => json_encode($stok),
            'produkAll' => $produkAll,
            'produkAllJson' => json_encode($produkAll),
            'url' => base64_encode($idProduk ? '/stokadmin/' . $idProduk . '/' . $pag : '/stokadmin'),
            'msg' => session()->getFlashdata('msg'),
            'stokVarian' => $stokVarian
        ];
        // dd($data);
        return view('pages/stokAdmin', $data);
    }
    public function addStokAdmin($url)
    {
        $lastData = $this->stokModel->orderBy('id', 'desc')->where(['id_barang' => $this->request->getVar('id_barang'), 'varian' => $this->request->getVar('varian')])->first();
        if (!$lastData) $currentStok = 0;
        else $currentStok = $lastData['stok_akhir'];
        if ($this->request->getVar('jenis') == 'keluar')
            $currentStok -= (int)$this->request->getVar('jumlah');
        else
            $currentStok += (int)$this->request->getVar('jumlah');
        if ($currentStok < 0) {
            session()->setFlashdata('msg', 'Stok tidak mencukupi');
            return redirect()->to(base64_decode($url));
        }
        $produkSelected = $this->barangModel->where(['id' => $this->request->getVar('id_barang')])->first();
        $index_varian = array_search($this->request->getVar('varian'), json_decode($produkSelected['varian'], true));
        $stokCur = explode(',', $produkSelected['stok']);
        $stokCur[$index_varian] = $currentStok;

        $this->barangModel->where(['id' => $produkSelected['id']])->set(['stok' => implode(',', $stokCur)])->update();

        $this->stokModel->insert([
            'id' => time(),
            'id_barang' => $this->request->getVar('id_barang'),
            'nama' => $this->request->getVar('nama'),
            'varian' => $this->request->getVar('varian'),
            'jumlah' => ($this->request->getVar('jenis') == 'keluar' ? '-' : '') . $this->request->getVar('jumlah'),
            'email_admin' => session()->get('email'),
            'keterangan' => $this->request->getVar('keterangan'),
            'stok_akhir' => $currentStok,
            'tanggal' => date('Y-m-d H:i:s', strtotime('+7 Hours'))
        ]);
        return redirect()->to(base64_decode($url));
    }
    public function accStokAdmin($id, $url)
    {
        $lastData = $this->stokModel->orderBy('id', 'desc')->where(['id_barang' => $this->request->getVar('id_barang'), 'varian' => $this->request->getVar('varian')])->first();
        if (!$lastData) $currentStok = 0;
        else $currentStok = $lastData['stok_akhir'];
        if ($this->request->getVar('jenis') == 'keluar')
            $currentStok -= (int)$this->request->getVar('jumlah');
        else
            $currentStok += (int)$this->request->getVar('jumlah');
        if ($currentStok < 0) {
            session()->setFlashdata('msg', 'Stok tidak mencukupi');
            return redirect()->to(base64_decode($url));
        }
        $produkSelected = $this->barangModel->where(['id' => $this->request->getVar('id_barang')])->first();
        $index_varian = array_search($this->request->getVar('varian'), json_decode($produkSelected['varian'], true));
        $stokCur = explode(',', $produkSelected['stok']);
        $stokCur[$index_varian] = $currentStok;

        $this->barangModel->where(['id' => $produkSelected['id']])->set(['stok' => implode(',', $stokCur)])->update();

        $this->stokModel->where(['id' => $id])->set([
            'id' => time(),
            'email_admin' => session()->get('email'),
            'stok_akhir' => $currentStok,
        ])->update();
        return redirect()->to(base64_decode($url));
    }
    public function benerinStokLuna()
    {
        $allProduk = $this->barangModel->findAll();
        // $produknya = [];
        foreach ($allProduk as $p) {
            $varian = json_decode($p['varian'], true);
            $stokBaru = '';
            foreach ($varian as $ind_v => $v) {
                $stokTerakhir = $this->stokModel->orderBy('tanggal', 'desc')->where([
                    'id_barang' => $p['id'],
                    'varian' => $v
                ])->first();
                $stokBaru .=  ($ind_v == 0 ? '' : ',') . ($stokTerakhir ? (string)$stokTerakhir['stok_akhir'] : '0');
            }
            // array_push($produknya, [
            //     'nama' => $p['nama'],
            //     'varian' => $varian,
            //     'stokbaru' => $stokBaru
            // ]);
            $this->barangModel->where(['id' => $p['id']])->set(['stok' => $stokBaru])->update();
        }
        return $this->response->setJSON([
            'success' => true
        ], false);
    }
    public function cobaWs()
    {
        $ws_url = env('WS_URL', 'DefaultValue');
        $client = new Client($ws_url);
        $client->send(json_encode([
            'jenis' => 'order'
        ]));
        $client->close();
        return $this->response->setJSON([
            'success' => true
        ], false);
    }

    public function notFound()
    {
        return redirect()->to('/');
    }
}
