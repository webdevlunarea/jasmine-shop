<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Pages::index');
$routes->get('/sitemap.xml', 'SitemapController::index');
$routes->get('/ref/(:segment)', 'AndroidReferralLink::show/$1');

// Versioned JSON API for the native Android client. Authentication uses opaque
// bearer tokens; state-changing API calls do not rely on browser cookies/CSRF.
$routes->group('api/v1', ['filter' => 'apiVersion'], static function ($routes) {
    $routes->post('auth/login', 'Api\\V1\\AuthController::login');
    $routes->post('auth/google', 'Api\\V1\\AuthController::googleLogin');
    $routes->post('auth/mfa/complete', 'Api\\V1\\AuthController::completeMfaLogin');
    $routes->post('auth/refresh', 'Api\\V1\\AuthController::refresh');
    $routes->post('auth/register', 'Api\\V1\\AuthController::register');
    $routes->post('auth/verify-email', 'Api\\V1\\AuthController::verifyEmail');
    $routes->post('auth/verification/resend', 'Api\\V1\\AuthController::resendVerification');
    $routes->post('auth/password-reset/request', 'Api\\V1\\AuthController::requestPasswordReset');
    $routes->post('auth/password-reset/complete', 'Api\\V1\\AuthController::completePasswordReset');
    $routes->get('catalog/products', 'Api\\V1\\CatalogController::products');
    $routes->get('catalog/products/(:segment)', 'Api\\V1\\CatalogController::product/$1');
    $routes->get('catalog/categories', 'Api\\V1\\CatalogController::categories');
    $routes->get('home/banners', 'Api\\V1\\BannerController::index');
    $routes->get('home/flash-sale', 'Api\\V1\\FlashSaleController::index');
    $routes->get('articles', 'Api\\V1\\ArticleController::index');
    $routes->get('articles/(:segment)', 'Api\\V1\\ArticleController::show/$1');
    $routes->get('vouchers/promotions', 'Api\\V1\\VoucherController::promotions');
    $routes->get('regions/provinces', 'Api\\V1\\RegionController::provinces');
    $routes->get('regions/cities', 'Api\\V1\\RegionController::cities');
    $routes->get('regions/districts', 'Api\\V1\\RegionController::districts');
    $routes->get('regions/villages', 'Api\\V1\\RegionController::villages');

    $routes->group('', ['filter' => 'apiCustomer'], static function ($routes) {
        $routes->post('auth/logout', 'Api\\V1\\AuthController::logout');
        $routes->post('auth/account-deletion/request', 'Api\\V1\\AuthController::requestAccountDeletion');
        $routes->get('auth/sessions', 'Api\\V1\\AuthController::sessions');
        $routes->post('auth/sessions/(:segment)/revoke', 'Api\\V1\\AuthController::revokeSession/$1');
        $routes->post('auth/sessions/revoke-others', 'Api\\V1\\AuthController::revokeOtherSessions');
        $routes->post('auth/password/change', 'Api\\V1\\AuthController::changePassword');
        $routes->get('auth/mfa/status', 'Api\\V1\\AuthController::mfaStatus');
        $routes->post('auth/mfa/totp/setup', 'Api\\V1\\AuthController::beginMfaSetup');
        $routes->post('auth/mfa/totp/confirm', 'Api\\V1\\AuthController::confirmMfaSetup');
        $routes->post('auth/mfa/totp/disable', 'Api\\V1\\AuthController::disableMfa');
        $routes->post('auth/email/change/request', 'Api\\V1\\AuthController::requestEmailChange');
        $routes->post('auth/email/change/confirm', 'Api\\V1\\AuthController::confirmEmailChange');
        $routes->get('me', 'Api\\V1\\AuthController::me');
        $routes->patch('me', 'Api\\V1\\ProfileController::update');
        $routes->post('me/photo', 'Api\\V1\\ProfileController::uploadPhoto');
        $routes->get('me/username', 'Api\\V1\\UsernameController::show');
        $routes->put('me/username', 'Api\\V1\\UsernameController::save');
        $routes->get('wishlist', 'Api\\V1\\WishlistController::index');
        $routes->post('wishlist/items', 'Api\\V1\\WishlistController::add');
        $routes->delete('wishlist/items', 'Api\\V1\\WishlistController::delete');
        $routes->get('cart', 'Api\\V1\\CartController::index');
        $routes->post('cart/items', 'Api\\V1\\CartController::add');
        $routes->patch('cart/items', 'Api\\V1\\CartController::update');
        $routes->delete('cart/items', 'Api\\V1\\CartController::delete');
        $routes->get('addresses', 'Api\\V1\\AddressController::index');
        $routes->post('addresses', 'Api\\V1\\AddressController::save');
        $routes->put('addresses', 'Api\\V1\\AddressController::save');
        $routes->delete('addresses/(:num)', 'Api\\V1\\AddressController::delete/$1');
        $routes->post('addresses/(:num)/default', 'Api\\V1\\AddressController::setDefault/$1');
        $routes->post('shipping/quotes', 'Api\\V1\\ShippingController::quote');
        $routes->get('vouchers/available', 'Api\\V1\\VoucherController::index');
        $routes->get('points', 'Api\\V1\\PointsController::index');
        $routes->get('referrals', 'Api\\V1\\ReferralController::index');
        $routes->post('referrals/attribute', 'Api\\V1\\ReferralController::attribute');
        $routes->post('checkout/preview', 'Api\\V1\\CheckoutController::preview');
        $routes->post('checkout/orders', 'Api\\V1\\CheckoutController::createOrder');
        $routes->get('orders', 'Api\\V1\\OrderController::index');
        $routes->post('orders/(:segment)/cancel', 'Pages::cancelOrderApi/$1');
        $routes->post('orders/(:segment)/payment-proof', 'Api\\V1\\PaymentProofController::upload/$1');
        $routes->get('orders/(:segment)/payment-status', 'Api\\V1\\OrderController::paymentStatus/$1');
        $routes->get('orders/(:segment)', 'Api\\V1\\OrderController::show/$1');
        $routes->get('orders/(:segment)/tracking', 'Api\\V1\\OrderController::tracking/$1');
        $routes->post('push/devices', 'Api\\V1\\PushDeviceController::register');
        $routes->delete('push/devices', 'Api\\V1\\PushDeviceController::unregister');
        $routes->get('notifications', 'Api\\V1\\NotificationController::index');
        $routes->post('notifications/read', 'Api\\V1\\NotificationController::markRead');
    });
});

