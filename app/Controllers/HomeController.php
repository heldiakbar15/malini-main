<?php

namespace App\Controllers;

use App\Models\CategoryModel;
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
            ->where('products.is_featured', 1)
            ->groupBy('products.id')
            ->orderBy('products.is_featured', 'DESC')
            ->orderBy('products.id', 'DESC')
            ->findAll(4);

        if (empty($products)) {
            $products = $productModel
                ->select('products.*, MIN(product_variants.price) as min_price')
                ->join('product_variants', 'product_variants.product_id = products.id', 'left')
                ->groupBy('products.id')
                ->orderBy('products.id', 'DESC')
                ->findAll(4);
        }

        return view('home/index', [
            'products' => $products
        ]);
    }

    public function products()
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel();

        $search = trim((string) $this->request->getGet('q'));
        $categoryId = trim((string) $this->request->getGet('category_id'));

        $productModel
            ->select('products.*, categories.name as category_name, MIN(product_variants.price) as min_price')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->join('product_variants', 'product_variants.product_id = products.id', 'left');

        if ($search !== '') {
            $productModel->groupStart()
                ->like('products.name', $search)
                ->orLike('products.description', $search)
                ->groupEnd();
        }

        if ($categoryId !== '') {
            $productModel->where('products.category_id', $categoryId);
        }

        $products = $productModel
            ->groupBy('products.id')
            ->orderBy('products.id', 'DESC')
            ->paginate(8, 'products');

        return view('home/products', [
            'products'   => $products,
            'pager'      => $productModel->pager,
            'categories' => $categoryModel->orderBy('name', 'ASC')->findAll(),
            'search'     => $search,
            'categoryId' => $categoryId,
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
