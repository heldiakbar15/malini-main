<?php

namespace App\Controllers;

use App\Models\ProductVariantModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'customer') {
            return redirect()->to('/admin/dashboard')->with('error', 'Checkout hanya dapat dilakukan oleh customer.');
        }

        $cart = session()->get('cart') ?? [];

        if (empty($cart)) {
            return redirect()->to('/cart')->with('error', 'Keranjang masih kosong.');
        }

        return view('checkout/index', [
            'cart' => $cart
        ]);
    }

    public function process()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'customer') {
            return redirect()->to('/admin/dashboard')->with('error', 'Checkout hanya dapat dilakukan oleh customer.');
        }

        $cart = session()->get('cart') ?? [];

        if (empty($cart)) {
            return redirect()->to('/cart')->with('error', 'Keranjang masih kosong.');
        }

        $customerName    = trim((string) $this->request->getPost('customer_name'));
        $customerPhone   = trim((string) $this->request->getPost('customer_phone'));
        $shippingAddress = trim((string) $this->request->getPost('shipping_address'));
        $paymentMethod   = 'Midtrans';

        if (!$customerName || !$shippingAddress) {
            return redirect()->back()->with('error', 'Nama dan alamat pengiriman wajib diisi.');
        }

        $db = \Config\Database::connect();
        $variantModel = new ProductVariantModel();
        $transactionModel = new TransactionModel();
        $detailModel = new TransactionDetailModel();

        $totalAmount = 0;

        foreach ($cart as $item) {
            $variant = $variantModel->find($item['variant_id']);

            if (!$variant) {
                return redirect()->to('/cart')->with('error', 'Salah satu varian produk tidak ditemukan.');
            }

            if ((int) $variant['stock'] < (int) $item['quantity']) {
                return redirect()->to('/cart')->with('error', 'Stok produk ' . $item['name'] . ' ukuran ' . $item['size'] . ' tidak mencukupi.');
            }

            $totalAmount += (int) $item['subtotal'];
        }

        $invoiceNumber = 'INV-' . date('YmdHis') . '-' . session()->get('user_id');
        $midtransOrderId = $invoiceNumber;

        $db->transStart();

        $transactionId = $transactionModel->insert([
            'user_id'             => session()->get('user_id'),
            'invoice_number'      => $invoiceNumber,
            'customer_name'       => $customerName,
            'customer_phone'      => $customerPhone,
            'shipping_address'    => $shippingAddress,
            'total_amount'        => $totalAmount,
            'payment_method'      => $paymentMethod,
            'payment_status'      => 'pending',
            'order_status'        => 'pending',
            'midtrans_order_id'   => $midtransOrderId,
        ]);

        foreach ($cart as $item) {
            $detailModel->insert([
                'transaction_id'      => $transactionId,
                'product_id'          => $item['product_id'],
                'product_variant_id'  => $item['variant_id'],
                'product_name'        => $item['name'],
                'variant_size'        => $item['size'],
                'price'               => $item['price'],
                'quantity'            => $item['quantity'],
                'subtotal'            => $item['subtotal'],
            ]);

            $variant = $variantModel->find($item['variant_id']);

            $variantModel->update($item['variant_id'], [
                'stock' => (int) $variant['stock'] - (int) $item['quantity']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/cart')->with('error', 'Checkout gagal diproses.');
        }

        try {
            Config::$serverKey    = getenv('MIDTRANS_SERVER_KEY');
            Config::$isProduction = getenv('MIDTRANS_IS_PRODUCTION') === 'true';
            Config::$isSanitized  = true;
            Config::$is3ds        = true;

            $itemDetails = [];

            foreach ($cart as $item) {
                $itemDetails[] = [
                    'id'       => (string) $item['variant_id'],
                    'price'    => (int) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'name'     => substr($item['name'] . ' - ' . $item['size'], 0, 50),
                ];
            }

            $params = [
                'transaction_details' => [
                    'order_id'     => $midtransOrderId,
                    'gross_amount' => (int) $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $customerName,
                    'email'      => session()->get('email'),
                    'phone'      => $customerPhone,
                    'shipping_address' => [
                        'first_name' => $customerName,
                        'phone'      => $customerPhone,
                        'address'    => $shippingAddress,
                    ],
                ],
                'item_details' => $itemDetails,
                'callbacks' => [
                    'finish'   => base_url('/payment/finish'),
                    'unfinish' => base_url('/payment/unfinish'),
                    'error'    => base_url('/payment/error'),
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $transactionModel->update($transactionId, [
                'snap_token' => $snapToken,
            ]);

            session()->remove('cart');

            return redirect()->to('/payment/pay/' . $transactionId);

        } catch (\Throwable $e) {
            return redirect()->to('/transactions/detail/' . $transactionId)
                ->with('error', 'Transaksi berhasil dibuat, tetapi gagal membuat token pembayaran Midtrans: ' . $e->getMessage());
        }
    }

    public function success($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $transactionModel = new TransactionModel();
        $transaction = $transactionModel->find($id);

        if (!$transaction) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Transaksi tidak ditemukan');
        }

        return view('checkout/success', [
            'transaction' => $transaction
        ]);
    }
}