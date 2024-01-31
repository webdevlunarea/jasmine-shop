<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\GambarBarangModel;
use App\Models\PembeliModel;
use App\Models\PemesananModel;
use App\Models\UserModel;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use Faker\Core\Number;
use KiriminAja\Base\Config\Cache\Mode;
use KiriminAja\Base\Config\KiriminAjaConfig;
use KiriminAja\Services\KiriminAja;

use function PHPSTORM_META\map;
use function PHPUnit\Framework\isEmpty;

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
        $data = [
            'title' => 'Beranda',
            'produk' => $produk,
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
    public function all($subkategori = false)
    {
        $produk = $this->barangModel->where('subkategori', $subkategori)->findAll(20, 0);
        $semuaproduk = $this->barangModel->where('subkategori', $subkategori)->findAll();
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
            $produk = $this->barangModel->where('subkategori', $subkategori)->findAll(20, $hitungOffset);
        } else {
            $produk = $this->barangModel->where('subkategori', $subkategori)->findAll(20, 0);
        }
        $semuaproduk = $this->barangModel->where('subkategori', $subkategori)->findAll();
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
                'val_email' => session()->getFlashdata('val-email'),
                'val_sandi' => session()->getFlashdata('val-sandi'),
                'val_alamat' => session()->getFlashdata('val-alamat'),
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
        if (!$this->validate([
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
            'alamat' => [
                'rules' => 'required',
                'errors' => [
                    'required' => 'Alamat harus diisi'
                ]
            ]
        ])) {
            $validation = \Config\Services::validation();
            session()->setFlashdata('val-email', $validation->getError('email'));
            session()->setFlashdata('val-sandi', $validation->getError('sandi'));
            session()->setFlashdata('val-alamat', $validation->getError('alamat'));
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

        $this->userModel->save([
            'email' => $this->request->getVar('email'),
            'sandi' => password_hash($this->request->getVar('sandi'), PASSWORD_DEFAULT),
            'role' => '0',
            'otp' => $otp_number,
            'active' => '0',
            'waktu_otp' => $waktu_otp
        ]);
        $this->pembeliModel->save([
            'email_user' => $this->request->getVar('email'),
            'alamat' => $this->request->getVar('alamat'),
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
            'alamat' => $getPembeli['alamat'],
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
            ]
        ];
        return view('pages/login', $data);
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
        if (!$getUser) {
            session()->setFlashdata('msg', 'Email tidak terdaftar');
            return redirect()->to('/login');
        }
        $authSandi = password_verify($sandi, $getUser['sandi']);
        if (!$authSandi) {
            session()->setFlashdata('msg', 'Sandi salah');
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
                'alamat' => $getPembeli['alamat'],
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
    public function actionLogout()
    {
        $ses_data = ['email', 'role', 'alamat', 'wishlist', 'keranjang', 'isLogin', 'active', 'transaksi'];
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
            'title' => 'Masuk',
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
                    $hasil = $persen * $produknya['harga'];
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
            $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjangBaru)])->update();
            return redirect()->to('/cart');
        }
        session()->set(['keranjang' => $keranjang]);

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
        $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjangBaru)])->update();
        return redirect()->to('/cart');
    }
    public function successPay()
    {
        if (session()->getFlashdata('status_transaksi') == null) return redirect()->to('/');
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
        if (session()->getFlashdata('status_transaksi') == null) return redirect()->to('/');
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
        if (session()->getFlashdata('status_transaksi') == null) return redirect()->to('/');
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
        $produk = [];
        $jumlah = [];
        $produkJson = [];
        $subtotal = 0;
        $berat = 0;
        $beratHitung = 0;
        $dimensiSemua = [];
        if (!empty($keranjang)) {
            foreach ($keranjang as $ind => $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                array_push($produk, $produknya);
                array_push($jumlah, $element['jumlah']);

                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = $persen * $produknya['harga'];
                $subtotal += $hasil * $element['jumlah'];
                $dimensi = explode("X", $produknya['dimensi']);
                array_push($dimensiSemua, $produknya['dimensi']);
                $berat += $produknya['berat'] * $element['jumlah'];
                $beratHitung += (float)$dimensi[0] * (float)$dimensi[1] * (float)$dimensi[2] / 4000; //kg

                array_push($produkJson, array(
                    'name' => $produknya['nama'] . " (" . $element['varian'] . ")",
                    'description' => $produknya['deskripsi'],
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

        //Dapatkan data provinsi
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/province",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: cc2c0bc6b0af484079a445cc8da39490"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $provinsi = json_decode($response, true);

        $user = [
            'alamat' => $alamat,
            'email' => $email,
        ];
        $data = [
            'title' => 'Check Out',
            'produk' => $produk,
            'produkJson' => json_encode($produkJson),
            'jumlah' => $jumlah,
            'berat' => $berat, //kilogram
            'beratHitung' => $beratHitung, //kilogram
            'dimensiSemua' => implode("-", $dimensiSemua),
            'user' => $user,
            'total' => $total,
            'subtotal' => $subtotal,
            'provinsi' => $provinsi["rajaongkir"]["results"],
            'keranjang' => $keranjang
        ];
        return view('pages/checkout', $data);
    }
    public function getKota($id_prov)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/city?province=" . $id_prov,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: cc2c0bc6b0af484079a445cc8da39490"
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
    public function getPaket($asal, $tujuan, $berat, $kurir)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=" . $asal . "&destination=" . $tujuan . "&weight=" . $berat . "&courier=" . $kurir,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: cc2c0bc6b0af484079a445cc8da39490"
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
    public function getArea($kota)
    {
        $curl = curl_init();
        $input = str_replace(" ", "+", $kota);
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.biteship.com/v1/maps/areas?countries=ID&input=" . $input . "&type=single",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "authorization: biteship_test.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiamFzbWluZSB0ZXN0aW5nIiwidXNlcklkIjoiNjU4M2I1MmY2YzAyMTAxZjVhZTJlNWY5IiwiaWF0IjoxNzAzMTMxOTQ5fQ.22F0VWJe-JavNsxaw_s68ErNv41cTVcYIm1OWtJF9og"
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
    public function getRates()
    {
        $curl = curl_init();
        $body = $this->request->getBody();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.biteship.com/v1/rates/couriers",
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
                "authorization: biteship_test.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJuYW1lIjoiamFzbWluZSB0ZXN0aW5nIiwidXNlcklkIjoiNjU4M2I1MmY2YzAyMTAxZjVhZTJlNWY5IiwiaWF0IjoxNzAzMTMxOTQ5fQ.22F0VWJe-JavNsxaw_s68ErNv41cTVcYIm1OWtJF9og",
                "content-type: application/json"
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
        $nama = $this->request->getVar('nama');
        $alamat = $this->request->getVar('alamat');
        $phone = $this->request->getVar('phone');
        $email = $this->request->getVar('email');
        $paketData = $this->request->getVar('paket');
        $paket = base64_decode($paketData);

        $getPembeli = $this->pembeliModel->getPembeli($email);
        $keranjang = json_decode($getPembeli['keranjang'], true);
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
                $hasil = $persen * $produknya['harga'];
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

        \Midtrans\Config::$serverKey = "SB-Mid-server-PyBwfT6Pz13tcj4IBVtlwp9f";
        \Midtrans\Config::$isProduction = false;
        $params = array(
            'transaction_details' => array(
                'order_id' => rand(),
                'gross_amount' => $total,
            ),
            'customer_details' => array(
                'email' => $email,
                'first_name' => $nama,
                'phone' => $phone,
                'billing_address' => array(
                    'email' => $email,
                    'first_name' => $nama,
                    'phone' => $phone,
                    'address' => $alamat,
                ),
                'shipping_address' => array(
                    'email' => $email,
                    'first_name' => $nama,
                    'phone' => $phone,
                    'address' => $alamat,
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
            'phone' => $phone,
        );
        return $this->response->setJSON($arr, false);
    }

    public function tracking($tipe, $resi)
    {
        $curl = curl_init();
        if($tipe == "da"){
            curl_setopt_array($curl, array(
                CURLOPT_URL => "http://www.dakotacargo.co.id/api/api_trace_package.asp?b=" . $resi,
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
        } else if($tipe == "ro"){
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://pro.rajaongkir.com/api/waybill",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "waybill=SOCAG00183235715&courier=jne",
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


        $bulan= ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
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
            'hasilnya'=> $hasilnya,
            'bulan'=> $bulan,
        ];
        return view('pages/tracking', $data);

    }
    public function transaction()
    {
        $email = session()->get("email");
        $transaksi = session()->get("transaksi");
        $auth = base64_encode("SB-Mid-server-PyBwfT6Pz13tcj4IBVtlwp9f" . ":");

        $cekAdaEror = false;
        foreach ($transaksi as $t_ind => $t) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.sandbox.midtrans.com/v2/" . $t . "/status",
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
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
            $item_status = json_decode($response, true);

            if (!isset($item_status['transaction_status'])) {
                $cekAdaEror = true;
                break;
            }
            $oldTransaction = $this->pemesananModel->getPemesanan($t);
            if ($item_status['transaction_status'] != json_decode($oldTransaction['data_mid'], true)['transaction_status']) {
                switch ($item_status['transaction_status']) {
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
                    default:
                        $status = "No Status";
                        break;
                }
                $this->pemesananModel->where('id_midtrans', $t)->set([
                    'status' => $status,
                    'data_mid' => json_encode($item_status),
                ])->update();
            }
        }

        $detailTransaksi = $this->pemesananModel->getPemesananCus($email);
        $data = [
            'title' => 'Transaksi Pembayaran',
            'transaksi' => $detailTransaksi,
            'cekEror' => $cekAdaEror
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
            'alamat_cus' => $body['alamatCus'],
            'hp_cus' => $body['hpCus'],
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
                'alamat_cus' => $body['alamatCus'],
                'hp_cus' => $body['hpCus'],
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
    public function updateTransaction()
    {
        // $bodyJson = $this->request->getBody();
        // $body = json_decode($bodyJson, true);
        \Midtrans\Config::$serverKey = "SB-Mid-server-PyBwfT6Pz13tcj4IBVtlwp9f";
        \Midtrans\Config::$isProduction = false;

        $notif = new \Midtrans\Notification();
        $transaction = $notif->transaction_status;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;

        session()->setFlashdata('status_transaksi', $transaction);
        session()->setFlashdata('id_pesanan', $order_id);
        if ($fraud == "accept") {
            switch ($transaction) {
                case 'settlement':
                    return redirect()->to('/successpay');
                    break;
                case 'capture':
                    return redirect()->to('/successpay');
                    break;
                case 'pending':
                    return redirect()->to('/progresspay');
                    break;
                case 'expire':
                    return redirect()->to('/errorpay');
                    break;
                case 'deny':
                    return redirect()->to('/errorpay');
                    break;
                case 'failure':
                    return redirect()->to('/errorpay');
                    break;
                case 'refund':
                    echo "Pembayaran Id:" . $order_id . " status:" . $transaction;
                    break;
                case 'partial_refund':
                    echo "Pembayaran Id:" . $order_id . " status:" . $transaction;
                    break;
                default:
                    echo "Pembayaran Id:" . $order_id . " status:" . $transaction;
                    break;
            }
        }
    }
    public function account()
    {
        $data = [
            'title' => 'Akun Saya'
        ];
        return view('pages/account', $data);
    }

    public function editAccount()
    {
        $email = session()->get("email");
        $role = session()->get("role");
        $sandi = $this->request->getVar('sandi');
        $alamat = $this->request->getVar('alamat');

        if ($sandi != '') {
            $this->userModel->where('email', $email)->set([
                'sandi' => password_hash($sandi, PASSWORD_DEFAULT),
            ])->update();
        }
        if ($role == '0') {
            $this->pembeliModel->where('email_user', $email)->set([
                'alamat' => $alamat
            ])->update();

            session()->set([
                'alamat' => $alamat,
            ]);
        }

        $data = [
            'title' => 'Akun Saya'
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
        $produksekategori = $this->barangModel->where('kategori', $produk['kategori'])->findAll();
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

    public function productFilter($nama)
    {
        $produk = $this->barangModel->like("nama", $nama, "both")->findAll(20, 0);
        $semuaproduk = $this->barangModel->like("nama", $nama, "both")->findAll();
        $data = [
            'title' => 'Produk',
            'produk' => $produk,
            'nama' => $nama,
            'kategori' => false,
            'semuaProduk' => $semuaproduk,
            'page' => 1,
        ];
        return view('pages/all', $data);
    }

    //============ ADMIN ==============//
    public function listCustomer()
    {
        $transaksiCus = $this->pemesananModel->getPemesanan();
        $transaksiCusNoJSON = [];
        foreach ($transaksiCus as $transaksi) {
            $arr = [
                'id' => $transaksi['id'],
                'nama_cus' => $transaksi['nama_cus'],
                'email_cus' => $transaksi['email_cus'],
                'alamat_cus' => $transaksi['alamat_cus'],
                'hp_cus' => $transaksi['hp_cus'],
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
            'transaksiCus' => $transaksiCus,
            'transaksiJson' => $transaksiJson,
        ];
        return view('pages/listCustomer', $data);
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
    public function listProduct()
    {
        $produk = $this->barangModel->getBarang();
        $data = [
            'title' => 'List Produk',
            'produk' => $produk,
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
        $hasilVarian = count(explode(",", $this->request->getVar('varian'))) * (int)$this->request->getVar('jml_varian');
        $gambarnya = [];
        $insertGambarBarang = [
            'id' => $tanggal
        ];
        for ($i = 1; $i <= $hasilVarian; $i++) {
            array_push($gambarnya, file_get_contents($this->request->getFile("gambar" . $i)));
            $insertGambarBarang["gambar" . $i] = file_get_contents($this->request->getFile("gambar" . $i));
        }
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
        if (!empty($_FILES['gambar1']['tmp_name'])) {
            $hasilVarian = count(explode(",", $this->request->getVar('varian'))) * (int)$this->request->getVar('jml_varian');
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