$routes->post('/kirimotp', 'Pages::kirimOTP', ['filter' => ['harusLogin', 'csrf']]);
$routes->post('/kirimemail', 'Pages::kirimEmail', ['filter' => ['harusLogin', 'csrf']]);
$routes->get('/all', 'Pages::all');
$routes->get('/all/(:any)', 'Pages::all/$1');
$routes->get('/search/suggest', 'Pages::searchSuggest');
$routes->get('/page/(:any)', 'Pages::allPage/$1'); //page, subkategori
$routes->get('/page/(:any)/(:any)', 'Pages::allPage/$1/$2'); //page, subkategori
$routes->get('/kebijakan-privasi', 'Pages::kebijakanprivasi');
$routes->get('/hapus-akun', 'Pages::hapusAkun');
$routes->get('/akun-dibatasi', 'Pages::akunDibatasi');
$routes->get('/syarat-dan-ketentuan', 'Pages::syaratdanketentuan');
$routes->get('/faq', 'Pages::faq');
$routes->match(['get', 'post'], '/lacak-pengiriman', 'Pages::lacakPengiriman');
$routes->get('/form', 'Pages::form');
$routes->get('/formthanks', 'Pages::formThanks');
$routes->post('/actionform', 'Pages::actionForm', ['filter' => 'csrf']);

