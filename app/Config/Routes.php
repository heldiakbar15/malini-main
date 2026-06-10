<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'HomeController::index');
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

// Customer transaksi
$routes->get('/transactions', 'CustomerTransactionController::index');
$routes->get('/transactions/detail/(:num)', 'CustomerTransactionController::detail/$1');