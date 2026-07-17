<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'HomeController::index');
$routes->get('/produk', 'HomeController::products');
$routes->get('/produk/(:num)', 'HomeController::detail/$1');

$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::processLogin');

$routes->get('/register', 'AuthController::register');
$routes->post('/register', 'AuthController::processRegister');

$routes->get('/logout', 'AuthController::logout');

$routes->get('/dashboard', 'DashboardController::customer');
$routes->get('/admin/dashboard', 'DashboardController::admin');

$routes->post('/cart/add', 'CartController::add');
$routes->get('/cart', 'CartController::index');
$routes->post('/cart/update', 'CartController::update');
$routes->get('/cart/remove/(:num)', 'CartController::remove/$1');
$routes->get('/cart/clear', 'CartController::clear');

$routes->get('/checkout', 'CheckoutController::index');
$routes->post('/checkout/process', 'CheckoutController::process');
$routes->get('/checkout/success/(:num)', 'CheckoutController::success/$1');

// Admin transaksi
$routes->get('/admin/transactions', 'AdminTransactionController::index');
$routes->get('/admin/transactions/detail/(:num)', 'AdminTransactionController::detail/$1');
$routes->post('/admin/transactions/update-status/(:num)', 'AdminTransactionController::updateStatus/$1');

// Admin master data
$routes->get('/admin/categories', 'AdminCategoryController::index');
$routes->get('/admin/categories/create', 'AdminCategoryController::create');
$routes->post('/admin/categories/store', 'AdminCategoryController::store');
$routes->get('/admin/categories/edit/(:num)', 'AdminCategoryController::edit/$1');
$routes->post('/admin/categories/update/(:num)', 'AdminCategoryController::update/$1');
$routes->post('/admin/categories/delete/(:num)', 'AdminCategoryController::delete/$1');

$routes->get('/admin/products', 'AdminProductController::index');
$routes->get('/admin/products/create', 'AdminProductController::create');
$routes->post('/admin/products/store', 'AdminProductController::store');
$routes->get('/admin/products/edit/(:num)', 'AdminProductController::edit/$1');
$routes->post('/admin/products/update/(:num)', 'AdminProductController::update/$1');
$routes->post('/admin/products/toggle-featured/(:num)', 'AdminProductController::toggleFeatured/$1');
$routes->post('/admin/products/delete/(:num)', 'AdminProductController::delete/$1');

// Export
$routes->get('/admin/reports', 'AdminReportController::index');
$routes->get('/admin/reports/export-excel', 'AdminReportController::exportExcel');
$routes->get('/admin/reports/export-pdf', 'AdminReportController::exportPdf');

// Customer transaksi
$routes->get('/transactions', 'CustomerTransactionController::index');
$routes->get('/transactions/detail/(:num)', 'CustomerTransactionController::detail/$1');

// Midtrans Payment
$routes->get('/payment/pay/(:num)', 'PaymentController::pay/$1');
$routes->post('/payment/notification', 'PaymentController::notification');
$routes->get('/payment/finish', 'PaymentController::finish');
$routes->get('/payment/unfinish', 'PaymentController::unfinish');
$routes->get('/payment/error', 'PaymentController::error');
