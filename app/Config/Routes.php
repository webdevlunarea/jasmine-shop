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
$routes->get('/logintamu/(:any)/(:any)/(:any)', 'Pages::actionLoginTamu/$1/$2/$3', ['filter' => 'harusLogout']);
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

// $routes->get('/tracking/(:any)/(:any)', 'Pages::tracking/$1/$2', ['filter' => 'harusUser']);

$routes->get('/checkout', 'Pages::checkout', ['filter' => 'harusUser']);
$routes->post('/getalamat', 'Pages::getAllSelectAlamat');
$routes->get('/getkota/(:any)', 'Pages::getKota/$1');
$routes->get('/getkec/(:any)', 'Pages::getKec/$1');
$routes->get('/getkode/(:any)', 'Pages::getKode/$1', ['filter' => 'corsFilter']);
$routes->get('/updatealamat/(:any)/(:any)', 'Pages::updateAlamat/$1/$2');
$routes->get('/getdakota', 'Pages::getDakota');
$routes->get('/getpaket/(:any)/(:any)/(:any)/(:any)', 'Pages::getPaket/$1/$2/$3/$4');
// $routes->post('/actioncheckout', 'Pages::actionCheckout');
// $routes->post('/actionpay', 'Pages::actionPay');
// $routes->post('/actionpaysnap', 'Pages::actionPaySnap');
$routes->post('/actionpaycore', 'Pages::actionPayCore');
$routes->get('/usevoucher/(:any)', 'Pages::useVoucher/$1');
$routes->get('/cancelvoucher/(:any)', 'Pages::cancelVoucher/$1');

$routes->get('/transaction', 'Pages::transaction', ['filter' => 'harusUser']);
// $routes->post('/addtransaction', 'Pages::addTransaction');
// $routes->get('/afteraddtransaction/(:any)', 'Pages::afterAddTransaction/$1', ['filter' => 'harusUser']);
$routes->post('/updatetransaction', 'Pages::updateTransaction');

// $routes->get('/finish_urlMid/(:any)/(:any)', 'Pages::finishUrlMid/$1/$2');
// $routes->get('/finish_url/(:any)', 'Pages::finishUrl/$1');
// $routes->get('/finish_url/(:any)/(:any)', 'Pages::finishUrl/$1/$2'); //code rahasia = JSM-zWYWObdPEKlHA0PWP6BN
// $routes->get('/successpay', 'Pages::successPay');
// $routes->get('/progresspay', 'Pages::progressPay');
// $routes->get('/errorpay', 'Pages::errorPay');
$routes->get('/order/(:any)', 'Pages::order/$1');
// $routes->get('/cancelorder/(:any)', 'Pages::cancelOrder/$1');
// $routes->get('/orderlocal', 'Pages::orderLocal');

$routes->get('/invoice/(:any)', 'Pages::invoice/$1', ['filter' => 'harusLogin']);
// $routes->get('/qris/(:any)', 'Pages::qris/$1', ['filter' => 'harusLogin']);
$routes->get('/account', 'Pages::account', ['filter' => 'harusLogin']);
$routes->post('/account', 'Pages::editAccount', ['filter' => 'harusLogin']);
$routes->get('/contact', 'Pages::contact');
$routes->get('/about', 'Pages::about');

$routes->get('/product/(:any)', 'Pages::product/$1');
$routes->get('/find/(:any)', 'Pages::productFilter/$1');
$routes->get('/find/(:any)/(:any)', 'Pages::productFilter/$1/$2');

