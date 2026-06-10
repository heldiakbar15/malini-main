<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductVariantModel;

class CartController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $cart = session()->get('cart') ?? [];

        return view('cart/index', [
            'cart' => $cart
        ]);
    }

    public function add()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $productId = $this->request->getPost('product_id');
        $variantId = $this->request->getPost('variant_id');
        $quantity  = (int) $this->request->getPost('quantity');

        if ($quantity < 1) {
            $quantity = 1;
        }

        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();

        $product = $productModel->find($productId);
        $variant = $variantModel->find($variantId);

        if (!$product || !$variant) {
            return redirect()->back()->with('error', 'Produk atau varian tidak ditemukan.');
        }

        if ($variant['stock'] < $quantity) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi.');
        }

        $cart = session()->get('cart') ?? [];
        $cartKey = $variantId;

        if (isset($cart[$cartKey])) {
            $newQuantity = $cart[$cartKey]['quantity'] + $quantity;

            if ($variant['stock'] < $newQuantity) {
                return redirect()->back()->with('error', 'Jumlah melebihi stok tersedia.');
            }

            $cart[$cartKey]['quantity'] = $newQuantity;
            $cart[$cartKey]['subtotal'] = $newQuantity * $variant['price'];
        } else {
            $cart[$cartKey] = [
                'product_id' => $product['id'],
                'variant_id' => $variant['id'],
                'name'       => $product['name'],
                'size'       => $variant['size'],
                'price'      => $variant['price'],
                'stock'      => $variant['stock'],
                'quantity'   => $quantity,
                'subtotal'   => $quantity * $variant['price'],
            ];
        }

        session()->set('cart', $cart);

        return redirect()->to('/cart')->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $variantId = $this->request->getPost('variant_id');
        $quantity  = (int) $this->request->getPost('quantity');

        if ($quantity < 1) {
            return redirect()->to('/cart')->with('error', 'Jumlah produk minimal 1.');
        }

        $cart = session()->get('cart') ?? [];

        if (!isset($cart[$variantId])) {
            return redirect()->to('/cart')->with('error', 'Produk tidak ditemukan di keranjang.');
        }

        $variantModel = new ProductVariantModel();
        $variant = $variantModel->find($variantId);

        if (!$variant) {
            return redirect()->to('/cart')->with('error', 'Varian produk tidak ditemukan.');
        }

        if ($quantity > $variant['stock']) {
            return redirect()->to('/cart')->with('error', 'Jumlah melebihi stok tersedia.');
        }

        $cart[$variantId]['quantity'] = $quantity;
        $cart[$variantId]['stock']    = $variant['stock'];
        $cart[$variantId]['subtotal'] = $quantity * $cart[$variantId]['price'];

        session()->set('cart', $cart);

        return redirect()->to('/cart')->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    public function remove($variantId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $cart = session()->get('cart') ?? [];

        if (isset($cart[$variantId])) {
            unset($cart[$variantId]);
            session()->set('cart', $cart);
        }

        return redirect()->to('/cart')->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    public function clear()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        session()->remove('cart');

        return redirect()->to('/cart')->with('success', 'Keranjang berhasil dikosongkan.');
    }
}