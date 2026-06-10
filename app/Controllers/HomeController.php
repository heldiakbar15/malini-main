<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\ProductVariantModel;

class HomeController extends BaseController
{
    public function index()
    {
        $productModel = new ProductModel();

        $products = $productModel
            ->select('products.*, MIN(product_variants.price) as min_price')
            ->join('product_variants', 'product_variants.product_id = products.id', 'left')
            ->groupBy('products.id')
            ->findAll();

        return view('home/index', [
            'products' => $products
        ]);
    }

    public function detail($id)
    {
        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();

        $product = $productModel->find($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        $variants = $variantModel
            ->where('product_id', $id)
            ->orderBy('price', 'ASC')
            ->findAll();

        return view('home/detail', [
            'product'  => $product,
            'variants' => $variants
        ]);
    }
}