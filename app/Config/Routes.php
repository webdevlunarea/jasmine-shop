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
$routes->get('/faq', 'Pages::faq');
$routes->get('/form', 'Pages::form');
$routes->get('/formthanks', 'Pages::formThanks');
$routes->post('/actionform', 'Pages::actionForm');

$routes->get('/signup', 'Pages::signup', ['filter' => 'harusLogout']);
$routes->post('/daftar', 'Pages::actionSignup', ['filter' => 'harusLogout']);
$routes->post('/daftarcoba', 'Pages::actionSignupCoba', ['filter' => 'harusLogout']);
$routes->get('/login', 'Pages::login', ['filter' => 'harusLogout']);
$routes->post('/masuk', 'Pages::actionLogin', ['filter' => 'harusLogout']);
$routes->get('/logintamu', 'Pages::actionLoginTamu', ['filter' => 'harusLogout']);
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
$routes->get('/getkode/(:any)', 'Pages::getKode/$1');
$routes->get('/updatealamat/(:any)/(:any)', 'Pages::updateAlamat/$1/$2');
$routes->get('/getdakota', 'Pages::getDakota');
$routes->get('/getpaket/(:any)/(:any)/(:any)/(:any)', 'Pages::getPaket/$1/$2/$3/$4');
$routes->post('/actioncheckout', 'Pages::actionCheckout');
$routes->post('/actionpay', 'Pages::actionPay');

$routes->get('/transaction', 'Pages::transaction', ['filter' => 'harusUser']);
$routes->post('/addtransaction', 'Pages::addTransaction');
$routes->get('/afteraddtransaction/(:any)', 'Pages::afterAddTransaction/$1', ['filter' => 'harusUser']);
$routes->post('/updatetransaction', 'Pages::updateTransaction');

$routes->get('/finish_urlMid/(:any)/(:any)', 'Pages::finishUrlMid/$1/$2');
$routes->get('/finish_url/(:any)', 'Pages::finishUrl/$1');
$routes->get('/finish_url/(:any)/(:any)', 'Pages::finishUrl/$1/$2'); //code rahasia = JSM-zWYWObdPEKlHA0PWP6BN
$routes->get('/successpay', 'Pages::successPay');
$routes->get('/progresspay', 'Pages::progressPay');
$routes->get('/errorpay', 'Pages::errorPay');

$routes->get('/invoice/(:any)', 'Pages::invoice/$1', ['filter' => 'harusLogin']);
$routes->get('/qris/(:any)', 'Pages::qris/$1', ['filter' => 'harusLogin']);
$routes->get('/account', 'Pages::account', ['filter' => 'harusLogin']);
$routes->post('/account', 'Pages::editAccount', ['filter' => 'harusLogin']);
$routes->get('/contact', 'Pages::contact');
$routes->get('/about', 'Pages::about');

$routes->get('/product/(:any)', 'Pages::product/$1');
$routes->get('/find/(:any)', 'Pages::productFilter/$1');
$routes->get('/find/(:any)/(:any)', 'Pages::productFilter/$1/$2');

$routes->get('/listform', 'Pages::listForm', ['filter' => 'harusAdmin']);
$routes->get('/listcustomer', 'Pages::listCustomer', ['filter' => 'harusAdmin']);
$routes->get('/listcustomer/(:any)', 'Pages::listCustomer/$1', ['filter' => 'harusAdmin']);
$routes->get('/pdf/(:any)', 'Pages::pdf/$1', ['filter' => 'harusAdmin']);
$routes->post('/editresi', 'Pages::editResi');
$routes->get('/listproduct', 'Pages::listProduct', ['filter' => 'harusAdmin']);
$routes->get('/listproduct/(:any)', 'Pages::listProduct/$1', ['filter' => 'harusAdmin']);
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
$routes->get('/apicomp/cari/(:any)/(:any)', 'ApiCompany::cari/$1/$2', ['filter' => 'corsFilter']);
$routes->get('/apicomp/gambar/(:any)', 'ApiCompany::gambar/$1', ['filter' => 'corsFilter']);
$routes->get('/apicomp/getgambarbarang/(:any)', 'ApiCompany::getGambarBarang/$1', ['filter' => 'corsFilter']);
$routes->get('/apicomp/getgambar/(:any)/(:any)', 'ApiCompany::getGambar/$1/$2', ['filter' => 'corsFilter']);

// GambarController
$routes->get('/imgart/(:any)', 'GambarController::tampilGambarArtikel/$1');
$routes->get('/imgart/(:any)/(:any)', 'GambarController::tampilGambarArtikel/$1/$2');

//Artikel
$routes->get('/article', 'Pages::article');
$routes->get('/article/category/(:any)', 'Pages::articleCategory/$1');
$routes->get('/article/(:any)', 'Pages::article/$1');
$routes->get('/addarticle', 'Pages::addArticle');
$routes->post('/addarticle', 'Pages::actionAddArticle', ['filter' => 'harusAdmin']);

// TrackingController
$routes->post('/addtracking', 'TrackingController::addTracking', ['filter' => 'corsFilter']);

// CopyGambarController
$routes->get('/copygambar', 'CopyGambarController::copyGambar');

$routes->get('(:any)', 'Pages::notFound');
