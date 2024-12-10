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
use App\Models\PreorderBarangModel;

class Pages extends BaseController
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
    protected $preorderBarangModel;
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
        $this->preorderBarangModel = new PreorderBarangModel();
    }
    public function index()
    {
        $produk = $this->barangModel->getBarangLimit();
        $produkBaru = $this->barangModel->getBarangPopuler();
        $data = [
            'title' => 'Beranda',
            'produk' => $produk,
            'produkBaru' => $produkBaru,
            'metaKeyword' => 'lunarea furniture,toko furniture,
            lemari dewasa lunarea semarang,lemari anak lunarea semarang,meja rias lunarea semarang,meja belajar lunarea semarang,meja tv lunarea semarang,meja tulis lunarea semarang,meja komputer lunarea semarang,rak sepatu lunarea semarang,rak besi lunarea semarang,rak serbaguna lunarea semarang,kursi lunarea semarang',
            'msg_active' => session()->getFlashdata('msg_active') ? session()->getFlashdata('msg_active') : false,
        ];
        return view('pages/home', $data);
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
        $data = [
            'title' => 'Syarat dan Ketentuan',
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

        $email = \Config\Services::email();
        $email->setFrom('no-reply@lunareafurniture.com', 'Lunarea Furniture');
        $email->setTo('info@lunareafurniture.com');
        $email->setSubject('Lunarea Store - Formulir');
        $isiEmail = "<div>
            <h1>Pengisian Formulir</h1
            <p>Pesan :</p>
            <p>" . $pesan . "</p>
        </div>";
        $email->setMessage($isiEmail);
        $email->send();

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
    public function all($subkategori = false)
    {
        $produk = $this->barangModel->where(['active' => '1', 'subkategori' => $subkategori])->orderBy('nama', 'asc')->findAll(20, 0);
        $semuaproduk = $this->barangModel->where(['active' => '1', 'subkategori' => $subkategori])->orderBy('nama', 'asc')->findAll();
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
    public function kirimOTP()
    {
        $emailUser = session()->get('email');
        $otp_number = rand(100000, 999999);
        $waktu_otp = time() + 300;
        $d = strtotime("+425 Minutes");
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        $waktu_otp_tanggal = date("d", $d) . " " . $bulan[date("m", $d) - 1] . " " . date("Y H:i:s", $d);

        $email = \Config\Services::email();
        $email->setFrom('no-reply@lunareafurniture.com', 'Lunarea Furniture');
        $email->setTo(session()->get('email'));
        $email->setSubject('Lunarea Store - Verifikasi OTP');
        $email->setMessage("<p>Berikut kode OTP verifikasi</p><h1>" . $otp_number . "</h1><p>Kode ini berlaku hingga " . $waktu_otp_tanggal . "</p>");
        $email->send();

        $this->userModel->where('email', $emailUser)->set([
            'otp' => $otp_number,
            'waktu_otp' => $waktu_otp
        ])->update();

        session()->setFlashdata('msg', "OTP telah dikirim ke email " . $emailUser . " dan berlaku hingga " . $waktu_otp_tanggal);
        return redirect()->to('/verify');
    }
    public function actionSignup()
    {
        session()->setFlashdata('msg', "Maaf, masih dalam masa perbaikan. Akan aktif kembali ketika pukul 07:30 WIB");
        return redirect()->to('/signup');
    }
    public function actionSignupBenar()
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
        $waktu_otp = time() + 300;
        $d = strtotime("+425 Minutes");
        $bulan = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
        $waktu_otp_tanggal = date("d", $d) . " " . $bulan[date("m", $d) - 1] . " " . date("Y H:i:s", $d);

        $email = \Config\Services::email();
        $email->setFrom('no-reply@lunareafurniture.com', 'Lunarea Furniture');
        $email->setTo($this->request->getVar('email'));
        $email->setSubject('Lunarea Store - Verifikasi OTP');
        $email->setMessage("<p>Berikut kode OTP verifikasi</p><h1>" . $otp_number . "</h1><p>Kode ini berlaku hingga " . $waktu_otp_tanggal . "</p>");
        $email->send();

        $this->userModel->insert([
            'email' => $this->request->getVar('email'),
            'sandi' => password_hash($this->request->getVar('sandi'), PASSWORD_DEFAULT),
            'role' => '0',
            'otp' => $otp_number,
            'active' => '0',
            'waktu_otp' => $waktu_otp
        ]);
        $this->pembeliModel->insert([
            'nama' => $this->request->getVar('nama'),
            'email_user' => $this->request->getVar('email'),
            'nohp' => $this->request->getVar('nohp'),
            'alamat' => json_encode([]),
            'wishlist' => json_encode([]),
            'keranjang' => json_encode([]),
            'transaksi' => json_encode([]),
        ]);

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
        $data = [
            'title' => 'Verifikasi',
            'val' => [
                'msg' => session()->getFlashdata('msg'),
                'val_verify' => session()->getFlashdata('val_verify')
            ]
        ];
        return view('pages/verify', $data);
    }
    public function actionVerify()
    {
        $otp = $this->request->getVar("otp");
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
            'role' => $getUser['role'],
            'nama' => $getPembeli['nama'],
            'alamat' => json_decode($getPembeli['alamat'], true),
            'nohp' => $getPembeli['nohp'],
            'wishlist' => json_decode($getPembeli['wishlist'], true),
            'keranjang' => json_decode($getPembeli['keranjang'], true),
            'transaksi' => json_decode($getPembeli['transaksi'], true)
        ];
        $this->userModel->where('email', $email)->set([
            'active' => '1',
            'otp' => '0',
            'waktu_otp' => '0'
        ])->update();
        session()->set($ses_data);
        session()->setFlashdata('msg_active', true);
        return redirect()->to(site_url('/'));
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
            $ses_data = [
                'active' => '1',
                'email' => $getUser['email'],
                'role' => $getUser['role'],
                'nama' => $getPembeli['nama'],
                'alamat' => json_decode($getPembeli['alamat'], true),
                'nohp' => $getPembeli['nohp'],
                'wishlist' => json_decode($getPembeli['wishlist'], true),
                'keranjang' => json_decode($getPembeli['keranjang'], true),
                'transaksi' => json_decode($getPembeli['transaksi'], true),
                'isLogin' => true
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
        if ($getUser['role'] == '0') {
            $getPembeli = $this->pembeliModel->getPembeli($email);
            $ses_data = [
                'active' => '1',
                'email' => $getUser['email'],
                'role' => $getUser['role'],
                'nama' => $getPembeli['nama'],
                'alamat' => json_decode($getPembeli['alamat'], true),
                'nohp' => $getPembeli['nohp'],
                'wishlist' => json_decode($getPembeli['wishlist'], true),
                'keranjang' => json_decode($getPembeli['keranjang'], true),
                'transaksi' => json_decode($getPembeli['transaksi'], true),
                'isLogin' => true
            ];
            session()->set($ses_data);

            $cekSubmitEmail = $this->submitEmailModel->getEmail($email);
            if ($cekSubmitEmail) session()->set('submitEmail', true);
            return redirect()->to(site_url('/'));
        } else {
            $ses_data = [
                'active' => '1',
                'email' => $getUser['email'],
                'role' => $getUser['role'],
                'isLogin' => true
            ];
            session()->set($ses_data);
            return redirect()->to('/');
        }
    }
    public function actionLoginTamuSalah($id_barang = false, $varian = false, $index_gambar = false)
    {
        session()->setFlashdata('msg', "Maaf, masih dalam masa perbaikan. Akan aktif kembali ketika pukul 07:30 WIB");
        return redirect()->to('/login');
    }
    public function actionLoginTamu($id_barang = false, $varian = false, $index_gambar = false)
    {
        if ($id_barang) {
            $ses_data = [
                'active' => '1',
                'email' => 'tamu',
                'role' => 0,
                'nama' => 'tamu',
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
                'isLogin' => true
            ];
            session()->set($ses_data);
            return redirect()->to('/cart');
        } else {
            $ses_data = [
                'active' => '1',
                'email' => 'tamu',
                'role' => 0,
                'nama' => 'tamu',
                'alamat' => [],
                'nohp' => 'tamu',
                'wishlist' => [],
                'keranjang' => [],
                'transaksi' => [],
                'isLogin' => true
            ];
            session()->set($ses_data);
            return redirect()->to('/');
        }
    }
    public function actionLogout()
    {
        $ses_data = ['email', 'role', 'alamat', 'wishlist', 'keranjang', 'isLogin', 'active', 'transaksi', 'nama', 'nohp', 'submitEmail', 'voucher'];
        session()->remove($ses_data);
        session()->setFlashdata('msg', 'Kamu telah keluar');
        return redirect()->to('/login');
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

        $data = [
            'title' => 'Keranjang',
            'produk' => $produk,
            'gambar' => $gambar,
            'jumlah' => $jumlah,
            'keranjang' => $keranjang,
            'tokenMid' => false,
            'berat' => $berat,
            'msg' => session()->getFlashdata('msg'),
            'indStokHabis' => $indElementStokHabis
        ];

        // if (!isset($total)) {
        //     return view('pages/cart', $data);
        // }

        return view('pages/cart', $data);
    }
    public function addCart($id_barang, $varian, $index_gambar)
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
        return redirect()->to('/cart');
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
        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => "https://pro.rajaongkir.com/api/province",
        //     CURLOPT_SSL_VERIFYHOST => 0,
        //     CURLOPT_SSL_VERIFYPEER => 0,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 30,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "GET",
        //     CURLOPT_HTTPHEADER => array(
        //         "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
        //     ),
        // ));
        // $response = curl_exec($curl);
        // $err = curl_error($curl);
        // curl_close($curl);
        // if ($err) {
        //     return "cURL Error #:" . $err;
        // }
        // $provinsi = json_decode($response, true);

        // if (count($alamat) > 0) {
        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => "https://pro.rajaongkir.com/api/city?province=" . $alamat['prov_id'],
        //         CURLOPT_SSL_VERIFYHOST => 0,
        //         CURLOPT_SSL_VERIFYPEER => 0,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "GET",
        //         CURLOPT_HTTPHEADER => array(
        //             "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
        //         ),
        //     ));
        //     $response = curl_exec($curl);
        //     $err = curl_error($curl);
        //     curl_close($curl);
        //     if ($err) {
        //         return "cURL Error #:" . $err;
        //     }
        //     $kota = json_decode($response, true);

        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => "https://pro.rajaongkir.com/api/subdistrict?city=" . $alamat['kab_id'],
        //         CURLOPT_SSL_VERIFYHOST => 0,
        //         CURLOPT_SSL_VERIFYPEER => 0,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "GET",
        //         CURLOPT_HTTPHEADER => array(
        //             "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
        //         ),
        //     ));
        //     $response = curl_exec($curl);
        //     $err = curl_error($curl);
        //     curl_close($curl);
        //     if ($err) {
        //         return "cURL Error #:" . $err;
        //     }
        //     $kec = json_decode($response, true);

        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => "https://dakotacargo.co.id/api/api_glb_M_kodepos.asp?key=15f6a51696a8b034f9ce366a6dc22138&id=11022019000001&aKec=" . rawurlencode($alamat['kec']),
        //         CURLOPT_SSL_VERIFYHOST => 0,
        //         CURLOPT_SSL_VERIFYPEER => 0,
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "GET",
        //     ));
        //     $response = curl_exec($curl);
        //     $err = curl_error($curl);
        //     curl_close($curl);
        //     if ($err) {
        //         return "cURL Error #:" . $err;
        //     }
        //     $desa = json_decode($response, true);
        // }




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
        $voucher = [];
        $emailUjiCoba = ['galihsuks123@gmail.com', 'lunareafurniture@gmail.com', 'galih8.4.2001@gmail.com'];
        if ($email != 'tamu' && in_array($email, $emailUjiCoba)) {
            //voucher member baru
            $voucherMemberBaru = $this->voucherModel->getVoucher(1);
            if ($voucherMemberBaru) {
                if (!in_array($email, json_decode($voucherMemberBaru['list_email'], true))) {
                    array_push($voucher, $voucherMemberBaru);
                }
            }
        }
        $diskonVoucher = 0;
        $voucherSelected = false;
        if (session()->get('voucher')) {
            $voucherDetail = $this->voucherModel->getVoucher(session()->get('voucher'));
            if (!$voucherDetail) {
                session()->remove('voucher');
                return redirect()->to('/checkout');
            }
            if ($voucherDetail['satuan'] == 'persen') {
                $diskonVoucher = round($voucherDetail['nominal'] / 100 * ($total - 5000));
            }
            $voucherSelected = $voucherDetail;
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
            // 'provinsi' => $provinsi["rajaongkir"]["results"],
            // 'kabupaten' => isset($kota) ? $kota["rajaongkir"]["results"] : [],
            // 'kecamatan' => isset($kec) ? $kec["rajaongkir"]["results"] : [],
            // 'desa' => isset($desa) ? $desa : [],
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
            'emailUji' => $emailUjiCoba,
            // 'paket' => $paketFilter,
            // 'paketJson' => json_encode($paketFilter),
            'potonganPreorder' => $potonganPreorder
        ];
        // return view('pages/' . (in_array($email, $emailUjiCoba) ? 'checkoutcore' : 'checkout'), $data);
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
        $biayaadmin = array(
            'id' => 'Biaya Admin',
            'price' => 5000,
            'quantity' => 1,
            'name' => 'Biaya Admin',
        );
        array_push($itemDetails, $biayaadmin);

        //voucher
        // $voucher = [];
        $emailUjiCoba = ['galihsuks123@gmail.com', 'lunareafurniture@gmail.com', 'galih8.4.2001@gmail.com'];
        // if ($email != 'tamu' && in_array($email, $emailUjiCoba)) {
        //     //voucher member baru
        //     $voucherMemberBaru = $this->voucherModel->getVoucher(1);
        //     if (!in_array($email, json_decode($voucherMemberBaru['list_email'], true))) {
        //         array_push($voucher, $voucherMemberBaru);
        //     }
        // }
        $diskonVoucher = 0;
        $voucherSelected = false;
        if (session()->get('voucher')) {
            $voucherDetail = $this->voucherModel->getVoucher(session()->get('voucher'));
            if ($voucherDetail['satuan'] == 'persen') {
                $diskonVoucher = round($voucherDetail['nominal'] / 100 * ($total - 5000));
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
            $diskonVoucher = array(
                'id' => 'Diskon Voucher',
                'price' => -$data['diskonVoucher'],
                'quantity' => 1,
                'name' => 'Diskon Voucher',
            );
            array_push($itemDetails, $diskonVoucher);
        }

        if ($potonganPreorder > 0) {
            array_push($itemDetails, [
                'id' => 'Potongan Preorder',
                'price' => -$potonganPreorder,
                'quantity' => 1,
                'name' => 'Potongan Preorder',
            ]);
        }

        if (in_array($email, $emailUjiCoba))
            $auth = base64_encode("SB-Mid-server-3M67g25LgovNPlwdS4WfiMsh" . ":");
        else
            $auth = base64_encode("" . ":");
        $pesananke = $this->pemesananModel->orderBy('id', 'desc')->first();
        $idFix = "L" . (sprintf("%08d", $pesananke ? ((int)$pesananke['id'] + 1) : 1));
        $randomId = "L" . rand();
        $customField = json_encode([
            'e' => $email,
            'n' => $nama,
            'h' => $nohp,
            'a' => $alamatLengkap,
            'i' => $produk,
            'nt' => $note,
            'v' => [
                'd' => $data['diskonVoucher'], //ini udah bentuk rupiah
                'id' => $voucher ? $voucher['id'] : false,
            ]
        ]);
        $arrPostField = [
            "transaction_details" => [
                "order_id" => in_array($email, $emailUjiCoba) ? $randomId : $idFix,
                "gross_amount" => $total - $data['diskonVoucher'] - $potonganPreorder,
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
            default:
                session()->setFlashdata('msg', 'Tipe pembayaran tidak ditemukan');
                return redirect()->to('/checkout');
                break;
        }

        // if ($email == 'galih8.4.2001@gmail.com') {
        //     return $this->response->setJSON([
        //         'arrpost' => $arrPostField,
        //     ], false);
        // }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => in_array($email, $emailUjiCoba) ? "https://api.sandbox.midtrans.com/v2/charge" : "https://api.midtrans.com/v2/charge",
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

        // if ($email == 'galih8.4.2001@gmail.com') {
        //     return $this->response->setJSON($hasilMidtrans, false);
        // }
        if (substr($hasilMidtrans['status_code'], 0, 1) != '2') {
            session()->setFlashdata('msg', $hasilMidtrans['status_message']);
            return redirect()->to('/checkout');
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
            default:
                $status = "No Status";
                break;
        }
        $this->pemesananModel->insert([
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
            'idVoucher' => $voucher ? $voucher['id'] : 0
        ]);

        if ($data['diskonVoucher'] > 0) {
            $voucherSelected = $this->voucherModel->getVoucher($voucher['id']);
            $voucherSelected_email = json_decode($voucherSelected['list_email'], true);
            array_push($voucherSelected_email, $email);
            $this->voucherModel->where(['id' => $voucher['id'], 'active' => '1'])->set(['list_email' => json_encode($voucherSelected_email)])->update();
            session()->remove('voucher');
        }

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
        return redirect()->to('/order/' . $arrPostField['transaction_details']['order_id']);
    }
    public function actionPaySnap()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $email = $body['email'];
        $nama = $body['nama'];
        $nohp = $body['nohp'];
        $prov = $body['provinsi'];
        $kab = $body['kota'];
        $kec = $body['kecamatan'];
        $kode = $body['kodepos'];
        $alamatAdd = $body['alamat_add'];
        $alamatLengkap = $body['alamat'];
        $note = $body['note'];
        $data = json_decode(base64_decode($body['data']), true);
        $keranjang = $data['keranjang'];
        $voucher = $data['voucherSelected'];

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
            }
            $total = $subtotal + 5000;
        }

        $biayaadmin = array(
            'id' => 'Biaya Admin',
            'price' => 5000,
            'quantity' => 1,
            'name' => 'Biaya Admin',
        );
        array_push($itemDetails, $biayaadmin);

        if ($data['diskonVoucher'] > 0) {
            $diskonVoucher = array(
                'id' => 'Diskon Voucher',
                'price' => -$data['diskonVoucher'],
                'quantity' => 1,
                'name' => 'Diskon Voucher',
            );
            array_push($itemDetails, $diskonVoucher);
        }

        // \Midtrans\Config::$serverKey = "SB-Mid-server-3M67g25LgovNPlwdS4WfiMsh";
        // \Midtrans\Config::$isProduction = false;
        $auth = base64_encode("SB-Mid-server-3M67g25LgovNPlwdS4WfiMsh" . ":");
        $pesananke = $this->pemesananModel->orderBy('id', 'desc')->first();
        $idFix = "L" . (sprintf("%08d", $pesananke ? ((int)$pesananke['id'] + 1) : 1));
        $randomId = "L" . rand();
        $customField = json_encode([
            'e' => $email,
            'n' => $nama,
            'h' => $nohp,
            'a' => $alamatLengkap,
            'i' => $produk,
            'nt' => $note,
            'v' => [
                'd' => $data['diskonVoucher'], //ini udah bentuk rupiah
                'id' => $voucher ? $voucher['id'] : false,
            ]
        ]);
        $emailUjiCoba = ['galihsuks123@gmail.com', 'lunareafurniture@gmail.com', 'galih8.4.2001@gmail.com'];
        $arrPostField = [
            "transaction_details" => [
                "order_id" => in_array($email, $emailUjiCoba) ? $randomId : $idFix,
                "gross_amount" => $total - $data['diskonVoucher'],
            ],
            'customer_details' => array(
                'email' => $email,
                'first_name' => $nama,
                'phone' => $nohp,
                'billing_address' => array(
                    'email' => $email,
                    'first_name' => $nama,
                    'phone' => $nohp,
                    'address' => $alamatLengkap,
                ),
                'shipping_address' => array(
                    'email' => $email,
                    'first_name' => $nama,
                    'phone' => $nohp,
                    'address' => $alamatLengkap,
                )
            ),
            'callbacks' => array(
                'finish' => "https://lunareafurniture.com/order/" . (in_array($email, $emailUjiCoba) ? $randomId : $idFix),
            ),
            'item_details' => $itemDetails,
            "custom_field1" => substr($customField, 0, 255),
            "custom_field2" => substr($customField, 255, 255),
            "custom_field3" => substr($customField, 510, 255),
        ];

        // $snapToken = \Midtrans\Snap::getSnapToken($arrPostField);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://app.midtrans.com/snap/v1/transactions",
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
        return $this->response->setJSON($hasilMidtrans, false);
    }
    public function actionPay()
    {
        $emailPem = $this->request->getVar('emailPem') ? $this->request->getVar('emailPem') : session()->get('email');
        $namaPem = $this->request->getVar('namaPem') ? $this->request->getVar('namaPem') : session()->get('nama');
        $nohpPem = $this->request->getVar('nohpPem') ? $this->request->getVar('nohpPem') : session()->get('nohp');
        $nama = $this->request->getVar('nama');
        $nohp = $this->request->getVar('nohp');
        $prov = $this->request->getVar('provinsi');
        $kab = $this->request->getVar('kota');
        $kec = $this->request->getVar('kecamatan');
        $kode = $this->request->getVar('kodepos');
        $alamatAdd = $this->request->getVar('alamat_add');
        $alamatLengkap = $this->request->getVar('alamat');
        $pembayaran = $this->request->getVar('pembayaran');

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
        if (session()->get('email') != 'tamu') {
            $this->pembeliModel->where('email_user', $emailPem)->set([
                'alamat' => json_encode($alamat),
            ])->update();
        }
        session()->set(['alamat' => $alamat]);
        $keranjang = session()->get('keranjang');
        $produk = [];

        $subtotal = 0;
        $itemDetails = [];
        if (!empty($keranjang)) {
            foreach ($keranjang as $ind => $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = round($persen * $produknya['harga']);
                $subtotal += $hasil * (int)$element['jumlah'];
                $dimensi = explode("X", $produknya['dimensi']);
                array_push($produk, array(
                    'name' => $produknya['nama'] . " (" . $element['varian'] . ")",
                    'value' => $hasil,
                    'length' => (float)$dimensi[0],
                    'width' => (float)$dimensi[1],
                    'height' => (float)$dimensi[2],
                    'weight' => (float)$produknya['berat'],
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
            }
            $total = $subtotal + 5000;
        }

        $biayaadmin = array(
            'id' => 'Biaya Admin',
            'price' => 5000,
            'quantity' => 1,
            'name' => 'Biaya Admin',
        );
        array_push($itemDetails, $biayaadmin);

        $auth = base64_encode("SB-Mid-server-3M67g25LgovNPlwdS4WfiMsh" . ":");
        $pesananke = $this->pemesananModel->orderBy('id', 'desc')->first();
        $idFix = "JM" . (sprintf("%08d", $pesananke ? ((int)$pesananke['id'] + 1) : 1));
        $randomId = "JM" . rand();
        $customField = json_encode([
            'e' => $emailPem,
            'n' => $nama,
            'h' => $nohp,
            'a' => $alamatLengkap,
            'i' => $produk
        ]);
        $arrPostField = [
            "transaction_details" => [
                "order_id" => $idFix,
                "gross_amount" => $total,
                "payment_link_id" => "payment-link-lunarea-" . $idFix
            ],
            // 'customer_details' => array(
            //     'email' => $emailPem,
            //     'first_name' => $namaPem,
            //     'phone' => $nohpPem,
            //     'billing_address' => array(
            //         'email' => $emailPem,
            //         'first_name' => $namaPem,
            //         'phone' => $nohpPem,
            //         'address' => $alamatLengkap,
            //     ),
            //     'shipping_address' => array(
            //         'email' => $emailPem,
            //         'first_name' => $nama,
            //         'phone' => $nohp,
            //         'address' => $alamatLengkap,
            //     )
            // ),
            'customer_details' => array(
                'email' => $emailPem,
                'phone' => $nohp,
                'first_name' => $nama,
            ),
            'item_details' => $itemDetails,
            "usage_limit" =>  1,
            "enabled_payments" => ["gopay", "cimb_clicks", "bca_klikbca", "bca_klikpay", 'bri_epay', 'echannel', 'permata_va', 'bca_va', 'bni_va', 'bri_va', 'shopeepay'],
            "expiry" => [
                "duration" => 24,
                "unit" => "hours"
            ],
            "custom_field1" => substr($customField, 0, 255),
            "custom_field2" => substr($customField, 255, 255),
            "custom_field3" => substr($customField, 510, 255),
        ];

        // switch ($pembayaran) {
        //     case 'bca':
        //         $arrPostField["payment_type"] = "bank_transfer";
        //         $arrPostField["bank_transfer"] = ["bank" => "bca"];
        //         break;
        //     case 'bri':
        //         $arrPostField["payment_type"] = "bank_transfer";
        //         $arrPostField["bank_transfer"] = ["bank" => "bri"];
        //         break;
        //     case 'bni':
        //         $arrPostField["payment_type"] = "bank_transfer";
        //         $arrPostField["bank_transfer"] = ["bank" => "bni"];
        //         break;
        //     case 'cimb':
        //         $arrPostField["payment_type"] = "bank_transfer";
        //         $arrPostField["bank_transfer"] = ["bank" => "cimb"];
        //         break;
        //     case 'permata':
        //         $arrPostField["payment_type"] = "permata";
        //         break;
        //     case 'mandiri':
        //         $arrPostField["payment_type"] = "echannel";
        //         $arrPostField["echannel"] = [
        //             "bill_info1" => "Payment:",
        //             "bill_info2" => "Online purchase"
        //         ];
        //         break;
        //     default:
        //         return redirect()->to('/cart');
        //         break;
        // }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.midtrans.com/v1/payment-links",
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
        // dd($hasilMidtrans);

        // if ($hasilMidtrans['fraud_status'] == "accept") {
        //     switch ($hasilMidtrans['transaction_status']) {
        //         case 'settlement':
        //             $status = "Proses";
        //             break;
        //         case 'capture':
        //             $status = "Proses";
        //             break;
        //         case 'pending':
        //             $status = "Menunggu Pembayaran";
        //             break;
        //         case 'expire':
        //             $status = "Kadaluarsa";
        //             break;
        //         case 'deny':
        //             $status = "Ditolak";
        //             break;
        //         case 'failure':
        //             $status = "Gagal";
        //             break;
        //         case 'refund':
        //             $status = "Refund";
        //             break;
        //         case 'partial_refund':
        //             $status = "Partial Refund";
        //             break;
        //         case 'cancel':
        //             $status = "Dibatalkan";
        //             break;
        //         default:
        //             $status = "No Status";
        //             break;
        //     }
        // } else {
        //     $status = 'Forbidden';
        // }
        // $this->pemesananModel->set([
        //     'nama_cus' => $namaPem,
        //     'email_cus' => $emailPem,
        //     'hp_cus' => $nohpPem,
        //     'nama_pen' => $nama,
        //     'hp_pen' => $nohp,
        //     'alamat_pen' => json_encode($alamat),
        //     'resi' => "Menunggu pengiriman",
        //     'items' => json_encode($produk),
        //     'status' => $status,
        //     'kurir' => 'kosong'
        // ])->update();

        session()->setFlashdata('id_pesanan', $hasilMidtrans['order_id']);
        return redirect()->to($hasilMidtrans['payment_url']);
    }
    public function actionCheckout()
    {
        $namaPen = $this->request->getVar('namaPen');
        $nohpPen = $this->request->getVar('nohpPen');
        $nama = $this->request->getVar('nama');
        $alamat = $this->request->getVar('alamat');
        $nohp = $this->request->getVar('nohp');
        $email = $this->request->getVar('email');
        $paketData = $this->request->getVar('paket');
        $items = $this->request->getVar('produk');
        $keranjangJson = $this->request->getVar('keranjang');
        $paket = (int)explode("@", base64_decode($paketData))[0];
        $kurir = explode("@", base64_decode($paketData))[1];

        // $getPembeli = $this->pembeliModel->getPembeli($email);
        // $keranjang = json_decode($getPembeli['keranjang'], true);
        $keranjang = json_decode($keranjangJson, true);
        // $produk = [];
        $jumlah = [];
        $subtotal = 0;
        $total = 0;
        $itemDetails = [];
        if (!empty($keranjang)) {
            foreach ($keranjang as $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                // array_push($produk, $produknya);
                array_push($jumlah, $element['jumlah']);
                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = round($persen * $produknya['harga']);
                $subtotal += $hasil * $element['jumlah'];
                $item = array(
                    'id' => $produknya["id"],
                    'price' => $hasil,
                    'quantity' => $element['jumlah'],
                    'name' => $produknya["nama"] . " (" . $element['varian'] . ")",
                );
                array_push($itemDetails, $item);
            }
            // $item = array(
            //     'id' => 'Biaya Tambahan',
            //     'price' => $paket,
            //     'quantity' => 1,
            //     'name' => 'Biaya Ongkir',
            // );
            $biayaadmin = array(
                'id' => 'Biaya Admin',
                'price' => 5000,
                'quantity' => 1,
                'name' => 'Biaya Admin',
            );
            // array_push($itemDetails, $item);
            array_push($itemDetails, $biayaadmin);
            $total = $subtotal + $paket + 5000;
        }

        \Midtrans\Config::$serverKey = "";
        \Midtrans\Config::$isProduction = false;
        $pesananke = $this->pemesananModel->orderBy('id', 'desc')->first();
        $idFix = "JM" . (sprintf("%08d", $pesananke ? ((int)$pesananke['id'] + 1) : 1));
        $randomId = rand();
        $stringData = $email . "&" . $nama . "&" . $nohp . "&" . $namaPen . "&" . $nohpPen . "&" . $alamat . "&" . $idFix . "&" . str_replace("&", "@", $kurir) . "&" . $items;
        $params = array(
            'transaction_details' => array(
                'order_id' => $idFix,
                //'order_id' => $randomId,
                'gross_amount' => $total,
            ),
            'callbacks' => array(
                'finish' => "https://lunareafurniture.com/finish_url/JSM-zWYWObdPEKlHA0PWP6BN/" . $stringData,
            ),
            'customer_details' => array(
                'email' => $email,
                'first_name' => $nama,
                'phone' => $nohp,
                'billing_address' => array(
                    'email' => $email,
                    'first_name' => $nama,
                    'phone' => $nohp,
                    'address' => json_decode($alamat, true)['alamat'],
                ),
                'shipping_address' => array(
                    'email' => $email,
                    'first_name' => $namaPen,
                    'phone' => $nohpPen,
                    'address' => json_decode($alamat, true)['alamat'],
                )
            ),
            'item_details' => $itemDetails
        );
        $snapToken = \Midtrans\Snap::getSnapToken($params);
        $arr = array(
            'snapToken' => $snapToken,
            'email' => $email,
            'alamat' => $alamat,
            'nama' => $nama,
            'phone' => $nohp,
        );
        return $this->response->setJSON($arr, false);
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

                //reset jumlah produk
                if ($status == 'Kadaluarsa' || $status == 'Ditolak' || $status == 'Gagal' || $status == "Dibatalkan") {
                    $dataTransaksiFulDariDatabase = $this->pemesananModel->where('id_midtrans', $order_id)->first();
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

                    //cek kalo dia pake voucher maka dihapus emailnya dri list email yg di table vouhcer
                    if ($dataTransaksiFulDariDatabase['diskonVoucher'] > 0) {
                        $listEmailBaru = json_decode($this->voucherModel->where(['id' => $dataTransaksiFulDariDatabase['idVoucher']])->first()['list_email'], true);
                        if (($key = array_search($dataTransaksiFulDariDatabase['email_cus'], $listEmailBaru)) !== false) {
                            unset($listEmailBaru[$key]);
                        }
                        $this->voucherModel->where(['id' => $dataTransaksiFulDariDatabase['idVoucher']])->set(['list_email' => json_encode($listEmailBaru)])->update();
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
                            $va_number = 'https://api.midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
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
                        'waktuExpire' => date("d", $waktuExpire) . " " . $bulan[(int)date("m", $waktuExpire) - 1] . " " . date("Y H:i:s", $waktuExpire)
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
                        case 'echannel':
                            $va_number = $dataMid['bill_key'];
                            $biller_code = $dataMid['biller_code'];
                            $bank = "mandiri";
                            break;
                        case 'qris':
                            $va_number = 'https://api.midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
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
                        case 'echannel':
                            $va_number = $dataMid['bill_key'];
                            $biller_code = $dataMid['biller_code'];
                            $bank = "mandiri";
                            break;
                        case 'qris':
                            $va_number = 'https://api.midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
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
                            $va_number = 'https://api.midtrans.com/v2/qris/' . $dataMid['transaction_id'] . '/qr-code';
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
    public function account()
    {
        $nama = session()->get("nama");
        $nohp = session()->get("nohp");
        $data = [
            'title' => 'Akun Saya',
            'nama' => $nama,
            'nohp' => $nohp,
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

        if ($sandi != '') {
            $this->userModel->where('email', $email)->set([
                'sandi' => password_hash($sandi, PASSWORD_DEFAULT),
            ])->update();
        }
        if ($role == '0') {
            $this->pembeliModel->where('email_user', $email)->set([
                'nama' => $nama,
                'nohp' => $nohp,
            ])->update();

            session()->set([
                'nama' => $nama,
                'nohp' => $nohp,
            ]);
        }

        session()->set('msg', 'Akun Anda telah diperbarui');
        return redirect()->to('/account');
        // $data = [
        //     'title' => 'Akun Saya',
        //     'nama' => $nama,
        //     'nohp' => $nohp
        // ];
        // return view('pages/account', $data);
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
            ];
            if ($status == 'Proses') {
                if ($transaksi['status'] == 'Proses') array_push($transaksiCusNoJSON, $arr);
            } else if ($status == 'Menunggu-Pembayaran') {
                if ($transaksi['status'] == 'Menunggu Pembayaran') array_push($transaksiCusNoJSON, $arr);
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
        $data = [
            'title' => 'List Customer',
            'transaksiCus' => $transaksiCusNoJSON,
            'semuaTransaksiCus' => $semuaTransaksiCusFilter,
            'transaksiJson' => $transaksiJson,
            'page' => $page,
            'status' => $status
        ];
        return view('pages/listCustomer', $data);
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
        $email = \Config\Services::email();
        $email->setFrom('no-reply@lunareafurniture.com', 'Lunarea Furniture');
        $email->setTo($body['data']['email_cus']);
        $email->setSubject('Lunarea Store - Pesananmu sudah dikirim');
        $email->setMessage("<p>Berikut nomor resi pada pesanan " . $body['data']['id_midtrans'] . "</p>
        <h1>" . $body['resi'] . '</h1>
        <p style="margin-bottom: 10px">' . $body['data']['kurir'] . '</p>
        <span style="margin-bottom: 10px>-------------------------------------------------</span>       
        <p style="margin-bottom: 10px"><b>Informasi terkait pesanan</b></p>
        <p>Nama : ' . $body['data']['nama_cus'] . '</p>
        <p>Email : ' . $body['data']['email_cus'] . '</p>
        <p style="margin-bottom: 10px">Kode Pesanan : ' . $body['data']['id_midtrans'] . '</p>
        <p>Item Pesanan :</p>' . $list_item);
        $email->send();

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
    public function listVoucher()
    {
        $voucher = $this->voucherModel->findAll();
        $data = [
            'title' => 'List Voucher',
            'voucher' => $voucher
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
            'jenis' => ['rules' => 'required'],
        ])) {
            session()->setFlashdata('msg', 'Ada data yang belum diisi');
            return redirect()->to('/addvoucher')->withInput();
        }

        $nama = $this->request->getVar('nama');
        $nominal = $this->request->getVar('nominal');
        $satuan = $this->request->getVar('satuan');
        $berakhir = $this->request->getVar('berakhir') ? $this->request->getVar('berakhir') : '0000-00-00';
        $jenis = $this->request->getVar('jenis');

        $this->voucherModel->insert([
            'nama' => $nama,
            'nominal' => $nominal,
            'satuan' => $satuan,
            'berakhir' => $berakhir,
            'jenis' => $jenis,
            'active' => true,
            'list_email' => json_encode([])
        ]);
        return redirect()->to('/listvoucher');
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

    public function notFound()
    {
        return redirect()->to('/');
    }
}