$routes->get('/sendwa', 'Pages::sendwa');
$routes->get('/signup', 'Pages::signup', ['filter' => 'harusLogout']);
$routes->post('/daftar', 'Pages::actionSignup', ['filter' => ['harusLogout', 'csrf']]);
$routes->post('/daftarcoba', 'Pages::actionSignupCoba', ['filter' => ['harusLogout', 'csrf']]);
$routes->get('/login', 'Pages::login', ['filter' => 'harusLogout']);
$routes->post('/masuk', 'Pages::actionLogin', ['filter' => ['harusLogout', 'csrf']]);
$routes->get('/lupa-sandi', 'PasswordReset::requestForm');
$routes->post('/lupa-sandi', 'PasswordReset::requestCode', ['filter' => 'csrf']);
$routes->get('/reset-sandi', 'PasswordReset::resetForm');
$routes->post('/reset-sandi', 'PasswordReset::complete', ['filter' => 'csrf']);
$routes->get('/auth/google', 'Pages::googleLogin');
$routes->get('/auth/google/callback', 'Pages::googleCallback');
$routes->get('/auth/facebook', 'Pages::facebookLogin');
$routes->get('/auth/facebook/callback', 'Pages::facebookCallback');
$routes->post('/auth/facebook/link', 'Pages::facebookLink', ['filter' => ['harusUser', 'csrf']]);
// Client-side localStorage cleanup and safe same-site redirect after login.
$routes->get('/hapuslocalstorage/(:any)', 'Pages::hapusLocalStorage/$1');
$routes->post('/keluar', 'Pages::actionLogout', ['filter' => 'csrf']);
$routes->post('/keluar/regist', 'Pages::actionLogoutRegist', ['filter' => 'csrf']);
$routes->get('/verify/url/redirect/(:any)', 'Pages::verifyUrlRedirect/$1', ['filter' => 'harusLogin']);
$routes->get('/verify/url/(:any)', 'Pages::verifyUrl/$1', ['filter' => 'harusLogin']);
$routes->get('/verify', 'Pages::verify', ['filter' => 'harusLogin']);
$routes->post('/verify', 'Pages::actionVerify', ['filter' => ['harusLogin', 'csrf']]);

$routes->get('/wishlist', 'Pages::wishlist', ['filter' => 'harusUser']);
$routes->post('/addwishlist/(:any)', 'Pages::addWishlist/$1', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/delwishlist/(:any)', 'Pages::delWishlist/$1', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/wishlisttocart', 'Pages::wishlistToCart', ['filter' => ['harusUser', 'csrf']]);

