<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\PembeliModel;
use App\Models\UserModel;

class Pages extends BaseController
{
    protected $barangModel;
    protected $userModel;
    protected $pembeliModel;
    public function __construct()
    {
        $this->barangModel = new BarangModel();
        $this->userModel = new UserModel();
        $this->pembeliModel = new PembeliModel();
    }
    public function index()
    {
        $produk = $this->barangModel->getBarang();
        $data = [
            'title' => 'Beranda',
            'produk' => $produk,
        ];
        return view('pages/home', $data);
    }
    public function all($kategori = false)
    {
        $produk = $this->barangModel->where('kategori', $kategori)->findAll();
        $data = [
            'title' => 'Semua Produk',
            'produk' => $produk,
            'kategori' => $kategori
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

        $this->userModel->save([
            'email' => $this->request->getVar('email'),
            'sandi' => password_hash($this->request->getVar('sandi'), PASSWORD_DEFAULT),
            'role' => '0',
        ]);
        $this->pembeliModel->save([
            'email_user' => $this->request->getVar('email'),
            'alamat' => $this->request->getVar('alamat'),
            'wishlist' => json_encode([]),
            'keranjang' => json_encode([]),
        ]);

        session()->setFlashdata('msg', 'Anda berhasil mendaftar');
        return redirect()->to('/login');
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
        if ($getUser['role'] == '0') {
            $getPembeli = $this->pembeliModel->getPembeli($email);
            $ses_data = [
                'email' => $getUser['email'],
                'role' => $getUser['role'],
                'alamat' => $getPembeli['alamat'],
                'wishlist' => json_decode($getPembeli['wishlist']),
                'keranjang' => (array)json_decode($getPembeli['keranjang']),
                'isLogin' => true
            ];
            session()->set($ses_data);
            return redirect()->to('/');
        } else {
            $ses_data = [
                'email' => $getUser['email'],
                'role' => $getUser['role'],
                'isLogin' => true
            ];
            session()->set($ses_data);
            echo "Anda masuk sebagai Admin";
        }
    }
    public function actionLogout()
    {
        $ses_data = ['email', 'role', 'alamat', 'wishlist', 'keranjang', 'isLogin'];
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
    public function cart()
    {
        $keranjang = session()->get('keranjang');
        $produk = [];
        $jumlah = [];
        if (!empty($keranjang)) {
            foreach ($keranjang as $key => $value) {
                array_push($produk, $this->barangModel->getBarang($key));
                array_push($jumlah, $value);
            }
        }
        $data = [
            'title' => 'Keranjang',
            'produk' => $produk,
            'jumlah' => $jumlah,
            'keranjang' => $keranjang
        ];
        return view('pages/cart', $data);
    }
    public function addCart($id_barang)
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        if (array_key_exists($id_barang, $keranjang)) $keranjang[$id_barang] += 1;
        else $keranjang[$id_barang] = 1;
        session()->set(['keranjang' => $keranjang]);

        $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjang)])->update();
        return redirect()->to('/cart');
    }
    public function redCart($id_barang)
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        if ($keranjang[$id_barang] > 1) $keranjang[$id_barang] -= 1;
        else unset($keranjang[$id_barang]);
        session()->set(['keranjang' => $keranjang]);

        $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjang)])->update();
        return redirect()->to('/cart');
    }
    public function delCart($id_barang)
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        unset($keranjang[$id_barang]);
        session()->set(['keranjang' => $keranjang]);

        $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjang)])->update();
        return redirect()->to('/cart');
    }
    public function checkout()
    {
        $user = [
            'alamat' => session()->get('alamat'),
            'email' => session()->get('email'),
        ];
        $data = [
            'title' => 'Check Out',
            'user' => $user
        ];
        return view('pages/checkout', $data);
    }
    public function account()
    {
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
        $data = [
            'title' => 'Produk',
            'produk' => $produk
        ];
        return view('pages/product', $data);
    }
}
