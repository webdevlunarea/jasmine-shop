<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Pages::index');
$routes->get('/kirimotp', 'Pages::kirimOTP', ['filter' => 'harusLogin']);
$routes->get('/all', 'Pages::all');
$routes->get('/all/(:any)', 'Pages::all/$1');
$routes->get('/page/(:any)', 'Pages::allPage/$1'); //page, subkategori
$routes->get('/page/(:any)/(:any)', 'Pages::allPage/$1/$2'); //page, subkategori
$routes->get('/kebijakan-privasi', 'Pages::kebijakanprivasi');
$routes->get('/syarat-dan-ketentuan', 'Pages::syaratdanketentuan');

$routes->get('/signup', 'Pages::signup', ['filter' => 'harusLogout']);
$routes->post('/daftar', 'Pages::actionSignup', ['filter' => 'harusLogout']);
$routes->get('/login', 'Pages::login', ['filter' => 'harusLogout']);
$routes->post('/masuk', 'Pages::actionLogin', ['filter' => 'harusLogout']);
$routes->get('/keluar', 'Pages::actionLogout');
$routes->get('/verify', 'Pages::verify', ['filter' => 'harusLogin']);
$routes->post('/verify', 'Pages::actionVerify', ['filter' => 'harusLogin']);

$routes->get('/wishlist', 'Pages::wishlist', ['filter' => 'harusUser']);
$routes->get('/addwishlist/(:any)', 'Pages::addWishlist/$1', ['filter' => 'harusUser']);
$routes->get('/delwishlist/(:any)', 'Pages::delWishlist/$1', ['filter' => 'harusUser']);
$routes->get('/wishlisttocart', 'Pages::wishlistToCart', ['filter' => 'harusUser']);

$routes->get('/cart', 'Pages::cart', ['filter' => 'harusUser']);
$routes->get('/addcart/(:any)/(:any)/(:any)', 'Pages::addCart/$1/$2/$3', ['filter' => 'harusUser']);
$routes->get('/redcart/(:any)', 'Pages::redCart/$1', ['filter' => 'harusUser']);
$routes->get('/delcart/(:any)', 'Pages::delCart/$1', ['filter' => 'harusUser']);

$routes->get('/tracking/(:any)/(:any)', 'Pages::tracking/$1/$2', ['filter' => 'harusUser']);

$routes->get('/checkout', 'Pages::checkout', ['filter' => 'harusUser']);
$routes->get('/getkota/(:any)', 'Pages::getKota/$1');
$routes->get('/getkec/(:any)', 'Pages::getKec/$1');
$routes->get('/updatealamat/(:any)', 'Pages::updateAlamat/$1');
$routes->get('/getdakota', 'Pages::getDakota');
$routes->get('/getpaket/(:any)/(:any)/(:any)/(:any)', 'Pages::getPaket/$1/$2/$3/$4');
$routes->post('/actioncheckout', 'Pages::actionCheckout');

$routes->get('/transaction', 'Pages::transaction', ['filter' => 'harusUser']);
$routes->post('/addtransaction', 'Pages::addTransaction');
$routes->get('/afteraddtransaction/(:any)', 'Pages::afterAddTransaction/$1', ['filter' => 'harusUser']);
$routes->post('/updatetransaction', 'Pages::updateTransaction');
$routes->get('/successpay', 'Pages::successPay', ['filter' => 'harusUser']);
$routes->get('/progresspay', 'Pages::progressPay', ['filter' => 'harusUser']);
$routes->get('/errorpay', 'Pages::errorPay', ['filter' => 'harusUser']);

$routes->get('/invoice/(:any)', 'Pages::invoice/$1', ['filter' => 'harusLogin']);
$routes->get('/qris/(:any)', 'Pages::qris/$1', ['filter' => 'harusLogin']);
$routes->get('/account', 'Pages::account', ['filter' => 'harusLogin']);
$routes->post('/account', 'Pages::editAccount', ['filter' => 'harusLogin']);
$routes->get('/contact', 'Pages::contact');
$routes->get('/about', 'Pages::about');

$routes->get('/product/(:any)', 'Pages::product/$1');
$routes->get('/productNama/(:any)', 'Pages::productFilter/$1');

$routes->get('/listcustomer', 'Pages::listCustomer', ['filter' => 'harusAdmin']);
$routes->get('/pdf/(:any)', 'Pages::pdf/$1', ['filter' => 'harusAdmin']);
$routes->post('/editresi', 'Pages::editResi');
$routes->get('/listproduct', 'Pages::listProduct', ['filter' => 'harusAdmin']);
$routes->get('/addproduct', 'Pages::addProduct', ['filter' => 'harusAdmin']);
$routes->post('/addproduct', 'Pages::actionAddProduct', ['filter' => 'harusAdmin']);
$routes->get('/editproduct/(:any)', 'Pages::editProduct/$1', ['filter' => 'harusAdmin']);
$routes->post('/editproduct/(:any)', 'Pages::actionEditProduct/$1', ['filter' => 'harusAdmin']);
$routes->get('/delproduct/(:any)', 'Pages::delProduct/$1', ['filter' => 'harusAdmin']);
$routes->post('/delproduct/(:any)', 'Pages::actionDelProduct/$1', ['filter' => 'harusAdmin']);

$routes->get('/apicomp', 'ApiCompany::index', ['filter' => 'corsFilter']);
$routes->get('/apicomp/getallbarang/(:any)', 'ApiCompany::getAllBarang/$1', ['filter' => 'corsFilter']);
$routes->get('/apicomp/barang/(:any)', 'ApiCompany::barang/$1', ['filter' => 'corsFilter']);
$routes->get('/apicomp/kategori/(:any)/(:any)', 'ApiCompany::kategori/$1/$2', ['filter' => 'corsFilter']);
$routes->get('/apicomp/subkategori/(:any)/(:any)', 'ApiCompany::subkategori/$1/$2', ['filter' => 'corsFilter']);
$routes->post('/apicomp/cari', 'ApiCompany::cari', ['filter' => 'corsFilter']);
$routes->get('/apicomp/gambar/(:any)', 'ApiCompany::gambar/$1', ['filter' => 'corsFilter']);
$routes->get('/apicomp/getgambarbarang/(:any)', 'ApiCompany::getGambarBarang/$1', ['filter' => 'corsFilter']);
$routes->get('/apicomp/getgambar/(:any)/(:any)', 'ApiCompany::getGambar/$1/$2', ['filter' => 'corsFilter']);
