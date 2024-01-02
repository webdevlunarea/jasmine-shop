<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\GambarBarangModel;
use App\Models\PembeliModel;
use App\Models\UserModel;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use KiriminAja\Base\Config\Cache\Mode;
use KiriminAja\Base\Config\KiriminAjaConfig;
use KiriminAja\Services\KiriminAja;

use function PHPUnit\Framework\isEmpty;

class Pages extends BaseController
{
    protected $barangModel;
    protected $gambarBarangModel;
    protected $userModel;
    protected $pembeliModel;
    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->gambarBarangModel = new GambarBarangModel();
        $this->userModel = new UserModel();
        $this->pembeliModel = new PembeliModel();
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
    public function all($subkategori = false)
    {
        $produk = $this->barangModel->where('subkategori', $subkategori)->findAll();
        $data = [
            'title' => 'Semua Produk',
            'produk' => $produk,
            'kategori' => $subkategori,
            'nama' => false
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
        $pesanEmail = "Masukan nomor OTP berikut pada akun Jasmine Furniture Store Anda\n" . $otp_number;
        // mail($this->request->getVar('email'), "OTP Jasmine Furniture Store", $pesanEmail);

        $emailLibrary = \Config\Services::email();
        $emailLibrary->setFrom('galih8.4.2001@gmail.com', 'Jasmine Furniture Store');
        $emailLibrary->setTo($this->request->getVar('email'));
        $emailLibrary->setSubject('Verifikasi OTP Jasmine Furniture Store');
        $emailLibrary->setMessage($pesanEmail);
        $emailLibrary->send();
        //blm berhasil kirim ke email

        $this->userModel->save([
            'email' => $this->request->getVar('email'),
            'sandi' => password_hash($this->request->getVar('sandi'), PASSWORD_DEFAULT),
            'role' => '0',
            'otp' => $otp_number,
            'active' => '0'
        ]);
        $this->pembeliModel->save([
            'email_user' => $this->request->getVar('email'),
            'alamat' => $this->request->getVar('alamat'),
            'wishlist' => json_encode([]),
            'keranjang' => json_encode([]),
        ]);

        $email = $this->request->getVar('email');
        $getUser = $this->userModel->getUser($email);
        $ses_data = [
            'email' => $getUser['email'],
            'active' => '0',
            'isLogin' => true
        ];
        session()->set($ses_data);
        session()->setFlashdata('msg', "OTP telah dikirim ke email " . $email);
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

        $getPembeli = $this->pembeliModel->getPembeli($email);
        $ses_data = [
            'active' => '1',
            'role' => $getUser['role'],
            'alamat' => $getPembeli['alamat'],
            'wishlist' => json_decode($getPembeli['wishlist'], true),
            'keranjang' => json_decode($getPembeli['keranjang'], true)
        ];
        $this->userModel->where('email', $email)->set([
            'active' => '1',
            'otp' => '0',
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
        $ses_data = ['email', 'role', 'alamat', 'wishlist', 'keranjang', 'isLogin', 'active'];
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
        $subtotal = 0;
        $berat = 0;
        if (!empty($keranjang)) {
            foreach ($keranjang as $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
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

        $data = [
            'title' => 'Keranjang',
            'produk' => $produk,
            'gambar' => $gambar,
            'jumlah' => $jumlah,
            'keranjang' => $keranjang,
            'tokenMid' => false,
            'berat' => $berat
            // 'ongkir' => (array)json_decode($ongkir)
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
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        if (!empty($keranjang)) {
            foreach ($keranjang as $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                $this->barangModel->save([
                    'id' => $element['id'],
                    'stok' => (int)$produknya['stok'] - (int)$element['jumlah'],
                ]);
            }
        }
        session()->set(['keranjang' => []]);
        $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode([])])->update();

        $data = [
            'title' => 'Pembayaran Sukses'
        ];
        return view('pages/successPay', $data);
    }
    public function progressPay()
    {
        $data = [
            'title' => 'Pembayaran Pending'
        ];
        return view('pages/progressPay', $data);
    }
    public function errorPay()
    {
        $data = [
            'title' => 'Pembayaran Gagal'
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
        $subtotal = 0;
        $berat = 0;
        if (!empty($keranjang)) {
            foreach ($keranjang as $element) {
                $produknya = $this->barangModel->getBarang($element['id']);
                array_push($produk, $produknya);
                array_push($jumlah, $element['jumlah']);

                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = $persen * $produknya['harga'];
                $subtotal += $hasil * $element['jumlah'];
                $berat += $produknya['berat'] * $element['jumlah'];
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
            'jumlah' => $jumlah,
            'berat' => $berat,
            'user' => $user,
            'total' => $total,
            'provinsi' => $provinsi["rajaongkir"]["results"],
            'keranjang' => $keranjang,
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
    public function actionCheckout()
    {
        $nama = $this->request->getVar('nama');
        $alamat = $this->request->getVar('alamat');
        $phone = $this->request->getVar('phone');
        $email = $this->request->getVar('email');
        $paket = $this->request->getVar('paket');

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
            'produksekategori' => $produksekategori
        ];
        return view('pages/product', $data);
    }

    public function productFilter($nama)
    {
        $produk = $this->barangModel->like("nama", $nama, "both")->findAll();
        $data = [
            'title' => 'Produk',
            'produk' => $produk,
            'nama' => $nama,
            'kategori' => false,
        ];
        return view('pages/all', $data);
    }

    //============ ADMIN ==============//
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