<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\GambarBarangModel;
use App\Models\PembeliModel;
use App\Models\PemesananModel;
use App\Models\UserModel;

class Pages extends BaseController
{
    protected $barangModel;
    protected $gambarBarangModel;
    protected $userModel;
    protected $pembeliModel;
    protected $pemesananModel;
    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->gambarBarangModel = new GambarBarangModel();
        $this->userModel = new UserModel();
        $this->pembeliModel = new PembeliModel();
        $this->pemesananModel = new PemesananModel();
    }
    public function index()
    {
        $produk = $this->barangModel->getBarangLimit();
        $produkBaru = $this->barangModel->getBarangBaru();
        $data = [
            'title' => 'Beranda',
            'produk' => $produk,
            'produkBaru' => $produkBaru,
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
    public function all($subkategori = false)
    {
        $produk = $this->barangModel->where('subkategori', $subkategori)->orderBy('nama', 'asc')->findAll(20, 0);
        $semuaproduk = $this->barangModel->where('subkategori', $subkategori)->orderBy('nama', 'asc')->findAll();
        $data = [
            'title' => 'Semua Produk',
            'produk' => $produk,
            'kategori' => $subkategori,
            'page' => 1,
            'nama' => false,
            'semuaProduk' => $semuaproduk
        ];
        return view('pages/all', $data);
    }
    public function allPage($page, $subkategori = false)
    {
        $pagination = (int)$page;
        if ($pagination > 1) {
            $hitungOffset = 20 * ($pagination - 1);
            $produk = $this->barangModel->where('subkategori', $subkategori)->orderBy('nama', 'asc')->findAll(20, $hitungOffset);
        } else {
            $produk = $this->barangModel->where('subkategori', $subkategori)->orderBy('nama', 'asc')->findAll(20, 0);
        }
        $semuaproduk = $this->barangModel->where('subkategori', $subkategori)->orderBy('nama', 'asc')->findAll();
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
        $email->setFrom('no-reply@jasminefurniture.com', 'Jasmine Furniture');
        $email->setTo(session()->get('email'));
        $email->setSubject('Jasmine Store - Verifikasi OTP');
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
        $email->setFrom('no-reply@jasminefurniture.com', 'Jasmine Furniture');
        $email->setTo($this->request->getVar('email'));
        $email->setSubject('Jasmine Store - Verifikasi OTP');
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
    public function actionLogin()
    {
        session()->setFlashdata('msg', "Maaf, masih dalam masa perbaikan. Akan aktif kembali ketika pukul 07:30 WIB");
        return redirect()->to('/login');
    }
    public function actionLoginBener()
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
    public function actionLoginTamu()
    {
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
        return redirect()->to(site_url('/'));
    }
    public function actionLogout()
    {
        $ses_data = ['email', 'role', 'alamat', 'wishlist', 'keranjang', 'isLogin', 'active', 'transaksi', 'nama', 'nohp'];
        session()->remove($ses_data);
        session()->setFlashdata('msg', 'Kamu telah keluar');
        return redirect()->to('/login');
    }
    public function wishlist()
    {
        $wishlist = session()->get('wishlist');
        $produk = [];
        if (count($wishlist) > 0) {
            foreach ($wishlist as $w) {
                array_push($produk, $this->barangModel->getBarang($w));
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
        return redirect()->to('/wishlist');
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
        $itemDetails = [];
        $indElementNotFound = [];
        $indElementStokHabis = [];
        $subtotal = 0;
        $berat = 0;
        if (!empty($keranjang)) {
            foreach ($keranjang as $ind => $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                if ($produknya) {
                    $gambarnya = $this->gambarBarangModel->getGambar($element['id']);
                    array_push($produk, $produknya);
                    array_push($gambar, $gambarnya["gambar" . ($element['index_gambar'] + 1)]);
                    array_push($jumlah, $element['jumlah']);
                    $item = array(
                        'id' => $produknya["id"],
                        'price' => $produknya["harga"],
                        'quantity' => $element['jumlah'],
                        'name' => $produknya["nama"],
                    );
                    array_push($itemDetails, $item);

                    $persen = (100 - $produknya['diskon']) / 100;
                    $hasil = floor($persen * $produknya['harga']);
                    $subtotal += $hasil * $element['jumlah'];
                    $berat += $produknya['berat'] * $element['jumlah'];

                    //cek stok habis
                    if ((int)$produknya['stok'] - (int)$element['jumlah'] < 0)
                        array_push($indElementStokHabis, $ind);
                } else {
                    array_push($indElementNotFound, $ind);
                }
            }
            $item = array(
                'id' => 'Biaya Tambahan',
                'price' => 10000,
                'quantity' => 1,
                'name' => 'Biaya Ongkir',
            );
            array_push($itemDetails, $item);
            $total = $subtotal + 10000;
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

        if (!isset($total)) {
            return view('pages/cart', $data);
        }

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
                if ((int)$produknya['stok'] - (int)$keranjang[$index]['jumlah'] - 1 < 0) {
                    session()->setFlashdata('msg', 'Stok kurang');
                    return redirect()->to("/product/" . $produknya['id']);
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
        if (session()->getFlashdata('id_pesanan') == null) return redirect()->to('/');
        $data = [
            'title' => 'Pembayaran Sukses',
            'data_transaksi' => array(
                'status' => session()->getFlashdata('status_transaksi'),
                'id' => session()->getFlashdata('id_pesanan'),
            ),
        ];
        return view('pages/successPay', $data);
    }
    public function progressPay()
    {
        if (session()->getFlashdata('id_pesanan') == null) return redirect()->to('/');
        $data = [
            'title' => 'Pembayaran Pending',
            'data_transaksi' => array(
                'status' => session()->getFlashdata('status_transaksi'),
                'id' => session()->getFlashdata('id_pesanan'),
            ),
        ];
        return view('pages/progressPay', $data);
    }
    public function errorPay()
    {
        if (session()->getFlashdata('id_pesanan') == null) return redirect()->to('/');
        $data = [
            'title' => 'Pembayaran Gagal',
            'data_transaksi' => array(
                'status' => session()->getFlashdata('status_transaksi'),
                'id' => session()->getFlashdata('id_pesanan'),
            ),
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
        if (!empty($keranjang)) {
            foreach ($keranjang as $ind => $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                array_push($produk, $produknya);
                array_push($jumlah, $element['jumlah']);

                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = round($persen * $produknya['harga']);
                $subtotal += $hasil * $element['jumlah'];
                $dimensi = explode("X", $produknya['dimensi']);
                array_push($dimensiSemua, $produknya['dimensi']);
                $berat += $produknya['berat'] * $element['jumlah'];
                $beratHitung += ceil((float)$dimensi[0] * (float)$dimensi[1] * (float)$dimensi[2] / 6000) * $element['jumlah']; //kg

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
                if ((int)$produknya['stok'] - (int)$element['jumlah'] < 0)
                    return redirect()->to('cart');
            }
            $total = $subtotal + 5000;
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
            $curl_jne = curl_init();
            curl_setopt_array($curl_jne, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "origin=5504&originType=subdistrict&destination=" . $alamat['kec_id'] . "&destinationType=subdistrict&weight=" . $beratAkhir * 1000 . "&courier=jne",
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/x-www-form-urlencoded",
                    "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
                ),
            ));
            $response = curl_exec($curl_jne);
            $err = curl_error($curl_jne);
            curl_close($curl_jne);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $jne = json_decode($response, true);

            $curl_jnt = curl_init();
            curl_setopt_array($curl_jnt, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "origin=5504&originType=subdistrict&destination=" . $alamat['kec_id'] . "&destinationType=subdistrict&weight=" . $beratAkhir * 1000 . "&courier=jnt",
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/x-www-form-urlencoded",
                    "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
                ),
            ));
            $response = curl_exec($curl_jnt);
            $err = curl_error($curl_jnt);
            curl_close($curl_jnt);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $jnt = json_decode($response, true);

            $curl_wahana = curl_init();
            curl_setopt_array($curl_wahana, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "origin=5504&originType=subdistrict&destination=" . $alamat['kec_id'] . "&destinationType=subdistrict&weight=" . $beratAkhir * 1000 . "&courier=wahana",
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/x-www-form-urlencoded",
                    "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
                ),
            ));
            $response = curl_exec($curl_wahana);
            $err = curl_error($curl_wahana);
            curl_close($curl_wahana);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $wahana = json_decode($response, true);

            $curl_dakota = curl_init();
            $data_dakota = [
                'prov' => $alamat['prov'],
                'kab' => $alamat['kab'],
                'kec' => $alamat['kec'],
            ];
            curl_setopt_array($curl_dakota, array(
                CURLOPT_URL => "https://api.jasminefurniture.co.id/dakota",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data_dakota),
                CURLOPT_HTTPHEADER => array(
                    "content-type: application/json"
                ),
            ));
            $response = curl_exec($curl_dakota);
            $err = curl_error($curl_dakota);
            curl_close($curl_dakota);
            if ($err) {
                return "cURL Error #:" . $err;
            }
            $dakota = json_decode($response, true);

            $costs_dakota = [];
            foreach ($dakota['data'] as $deskripsi => $value_dakota) {
                if ($deskripsi != 'UNIT') {
                    array_push($costs_dakota, [
                        'service' => $deskripsi,
                        'description' => ucwords($deskripsi),
                        'cost' => [
                            [
                                'value' => $beratHitung > (int)$value_dakota[0]['minkg'] ? (int)$value_dakota[0]['kgnext'] * $beratHitung : (int)$value_dakota[0]['pokok'],
                                'etd' => $value_dakota[0]['LT']
                            ]
                        ]
                    ]);
                }
            }

            $paket = [
                $jne['rajaongkir']['results'][0],
                $jnt['rajaongkir']['results'][0],
                $wahana['rajaongkir']['results'][0],
                [
                    'code' => 'dakota',
                    'name' => 'Dakota Cargo',
                    'costs' => $costs_dakota
                ]
            ];
        }

        $user = [
            'nama' => $email == 'tamu' ? (session()->getFlashdata('namaPen') ? session()->getFlashdata('namaPen') : '') : $nama,
            'alamat' => $alamat,
            'nohp' => $email == 'tamu' ? (session()->getFlashdata('nohpPen') ? session()->getFlashdata('nohpPen') : '') : $nohp,
            'email' => $email,
        ];
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
            'keranjang' => $keranjang,
            'keranjangJson' => json_encode($keranjang),
            'paket' => $paket,
            'paketJson' => json_encode($paket),
        ];
        return view('pages/checkout', $data);
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
        $stringDataLain = explode("&", $dataLain);
        // dd($arr);
        if ($email != 'tamu')
            $this->pembeliModel->where('email_user', $email)->set([
                'alamat' => json_encode($arr),
            ])->update();

        session()->set(['alamat' => $arr]);
        session()->setFlashdata('emailPem', $stringDataLain[0]);
        session()->setFlashdata('namaPem', $stringDataLain[1]);
        session()->setFlashdata('nohpPem', $stringDataLain[2]);
        session()->setFlashdata('namaPen', $stringDataLain[3]);
        session()->setFlashdata('nohpPen', $stringDataLain[4]);
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
        $produk = [];
        $jumlah = [];
        $subtotal = 0;
        $total = 0;
        $itemDetails = [];
        if (!empty($keranjang)) {
            foreach ($keranjang as $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                array_push($produk, $produknya);
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
            $item = array(
                'id' => 'Biaya Tambahan',
                'price' => $paket,
                'quantity' => 1,
                'name' => 'Biaya Ongkir',
            );
            $biayaadmin = array(
                'id' => 'Biaya Admin',
                'price' => 5000,
                'quantity' => 1,
                'name' => 'Biaya Admin',
            );
            array_push($itemDetails, $item);
            array_push($itemDetails, $biayaadmin);
            $total = $subtotal + $paket + 5000;
        }

        \Midtrans\Config::$serverKey = "Mid-server-uZVVVOFO2sD-nmeN1mfrcgpd";
        \Midtrans\Config::$isProduction = true;
        $pesananke = $this->pemesananModel->orderBy('id', 'desc')->first();
        $idFix = "JSM" . (sprintf("%08d", $pesananke ? ((int)$pesananke['id'] + 1) : 1));
        $randomId = rand();
        $stringData = $email . "&" . $nama . "&" . $nohp . "&" . $namaPen . "&" . $nohpPen . "&" . $alamat . "&" . $idFix . "&" . str_replace("&", "@", $kurir) . "&" . $items;
        $params = array(
            'transaction_details' => array(
                'order_id' => $idFix,
                //'order_id' => $randomId,
                'gross_amount' => $total,
            ),
            'callbacks' => array(
                'finish' => "https://jasminefurniture.store/finish_url/JSM-zWYWObdPEKlHA0PWP6BN/" . $stringData,
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
        dd([
            'code' => $code,
            'status' => $status
        ]);
        if ($code != "JSM-zWYWObdPEKlHA0PWP6BN") {
            return redirect()->to("/");
        }
        session()->setFlashdata('id_pesanan', 'JSM0000000');
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
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $order_id = $body['order_id'];
        $fraud = $body['fraud_status'];
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

        $dataTransaksi_curr = $this->pemesananModel->getPemesanan($order_id);
        if (isset($dataTransaksi_curr)) {
            $dataMid_curr = json_decode($dataTransaksi_curr['data_mid'], true);
            $dataMid_curr['transaction_status'] = $body['transaction_status'];
            $this->pemesananModel->where('id_midtrans', $order_id)->set([
                'status' => $status,
                'data_mid' => json_encode($dataMid_curr),
            ])->update();
        } else {
            $this->pemesananModel->insert([
                'nama_cus' => '',
                'email_cus' => '',
                'hp_cus' => '',
                'nama_pen' => '',
                'hp_pen' => '',
                'alamat_pen' => json_encode([]),
                'resi' => '',
                'items' => json_encode([]),
                'kurir' => '',
                'id_midtrans' => $order_id,
                'status' => $status,
                'data_mid' => json_encode($body),
            ]);
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
        \Midtrans\Config::$serverKey = "Mid-server-uZVVVOFO2sD-nmeN1mfrcgpd";
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
    public function account()
    {
        $nama = session()->get("nama");
        $nohp = session()->get("nohp");
        $data = [
            'title' => 'Akun Saya',
            'nama' => $nama,
            'nohp' => $nohp,
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

        $data = [
            'title' => 'Akun Saya',
            'nama' => $nama,
            'nohp' => $nohp
        ];
        return view('pages/account', $data);
    }
    public function contact()
    {
        $data = [
            'title' => 'Kontak'
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
    public function product($id = false)
    {
        $produk = $this->barangModel->getBarang($id);
        $produksekategori = $this->barangModel->where('kategori', $produk['kategori'])->findAll(10, 0);
        $gambarnya = $this->gambarBarangModel->getGambar($id);
        $varian = json_decode($produk['varian'], true);
        $dimensi = explode("X", $produk['dimensi']);
        $data = [
            'title' => 'Produk',
            'produk' => $produk,
            'gambar' => $gambarnya,
            'varian' => $varian,
            'dimensi' => $dimensi,
            'produksekategori' => $produksekategori,
            'msg' => session()->getFlashdata('msg')
        ];
        return view('pages/product', $data);
    }

    public function productFilter($namaDash, $page = 1)
    {
        $nama = str_replace("-", " ", $namaDash);
        $pagination = (int)$page;
        if ($pagination > 1) {
            $hitungOffset = 20 * ($pagination - 1);
            $produk = $this->barangModel->like("nama", $nama, "both")->orderBy('nama', 'asc')->findAll(20, $hitungOffset);
        } else {
            $produk = $this->barangModel->like("nama", $nama, "both")->orderBy('nama', 'asc')->findAll(20, 0);
        }
        $semuaproduk = $this->barangModel->like("nama", $nama, "both")->orderBy('nama', 'asc')->findAll();

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
            'nama_cus' => $transaksi['nama_cus'],
            'email_cus' => $transaksi['email_cus'],
            'hp_cus' => $transaksi['hp_cus'],
            'nama_pen' => $transaksi['nama_pen'],
            'hp_pen' => $transaksi['hp_pen'],
            'alamat_pen' => json_decode($transaksi['alamat_pen'], true),
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
    public function qris($string)
    {
        $auth = base64_encode("Mid-server-uZVVVOFO2sD-nmeN1mfrcgpd" . ":");
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
    public function listCustomer($page = 1)
    {
        $transaksiCus = $this->pemesananModel->getPemesananPage($page);
        $semuaTransaksiCus = $this->pemesananModel->getPemesanan();
        $transaksiCusNoJSON = [];
        foreach ($transaksiCus as $transaksi) {
            $arr = [
                'id' => $transaksi['id'],
                'nama_cus' => $transaksi['nama_cus'],
                'email_cus' => $transaksi['email_cus'],
                'hp_cus' => $transaksi['hp_cus'],
                'nama_pen' => $transaksi['nama_pen'],
                'hp_pen' => $transaksi['hp_pen'],
                'alamat_pen' => json_decode($transaksi['alamat_pen'], true),
                'resi' => $transaksi['resi'],
                'id_midtrans' => $transaksi['id_midtrans'],
                'items' => json_decode($transaksi['items'], true),
                'status' => $transaksi['status'],
                'kurir' => $transaksi['kurir'],
                'data_mid' => json_decode($transaksi['data_mid'], true),
            ];
            array_push($transaksiCusNoJSON, $arr);
        }
        $transaksiJson = json_encode($transaksiCusNoJSON);
        $data = [
            'title' => 'List Customer',
            'transaksiCus' => $transaksiCusNoJSON,
            'semuaTransaksiCus' => $semuaTransaksiCus,
            'transaksiJson' => $transaksiJson,
            'page' => $page
        ];
        return view('pages/listCustomer', $data);
    }
    public function pdf($id_mid)
    {
        $transaksi = $this->pemesananModel->getPemesanan($id_mid);
        $arr = [
            'id' => $transaksi['id'],
            'nama_cus' => $transaksi['nama_cus'],
            'email_cus' => $transaksi['email_cus'],
            'hp_cus' => $transaksi['hp_cus'],
            'nama_pen' => $transaksi['nama_pen'],
            'hp_pen' => $transaksi['hp_pen'],
            'alamat_pen' => json_decode($transaksi['alamat_pen'], true),
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
    public function editResi()
    {
        $bodyJson = $this->request->getBody();
        $body = json_decode($bodyJson, true);
        $this->pemesananModel->where('id_midtrans', $body['idMid'])->set([
            'resi' => $body['resi'],
            'status' => 'Dikirim',
        ])->update();

        $list_item = "";
        foreach ($body['data']['items'] as $item) {
            $list_item = $list_item . "<p>" . $item['quantity'] . " " . $item['name'] . "</p>";
        }
        $email = \Config\Services::email();
        $email->setFrom('no-reply@jasminefurniture.com', 'Jasmine Furniture');
        $email->setTo($body['data']['email_cus']);
        $email->setSubject('Jasmine Store - Pesananmu sudah dikirim');
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
        $produk = $this->barangModel->getBarangPage($page);
        $semuaproduk = $this->barangModel->getBarang();
        $data = [
            'title' => 'List Produk',
            'produk' => $produk,
            'page' => $page,
            'semuaProduk' => $semuaproduk
        ];
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
        $this->barangModel->insert([
            'id'            => $tanggal,
            'nama'          => $this->request->getVar('nama'),
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
        ]);
        $this->gambarBarangModel->insert($insertGambarBarang);

        session()->setFlashdata('msg', 'Produk berhasil ditambahkan');
        return redirect()->to('/listproduct');
    }
    public function editProduct($id)
    {
        $produk = $this->barangModel->getBarang($id);
        $gambar = $this->gambarBarangModel->getGambar($id);
        $varian = json_decode($produk['varian'], true);
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
            ]);
            $this->gambarBarangModel->save($insertGambarBarang);
        } else {
            $this->barangModel->save([
                'id' => $id,
                'nama'          => $this->request->getVar('nama'),
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
            ]);
        }

        session()->setFlashdata('msg', 'Produk telah ditambahkan');
        return redirect()->to('/listproduct');
    }
    public function delProduct($id)
    {
        $produk = $this->barangModel->where('id', $id)->delete();
        $gambar = $this->gambarBarangModel->where('id', $id)->delete();
        return redirect()->to('/listproduct');
    }
}