$routes->get('/listvoucher', 'Pages::listVoucher', ['filter' => 'harusAdmin']);
$routes->get('/activevoucher/(:any)', 'Pages::activeVoucher/$1', ['filter' => 'harusAdmin']);
$routes->get('/addvoucher', 'Pages::addVoucher', ['filter' => 'harusAdmin']);
$routes->post('/actionaddvoucher', 'Pages::actionAddVoucher', ['filter' => 'harusAdmin']);
$routes->get('/listform', 'Pages::listForm', ['filter' => 'harusAdmin']);
$routes->get('/listcustomer', 'Pages::listCustomer', ['filter' => 'harusAdmin']);
$routes->get('/listcustomer/(:any)', 'Pages::listCustomer/$1', ['filter' => 'harusAdmin']);
$routes->get('/listcustomer/(:any)/(:any)', 'Pages::listCustomer/$1/$2', ['filter' => 'harusAdmin']);
$routes->get('/pdf/(:any)', 'Pages::pdf/$1', ['filter' => 'harusAdmin']);
$routes->post('/editresi', 'Pages::editResi');
$routes->get('/listproduct', 'Pages::listProduct', ['filter' => 'harusAdmin']);
$routes->get('/listproduct/(:any)', 'Pages::listProduct/$1', ['filter' => 'harusAdmin']);
$routes->get('/addproduct', 'Pages::addProduct', ['filter' => 'harusAdmin']);
$routes->post('/addproduct', 'Pages::actionAddProduct', ['filter' => 'harusAdmin']);
$routes->get('/editproduct/(:any)', 'Pages::editProduct/$1', ['filter' => 'harusAdmin']);
$routes->post('/editproduct/(:any)', 'Pages::actionEditProduct/$1', ['filter' => 'harusAdmin']);
$routes->get('/delproduct/(:any)', 'Pages::delProduct/$1', ['filter' => 'harusAdmin']);
$routes->get('/activeproduct/(:any)', 'Pages::activeProduct/$1', ['filter' => 'harusAdmin']);
// $routes->post('/delproduct/(:any)', 'Pages::actionDelProduct/$1', ['filter' => 'harusAdmin']);
$routes->get('/orderdone/(:any)', 'Pages::orderDone/$1', ['filter' => 'harusAdmin']);
$routes->post('/findproductadmin', 'Pages::actionFindProductAdmin', ['filter' => 'harusAdmin']);
$routes->get('/findproductadmin/(:any)', 'Pages::findProductAdmin/$1', ['filter' => 'harusAdmin']);
$routes->get('/findproductadmin/(:any)/(:any)', 'Pages::findProductAdmin/$1/$2', ['filter' => 'harusAdmin']);

$routes->get('/invoiceadmin', 'Pages::invoiceAdmin', ['filter' => 'harusAdmin']);
$routes->get('/invoiceadmin/(:any)', 'Pages::invoiceAdmin/$1', ['filter' => 'harusAdmin']);
$routes->get('/addinvoiceadmin', 'Pages::addInvoiceAdmin', ['filter' => 'harusAdmin']);
$routes->post('/actionaddinvoiceadmin', 'Pages::activeAddInvoiceAdmin', ['filter' => 'harusAdmin']);

// $routes->get('/apicomp', 'ApiCompany::index', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/getallbarang/(:any)', 'ApiCompany::getAllBarang/$1', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/barang/(:any)', 'ApiCompany::barang/$1', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/kategori/(:any)/(:any)', 'ApiCompany::kategori/$1/$2', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/subkategori/(:any)/(:any)', 'ApiCompany::subkategori/$1/$2', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/cari/(:any)/(:any)', 'ApiCompany::cari/$1/$2', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/gambar/(:any)', 'ApiCompany::gambar/$1', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/getgambarbarang/(:any)', 'ApiCompany::getGambarBarang/$1', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/getgambar/(:any)/(:any)', 'ApiCompany::getGambar/$1/$2', ['filter' => 'corsFilter']);
$routes->get('/olahdb/desk', 'ApiCompany::deskToLuna', ['filter' => 'corsFilter']);
$routes->get('/isipath', 'ApiCompany::isiPath', ['filter' => 'corsFilter']);


// GambarController
$routes->get('/imgart/(:any)', 'GambarController::tampilGambarArtikel/$1');
$routes->get('/imgart/(:any)/(:any)', 'GambarController::tampilGambarArtikel/$1/$2');

//Artikel
$routes->get('/article', 'Pages::article');
$routes->get('/article/category/(:any)', 'Pages::articleCategory/$1');
$routes->get('/article/(:any)', 'Pages::article/$1');
$routes->get('/addlikearticle/(:any)', 'Pages::addLikeArticle/$1');
$routes->get('/addsharearticle/(:any)', 'Pages::addShareArticle/$1');
$routes->get('/addarticle', 'Pages::addArticle');
$routes->post('/addarticle', 'Pages::actionAddArticle', ['filter' => 'harusAdmin']);
$routes->post('/submitemail/(:any)', 'Pages::submitEmail/$1');
$routes->post('/addkomen/(:any)', 'Pages::addKomen/$1');
$routes->get('/delkomen/(:any)/(:any)', 'Pages::delKomen/$1/$2');
$routes->post('/editkomen/(:any)/(:any)', 'Pages::editKomen/$1/$2');

// TrackingController
$routes->post('/addtracking', 'TrackingController::addTracking', ['filter' => 'corsFilter']);
// $routes->post('/closegreeting', 'TrackingController::closeGreeting', ['filter' => 'corsFilter']);
// $routes->post('/trackpop', 'TrackingController::trackPop', ['filter' => 'corsFilter']);

// CopyGambarController
$routes->get('/copygambar', 'CopyGambarController::copyGambar');

$routes->get('(:any)', 'Pages::notFound');
