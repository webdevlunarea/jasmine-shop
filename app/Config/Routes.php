<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Pages::index');
$routes->get('/all', 'Pages::all');
$routes->get('/all/(:any)', 'Pages::all/$1');

$routes->get('/signup', 'Pages::signup');
$routes->post('/daftar', 'Pages::actionSignup');
$routes->get('/login', 'Pages::login');
$routes->post('/masuk', 'Pages::actionLogin');
$routes->get('/keluar', 'Pages::actionLogout');

$routes->get('/wishlist', 'Pages::wishlist');
$routes->get('/addwishlist/(:any)', 'Pages::addWishlist/$1');
$routes->get('/delwishlist/(:any)', 'Pages::delWishlist/$1');

$routes->get('/cart', 'Pages::cart');
$routes->get('/addcart/(:any)', 'Pages::addCart/$1');
$routes->get('/redcart/(:any)', 'Pages::redCart/$1');
$routes->get('/delcart/(:any)', 'Pages::delCart/$1');

$routes->get('/checkout', 'Pages::checkout');
$routes->get('/account', 'Pages::account');
$routes->get('/contact', 'Pages::contact');
$routes->get('/about', 'Pages::about');

// $routes->get('/gambar/(:any)', 'Pages::showGambar/$1');
$routes->get('/product/(:any)', 'Pages::product/$1');
$routes->get('/listproduct', 'Pages::listProduct');
$routes->get('/addproduct', 'Pages::addProduct');
$routes->post('/addproduct', 'Pages::actionAddProduct');
$routes->get('/editproduct/(:any)', 'Pages::editProduct/$1');
$routes->post('/editproduct/(:any)', 'Pages::actionEditProduct/$1');
$routes->get('/delproduct/(:any)', 'Pages::delProduct/$1');
$routes->post('/delproduct/(:any)', 'Pages::actionDelProduct/$1');