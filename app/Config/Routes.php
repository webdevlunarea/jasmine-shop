<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Pages::index');
$routes->get('/all', 'Pages::all');
$routes->get('/all/(:any)', 'Pages::all/$1');

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

$routes->get('/checkout', 'Pages::checkout', ['filter' => 'harusUser']);
$routes->get('/getarea/(:any)', 'Pages::getArea/$1');
$routes->get('/getkota/(:any)', 'Pages::getKota/$1');
$routes->post('/getrates', 'Pages::getRates');
$routes->get('/getpaket/(:any)/(:any)/(:any)/(:any)', 'Pages::getPaket/$1/$2/$3/$4');
$routes->post('/actioncheckout', 'Pages::actionCheckout');
$routes->get('/successpay', 'Pages::successPay', ['filter' => 'harusUser']);
$routes->get('/progresspay', 'Pages::progressPay', ['filter' => 'harusUser']);
$routes->get('/errorpay', 'Pages::errorPay', ['filter' => 'harusUser']);


$routes->get('/account', 'Pages::account', ['filter' => 'harusLogin']);
$routes->post('/account', 'Pages::editAccount', ['filter' => 'harusLogin']);
$routes->get('/contact', 'Pages::contact');
$routes->get('/about', 'Pages::about');

$routes->get('/product/(:any)', 'Pages::product/$1');
$routes->get('/productNama/(:any)', 'Pages::productFilter/$1');
$routes->get('/listproduct', 'Pages::listProduct', ['filter' => 'harusAdmin']);
$routes->get('/addproduct', 'Pages::addProduct', ['filter' => 'harusAdmin']);
$routes->post('/addproduct', 'Pages::actionAddProduct', ['filter' => 'harusAdmin']);
$routes->get('/editproduct/(:any)', 'Pages::editProduct/$1', ['filter' => 'harusAdmin']);
$routes->post('/editproduct/(:any)', 'Pages::actionEditProduct/$1', ['filter' => 'harusAdmin']);
$routes->get('/delproduct/(:any)', 'Pages::delProduct/$1', ['filter' => 'harusAdmin']);
$routes->post('/delproduct/(:any)', 'Pages::actionDelProduct/$1', ['filter' => 'harusAdmin']);
