<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\GambarBarangModel;
use App\Models\PembeliModel;
use App\Models\UserModel;
use CodeIgniter\Files\Exceptions\FileNotFoundException;

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
    public function all($kategori = false)
    {
        $produk = $this->barangModel->where('kategori', $kategori)->findAll();
        $data = [
            'title' => 'Semua Produk',
            'produk' => $produk,
            'kategori' => $kategori,
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
                'wishlist' => (array)json_decode($getPembeli['wishlist']),
                'keranjang' => (array)json_decode($getPembeli['keranjang']),
                'isLogin' => true
            ];
            session()->set($ses_data);
            return redirect()->to(site_url('/'));
        } else {
            $ses_data = [
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

    public function wishlistToCart()
    {
        $wishlist = session()->get('wishlist');
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        foreach ($wishlist as $id_barang) {
            if (array_key_exists($id_barang, $keranjang)) $keranjang[$id_barang] += 1;
            else $keranjang[$id_barang] = 1;
            session()->set(['keranjang' => $keranjang]);
        }

        $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode($keranjang)])->update();
        return redirect()->to('/cart');
    }
    public function cart()
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        $produk = [];
        $jumlah = [];
        $itemDetails = [];
        $subtotal = 0;
        if (!empty($keranjang)) {
            foreach ($keranjang as $key => $value) {
                $produknya = $this->barangModel->getBarang($key);
                array_push($produk, $produknya);
                array_push($jumlah, $value);
                $item = array(
                    'id' => $produknya["id"],
                    'price' => $produknya["harga"],
                    'quantity' => $value,
                    'name' => $produknya["nama"],
                );
                array_push($itemDetails, $item);

                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = $persen * $produknya['harga'];
                $subtotal += $hasil * $value;
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
            'jumlah' => $jumlah,
            'keranjang' => $keranjang,
            'tokenMid' => false
        ];

        if (!isset($total)) {
            return view('pages/cart', $data);
        }

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
    public function successPay()
    {
        $keranjang = session()->get('keranjang');
        $email = session()->get('email');
        $ceking = [];
        if (!empty($keranjang)) {
            foreach ($keranjang as $key => $value) {
                $produknya = $this->barangModel->getBarang($key);
                $this->barangModel->save([
                    'id' => $key,
                    'stok' => (int)$produknya['stok'] - (int)$value,
                ]);
                array_push($ceking, $value);
            }
        }

        session()->set(['keranjang' => []]);
        $this->pembeliModel->where('email_user', $email)->set(['keranjang' => json_encode([])])->update();

        $data = [
            'title' => 'Pembayaran Sukses',
            'ceking' => implode(" ", $ceking),
            'keranjang' => implode(" ", $keranjang),
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
        if (!empty($keranjang)) {
            foreach ($keranjang as $key => $value) {
                $produknya = $this->barangModel->getBarang($key);
                array_push($produk, $produknya);
                array_push($jumlah, $value);

                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = $persen * $produknya['harga'];
                $subtotal += $hasil * $value;
            }
            $total = $subtotal + 10000;
        }

        $user = [
            'alamat' => $alamat,
            'email' => $email,
        ];
        $data = [
            'title' => 'Check Out',
            'produk' => $produk,
            'jumlah' => $jumlah,
            'user' => $user,
            'total' => $total
        ];
        return view('pages/checkout', $data);
    }
    public function actionCheckout()
    {
        $nama = $this->request->getVar('nama');
        $alamat = $this->request->getVar('alamat');
        $phone = $this->request->getVar('phone');
        $email = $this->request->getVar('email');

        $getPembeli = $this->pembeliModel->getPembeli($email);
        $keranjang = (array)json_decode($getPembeli['keranjang']);
        $produk = [];
        $jumlah = [];
        $subtotal = 0;
        $total = 0;
        $itemDetails = [];
        if (!empty($keranjang)) {
            foreach ($keranjang as $key => $value) {
                $produknya = $this->barangModel->getBarang($key);
                array_push($produk, $produknya);
                array_push($jumlah, $value);
                $item = array(
                    'id' => $produknya["id"],
                    'price' => $produknya["harga"],
                    'quantity' => $value,
                    'name' => $produknya["nama"],
                );
                array_push($itemDetails, $item);

                $persen = (100 - $produknya['diskon']) / 100;
                $hasil = $persen * $produknya['harga'];
                $subtotal += $hasil * $value;
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
        $gambarnya = $this->gambarBarangModel->getGambar($id);
        $data = [
            'title' => 'Produk',
            'produk' => $produk,
            'gambar' => $gambarnya
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
        $tanggal = "B" . date("Ymdhis", $d);

        $gambarnya = file_get_contents($this->request->getFile('gambar'));
        $gambarnya1 = file_get_contents($this->request->getFile('gambar1'));
        $gambarnya2 = file_get_contents($this->request->getFile('gambar2'));
        $gambarnya3 = !isEmpty($this->request->getFile('gambar3')) ? file_get_contents($this->request->getFile('gambar3')) : null;
        $gambarnya4 = !isEmpty($this->request->getFile('gambar4')) ? file_get_contents($this->request->getFile('gambar4')) : null;

        $this->barangModel->insert([
            'id' => $tanggal,
            'nama' => $this->request->getVar('nama'),
            'gambar' => $gambarnya,
            'harga' => $this->request->getVar('harga'),
            'stok' => $this->request->getVar('stok'),
            'deskripsi' => $this->request->getVar('deskripsi'),
            'kategori' => $this->request->getVar('kategori'),
            'subkategori' => $this->request->getVar('subkategori'),
            'diskon' => $this->request->getVar('diskon'),
        ]);
        $this->gambarBarangModel->insert([
            'id' => $tanggal,
            'gambar1' => $gambarnya,
            'gambar2' => $gambarnya1,
            'gambar3' => $gambarnya2,
            'gambar4' => $gambarnya3,
            'gambar5' => $gambarnya4,
        ]);

        session()->setFlashdata('msg', 'Produk telah ditambahkan');
        return redirect()->to('/listproduct');
    }
    public function editProduct($id)
    {
        $produk = $this->barangModel->getBarang($id);
        $gambar = $this->gambarBarangModel->getGambar($id);
        $data = [
            'title' => 'Edit Produk',
            'produk' => $produk,
            'gambar' => $gambar
        ];
        return view('pages/editProduct', $data);
    }
    public function actionEditProduct($id)
    {
        if (!empty($_FILES['gambar']['tmp_name'])) {
            $gambarnya = file_get_contents($this->request->getFile('gambar'));
            $gambarnya1 = file_get_contents($this->request->getFile('gambar1'));
            $gambarnya2 = file_get_contents($this->request->getFile('gambar2'));
            $gambarnya3 = !isEmpty($this->request->getFile('gambar3')) ? file_get_contents($this->request->getFile('gambar3')) : null;
            $gambarnya4 = !isEmpty($this->request->getFile('gambar4')) ? file_get_contents($this->request->getFile('gambar4')) : null;
            $this->barangModel->save([
                'id' => $id,
                'nama' => $this->request->getVar('nama'),
                'gambar' => $gambarnya,
                'harga' => $this->request->getVar('harga'),
                'stok' => $this->request->getVar('stok'),
                'deskripsi' => $this->request->getVar('deskripsi'),
                'kategori' => $this->request->getVar('kategori'),
                'subkategori' => $this->request->getVar('subkategori'),
                'diskon' => $this->request->getVar('diskon'),
            ]);
            $this->gambarBarangModel->save([
                'id' => $id,
                'gambar1' => $gambarnya,
                'gambar2' => $gambarnya1,
                'gambar3' => $gambarnya2,
                'gambar4' => $gambarnya3,
                'gambar5' => $gambarnya4,
            ]);
        } else {
            $this->barangModel->save([
                'id' => $id,
                'nama' => $this->request->getVar('nama'),
                'harga' => $this->request->getVar('harga'),
                'stok' => $this->request->getVar('stok'),
                'deskripsi' => $this->request->getVar('deskripsi'),
                'kategori' => $this->request->getVar('kategori'),
                'subkategori' => $this->request->getVar('subkategori'),
                'diskon' => $this->request->getVar('diskon'),
            ]);
        }

        session()->setFlashdata('msg', 'Produk telah ditambahkan');
        return redirect()->to('/listproduct');
    }
    public function delProduct($id)
    {
        $produk = $this->barangModel->where('id', $id)->delete();
        return redirect()->to('/listproduct');
    }
}