$routes->get('/cart', 'Pages::cart', ['filter' => 'harusUser']);
$routes->post('/addcart/(:any)/(:any)/(:any)', 'Pages::addCart/$1/$2/$3', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/addcart/(:any)/(:any)/(:any)/(:any)', 'Pages::addCart/$1/$2/$3/$4', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/redcart/(:any)', 'Pages::redCart/$1', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/delcart/(:any)', 'Pages::delCart/$1', ['filter' => ['harusUser', 'csrf']]);

// $routes->get('/tracking/(:any)/(:any)', 'Pages::tracking/$1/$2', ['filter' => 'harusUser']);

$routes->get('/checkout', 'Pages::checkout', ['filter' => 'harusUser']);
$routes->post('/account/addresses/manage', 'CustomerAddresses::manage', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/shipping/quote', 'Pages::shippingQuote', ['filter' => 'harusUser']);
$routes->post('/gantikaca/(:any)', 'Pages::gantiKaca/$1', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/getadminfee', 'Pages::getBiayaAdminMidtrans');
$routes->get('/getkota/(:any)', 'Pages::getKota/$1');
$routes->get('/getkec/(:any)', 'Pages::getKec/$1');
$routes->get('/getkode/(:any)', 'Pages::getKode/$1', ['filter' => 'corsFilter']);
$routes->post('/updatealamat/(:any)/(:any)', 'Pages::updateAlamat/$1/$2', ['filter' => 'csrf']);
// $routes->post('/actioncheckout', 'Pages::actionCheckout');
// $routes->post('/actionpay', 'Pages::actionPay');
// $routes->post('/actionpaysnap', 'Pages::actionPaySnap');
$routes->post('/actionpaycore', 'Pages::actionPayCore', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/usevoucher/(:any)', 'Pages::useVoucher/$1', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/cancelvoucher/(:any)', 'Pages::cancelVoucher/$1', ['filter' => ['harusUser', 'csrf']]);
$routes->get('/voucher', 'Pages::voucher', ['filter' => 'harusUser']);
$routes->post('/voucher/claim', 'Pages::voucherClaim', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/voucher/redeem', 'Pages::voucherRedeem', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/voucher/redeem/checkout', 'Pages::voucherRedeemCheckout', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/voucher/add', 'Pages::voucherAddCode', ['filter' => ['harusLogin', 'csrf']]);
$routes->post('/voucher/addmember', 'Pages::voucherAddMember', ['filter' => ['harusAdmin', 'csrf']]);

$routes->get('/transaction', 'Pages::transaction', ['filter' => 'harusUser']);
$routes->get('/retur/order/(:any)', 'Pages::returOrder/$1', ['filter' => 'harusUser']);
$routes->post('/retur/order/(:any)', 'Pages::actionReturOrder/$1', ['filter' => ['harusUser', 'csrf']]);
// $routes->post('/addtransaction', 'Pages::addTransaction');
// $routes->get('/afteraddtransaction/(:any)', 'Pages::afterAddTransaction/$1', ['filter' => 'harusUser']);
$routes->post('/updatetransaction', 'Pages::updateTransaction');
$routes->post('/integrations/luna-return-status', 'Pages::updateReturStatusFromLuna');
$routes->post('/integrations/luna-order-status', 'Pages::updateOrderStatusFromLuna');

// $routes->get('/finish_urlMid/(:any)/(:any)', 'Pages::finishUrlMid/$1/$2');
// $routes->get('/finish_url/(:any)', 'Pages::finishUrl/$1');
// $routes->get('/finish_url/(:any)/(:any)', 'Pages::finishUrl/$1/$2'); //code rahasia = JSM-zWYWObdPEKlHA0PWP6BN
// $routes->get('/successpay', 'Pages::successPay');
// $routes->get('/progresspay', 'Pages::progressPay');
// $routes->get('/errorpay', 'Pages::errorPay');
$routes->get('/order/(:any)', 'Pages::order/$1', ['filter' => 'harusLogin']);
$routes->post('/cancelorder/(:any)', 'Pages::cancelOrder/$1', ['filter' => ['harusLogin', 'csrf']]);
$routes->post('/payorder/(:any)/confirm/(:any)', 'Pages::payOrderConfirm/$1/$2', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/payorder/(:any)', 'Pages::payOrder/$1', ['filter' => ['harusLogin', 'csrf']]);
// $routes->get('/orderlocal', 'Pages::orderLocal');

$routes->get('/invoice/(:any)', 'Pages::invoice/$1', ['filter' => 'harusLogin']);
// $routes->get('/qris/(:any)', 'Pages::qris/$1', ['filter' => 'harusLogin']);
$routes->get('/account', 'Pages::account', ['filter' => 'harusLogin']);
$routes->post('/account', 'Pages::editAccount', ['filter' => ['harusLogin', 'csrf']]);
$routes->post('/account/email/request', 'CustomerEmail::requestChange', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/account/email/confirm', 'CustomerEmail::confirmChange', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/account/username', 'CustomerUsername::save', ['filter' => ['harusUser', 'csrf']]);
$routes->get('/contact', 'Pages::contact');
$routes->get('/about', 'Pages::about');

$routes->get('/point', 'Pages::point', ['filter' => 'harusUser']);
$routes->get('/point/history', 'Pages::pointHistory', ['filter' => 'harusUser']);
$routes->post("/point/use", 'Pages::pointUse', ['filter' => ['harusUser', 'csrf']]);
$routes->post("/point/cancel", 'Pages::pointCancel', ['filter' => ['harusUser', 'csrf']]);

$routes->get('/product/(:any)', 'Pages::product/$1');
$routes->get('/find/(:any)', 'Pages::productFilter/$1');
$routes->get('/find/(:any)/(:any)', 'Pages::productFilter/$1/$2');

$routes->post('/addrating/(:any)', 'Pages::addRating/$1', ['filter' => ['harusUser', 'csrf']]);
$routes->post('/delrating/(:num)', 'Pages::delRating/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/manageratingterjual', 'Pages::manageRatingTerjual', ['filter' => 'harusAdmin']);
$routes->post('/updateterjualadmin/(:any)', 'Pages::updateTerjualAdmin/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/editratingadmin/(:num)', 'Pages::editRatingAdmin/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/generatedummyratingterjual', 'Pages::generateDummyRatingTerjual', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/freshgeneratedummyratingterjual', 'Pages::freshGenerateDummyRatingTerjual', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/hapusdummyrating', 'Pages::hapusDummyRating', ['filter' => ['harusAdmin', 'csrf']]);

$routes->get('/listredeem', 'Pages::listRedeem', ['filter' => 'harusAdmin']);
$routes->post('/redeemcode', 'Pages::addRedeem', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/redeemcode/edit/(:num)', 'Pages::editRedeem/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/redeemcode/delete/(:num)', 'Pages::deleteRedeem/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/listbanner', 'Pages::listBanner', ['filter' => 'harusAdmin']);
$routes->get('/addbanner', 'Pages::addBanner', ['filter' => 'harusAdmin']);
$routes->post('/actionaddbanner', 'Pages::actionAddBanner', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/editbanner/(:num)', 'Pages::editBanner/$1', ['filter' => 'harusAdmin']);
$routes->post('/actioneditbanner/(:num)', 'Pages::actionEditBanner/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/activebanner/(:num)', 'Pages::activeBanner/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/deletebanner/(:num)', 'Pages::deleteBanner/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/trafficadmin', 'Pages::trafficAdmin', ['filter' => 'harusAdmin']);
$routes->get('/trafficadmin/export', 'Pages::trafficAdminExport', ['filter' => 'harusAdmin']);
$routes->get('/trafficadmin/(:num)', 'Pages::trafficAdmin/$1', ['filter' => 'harusAdmin']);
$routes->get('/settingwarna', 'Pages::settingWarna', ['filter' => 'harusAdmin']);
$routes->post('/settingwarna', 'Pages::actionSettingWarna', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/shippingadmin', 'Pages::shippingAdmin', ['filter' => 'harusAdmin']);
$routes->get('/lunamappingadmin', 'LunaMappingAdmin::index', ['filter' => 'harusAdmin']);
$routes->get('/lunamappingadmin/catalogue', 'LunaMappingAdmin::catalogue', ['filter' => 'harusAdmin']);
$routes->post('/lunamappingadmin/save', 'LunaMappingAdmin::save', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/shippingadmin', 'Pages::actionShippingAdmin', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/shippingadmin/coverage/(:num)/deactivate', 'Pages::deactivateShippingCoverage/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/listvoucher', 'Pages::listVoucher', ['filter' => 'harusAdmin']);
$routes->post('/activevoucher/(:any)', 'Pages::activeVoucher/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/addvoucher', 'Pages::addVoucher', ['filter' => 'harusAdmin']);
$routes->post('/actionaddvoucher', 'Pages::actionAddVoucher', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/editvoucher/(:any)', 'Pages::editVoucher/$1', ['filter' => 'harusAdmin']);
$routes->post('/actioneditvoucher/(:any)', 'Pages::actionEditVoucher/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/deletevoucher/(:any)', 'Pages::deleteVoucher/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/actionbroadcastvoucher/(:any)', 'Pages::actionBroadcastVoucher/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/actionbroadcastvoucheremail', 'Pages::actionBroadcastVoucherEmail', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/listform', 'Pages::listForm', ['filter' => 'harusAdmin']);
$routes->get('/listcustomer', 'Pages::listCustomer', ['filter' => 'harusAdmin']);
$routes->get('/customerdirectoryadmin', 'CustomerDirectoryAdmin::index', ['filter' => 'harusAdmin']);
$routes->get('/admin/dashboard', 'AdminDashboard::index', ['filter' => 'harusAdmin']);
$routes->get('/admin/roles', 'AdminRoleAdmin::index', ['filter' => 'harusAdmin']);
$routes->post('/admin/roles/save', 'AdminRoleAdmin::saveRole', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/admin/roles/assign', 'AdminRoleAdmin::assignRole', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/customeradmin', 'CustomerModerationAdmin::index', ['filter' => 'harusAdmin']);
$routes->get('/customersuspensionadmin', 'CustomerSuspensionAdmin::index', ['filter' => 'harusAdmin']);
$routes->get('/notificationtestadmin', 'PushNotificationAdmin::index', ['filter' => 'harusAdmin']);
$routes->get('/androidreferraladmin', 'AndroidReferralAdmin::index', ['filter' => 'harusAdmin']);
$routes->post('/androidreferraladmin', 'AndroidReferralAdmin::save', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/notificationtestadmin/send', 'PushNotificationAdmin::sendTest', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/notificationtestadmin/broadcast', 'PushNotificationAdmin::sendBroadcast', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/customeradmin/domain/add', 'CustomerModerationAdmin::saveDomain', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/customeradmin/domain/remove', 'CustomerModerationAdmin::removeDomain', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/customeradmin/delete-expired-unverified', 'CustomerModerationAdmin::deleteExpiredUnverified', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/customeradmin/account-deletion/complete', 'CustomerModerationAdmin::completeDeletionRequest', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/customersuspensionadmin/suspend', 'CustomerSuspensionAdmin::suspend', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/customersuspensionadmin/reinstate', 'CustomerSuspensionAdmin::reinstate', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/listcustomer/(:any)', 'Pages::listCustomer/$1', ['filter' => 'harusAdmin']);
$routes->get('/listcustomer/(:any)/(:any)', 'Pages::listCustomer/$1/$2', ['filter' => 'harusAdmin']);
$routes->get('/pdf/(:any)', 'Pages::pdf/$1', ['filter' => 'harusAdmin']);
$routes->post('/editresi', 'Pages::editResi', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/syncorder/(:segment)', 'Pages::syncPaidOrderToLuna/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/shippingadmin/subsidy', 'ShippingSubsidyAdmin::save', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/listproduct', 'Pages::listProduct', ['filter' => 'harusAdmin']);
$routes->get('/listproduct/(:any)', 'Pages::listProduct/$1', ['filter' => 'harusAdmin']);
$routes->get('/category-image/(:segment)', 'Pages::categoryImageFile/$1');
$routes->get('/flashsaleadmin', 'Pages::flashSaleAdmin', ['filter' => 'harusAdmin']);
$routes->post('/flashsaleadmin', 'Pages::actionFlashSaleAdmin', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/categoryimagesadmin', 'Pages::categoryImagesAdmin', ['filter' => 'harusAdmin']);
$routes->post('/categoryimagesadmin', 'Pages::actionCategoryImagesAdmin', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/addproduct', 'Pages::addProduct', ['filter' => 'harusAdmin']);
$routes->post('/addproduct', 'Pages::actionAddProduct', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/editproduct/(:any)', 'Pages::editProduct/$1', ['filter' => 'harusAdmin']);
$routes->post('/editproduct/(:any)', 'Pages::actionEditProduct/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/delproduct/(:any)', 'Pages::delProduct/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/listproducttable', 'Pages::listProductTable', ['filter' => 'harusAdmin']);
$routes->post('/activeproduct/(:any)', 'Pages::activeProduct/$1', ['filter' => ['harusAdmin', 'csrf']]);
// $routes->post('/delproduct/(:any)', 'Pages::actionDelProduct/$1', ['filter' => 'harusAdmin']);
$routes->post('/orderdone/(:any)', 'Pages::orderDone/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/findproductadmin', 'Pages::actionFindProductAdmin', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/findproductadmin/(:any)', 'Pages::findProductAdmin/$1', ['filter' => 'harusAdmin']);
$routes->get('/findproductadmin/(:any)/(:any)', 'Pages::findProductAdmin/$1/$2', ['filter' => 'harusAdmin']);

$routes->get('/invoiceadmin', 'Pages::invoiceAdmin', ['filter' => 'harusAdmin']);
$routes->get('/invoiceadmin/(:any)', 'Pages::invoiceAdmin/$1', ['filter' => 'harusAdmin']);
$routes->get('/addinvoiceadmin', 'Pages::addInvoiceAdmin', ['filter' => 'harusAdmin']);
$routes->post('/actionaddinvoiceadmin', 'Pages::activeAddInvoiceAdmin', ['filter' => ['harusAdmin', 'csrf']]);

$routes->get('/stokadmin', 'Pages::stokAdmin', ['filter' => 'harusAdmin']);
$routes->get('/stokadmin/(:any)/(:any)', 'Pages::stokAdmin/$1/$2', ['filter' => 'harusAdmin']);
$routes->post('/stokadmin/(:any)', 'Pages::addStokAdmin/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/stokadminacc/(:any)/(:any)', 'Pages::accStokAdmin/$1/$2', ['filter' => ['harusAdmin', 'csrf']]);
// $routes->get('/apicomp', 'ApiCompany::index', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/getallbarang/(:any)', 'ApiCompany::getAllBarang/$1', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/barang/(:any)', 'ApiCompany::barang/$1', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/kategori/(:any)/(:any)', 'ApiCompany::kategori/$1/$2', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/subkategori/(:any)/(:any)', 'ApiCompany::subkategori/$1/$2', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/cari/(:any)/(:any)', 'ApiCompany::cari/$1/$2', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/gambar/(:any)', 'ApiCompany::gambar/$1', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/getgambarbarang/(:any)', 'ApiCompany::getGambarBarang/$1', ['filter' => 'corsFilter']);
// $routes->get('/apicomp/getgambar/(:any)/(:any)', 'ApiCompany::getGambar/$1/$2', ['filter' => 'corsFilter']);
// Legacy one-off data migrations and automation endpoints are intentionally not
// exposed over HTTP. Run approved maintenance through authenticated CLI commands.
$routes->get('/benerinstokluna', 'Pages::benerinStokLuna', ['filter' => 'harusAdmin']);

// GambarController
$routes->get('/imgshare/(:any)', 'GambarController::tampilGambarShare/$1');
$routes->get('/imgpic/(:any)', 'GambarController::tampilGambarBarang/$1');
$routes->get('/imgproduct/(:segment)/(:num)', 'GambarController::tampilFotoProduk/$1/$2');
$routes->get('/imgart/(:any)', 'GambarController::tampilGambarArtikel/$1');
$routes->get('/imgart/(:any)/(:any)', 'GambarController::tampilGambarArtikel/$1/$2');
$routes->get('/imgvoucher/email/(:any)', 'GambarController::voucherPosterEmail/$1');
$routes->get('/imgvoucher/(:any)', 'GambarController::voucherPoster/$1');
$routes->get('/imgbanner/(:num)', 'GambarController::tampilGambarBanner/$1');
$routes->get('/imguser/(:any)', 'GambarController::tampilGambarUser/$1');
$routes->get('/imgbuktibayar/(:any)', 'GambarController::tampilGambarBuktiBayar/$1');

//Artikel
$routes->get('/article', 'Pages::article');
$routes->post('/actionsearcharticle', 'Pages::actionSearchArticle');
$routes->get('/article/find/(:any)', 'Pages::findArticle/$1');
$routes->get('/article/category/(:any)', 'Pages::articleCategory/$1');
$routes->get('/article/(:any)', 'Pages::article/$1');
$routes->post('/addlikearticle/(:any)', 'Pages::addLikeArticle/$1', ['filter' => 'csrf']);
$routes->post('/addsharearticle/(:any)', 'Pages::addShareArticle/$1', ['filter' => 'csrf']);
$routes->get('/addarticle', 'Pages::addArticle', ['filter' => 'harusAdmin']);
$routes->post('/addarticle', 'Pages::actionAddArticle', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/delarticle/(:any)', 'Pages::actionDeleteArticle/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->get('/editarticle/(:any)', 'Pages::editArticle/$1', ['filter' => 'harusAdmin']);
$routes->post('/editarticle/(:any)', 'Pages::actionEditArticle/$1', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/submitemail/(:any)', 'Pages::submitEmail/$1', ['filter' => 'csrf']);
$routes->post('/addkomen/(:any)', 'Pages::addKomen/$1', ['filter' => 'csrf']);
$routes->post('/delkomen/(:any)/(:any)', 'Pages::delKomen/$1/$2', ['filter' => ['harusAdmin', 'csrf']]);
$routes->post('/editkomen/(:any)/(:any)', 'Pages::editKomen/$1/$2', ['filter' => ['harusAdmin', 'csrf']]);

// TrackingController
$routes->post('/addtracking', 'TrackingController::addTracking', ['filter' => 'corsFilter']);
// $routes->post('/closegreeting', 'TrackingController::closeGreeting', ['filter' => 'corsFilter']);
// $routes->post('/trackpop', 'TrackingController::trackPop', ['filter' => 'corsFilter']);

//API SHEET
$routes->post('/sheet/updatestok', 'ApiSheet::updateStok', ['filter' => 'corsFilter']);
$routes->post('/integrations/luna-product-sync', 'ProductSyncController::syncFromLuna', ['filter' => 'corsFilter']);

$routes->get('/cekdouble', 'Pages::cekDouble');

// The legacy /ro address-data importer has been retired; it performed bulk
// writes and used third-party credentials from a browser-accessible tool.


$routes->get('(:any)', 'Pages::notFound');
