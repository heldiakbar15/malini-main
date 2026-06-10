<?php

namespace App\Controllers;

use App\Models\ProductVariantModel;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

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

        $customerName    = $this->request->getPost('customer_name');
        $customerPhone   = $this->request->getPost('customer_phone');
        $shippingAddress = $this->request->getPost('shipping_address');
        $paymentMethod   = $this->request->getPost('payment_method');

        if (!$customerName || !$shippingAddress || !$paymentMethod) {
            return redirect()->back()->with('error', 'Nama, alamat, dan metode pembayaran wajib diisi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $variantModel = new ProductVariantModel();

        $totalAmount = 0;

        foreach ($cart as $item) {
            $variant = $variantModel->find($item['variant_id']);

            if (!$variant) {
                $db->transRollback();
                return redirect()->to('/cart')->with('error', 'Salah satu varian produk tidak ditemukan.');
            }

            if ($variant['stock'] < $item['quantity']) {
                $db->transRollback();
                return redirect()->to('/cart')->with('error', 'Stok produk ' . $item['name'] . ' ukuran ' . $item['size'] . ' tidak mencukupi.');
            }

            $totalAmount += $item['subtotal'];
        }

        $transactionModel = new TransactionModel();

        $invoiceNumber = 'INV-' . date('YmdHis') . '-' . session()->get('user_id');

        $transactionId = $transactionModel->insert([
            'user_id'          => session()->get('user_id'),
            'invoice_number'   => $invoiceNumber,
            'customer_name'    => $customerName,
            'customer_phone'   => $customerPhone,
            'shipping_address' => $shippingAddress,
            'total_amount'     => $totalAmount,
            'payment_method'   => $paymentMethod,
            'payment_status'   => 'pending',
            'order_status'     => 'pending',
        ]);

        $detailModel = new TransactionDetailModel();

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
                'stock' => $variant['stock'] - $item['quantity']
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/cart')->with('error', 'Checkout gagal diproses.');
        }

        session()->remove('cart');

        return redirect()->to('/checkout/success/' . $transactionId);
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