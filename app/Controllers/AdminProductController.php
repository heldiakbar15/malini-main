<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;

class AdminProductController extends BaseController
{
    private function checkAdmin()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Akses hanya untuk admin.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $productModel = new ProductModel();

        $products = $productModel
            ->select('products.*, categories.name as category_name, COUNT(product_variants.id) as variant_count, COALESCE(SUM(product_variants.stock), 0) as total_stock, MIN(product_variants.price) as min_price')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->join('product_variants', 'product_variants.product_id = products.id', 'left')
            ->groupBy('products.id')
            ->orderBy('products.id', 'DESC')
            ->findAll();

        return view('admin/products/index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        return view('admin/products/form', [
            'product'    => null,
            'variants'   => [],
            'categories' => $this->categories(),
            'action'     => '/admin/products/store',
            'title'      => 'Tambah Produk',
        ]);
    }

    public function store()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $validationError = $this->validateProductInput();

        if ($validationError !== null) {
            return redirect()->back()->withInput()->with('error', $validationError);
        }

        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $productId = $productModel->insert($this->productData());
        $this->saveVariants($variantModel, (int) $productId);

        $db->transComplete();

        return redirect()->to('/admin/products')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();
        $product = $productModel->find($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        return view('admin/products/form', [
            'product'    => $product,
            'variants'   => $variantModel->where('product_id', $id)->orderBy('id', 'ASC')->findAll(),
            'categories' => $this->categories(),
            'action'     => '/admin/products/update/' . $id,
            'title'      => 'Edit Produk',
        ]);
    }

    public function update($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $productModel = new ProductModel();

        if (!$productModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        $validationError = $this->validateProductInput();

        if ($validationError !== null) {
            return redirect()->back()->withInput()->with('error', $validationError);
        }

        $variantModel = new ProductVariantModel();
        $db = \Config\Database::connect();
        $db->transStart();

        $productModel->update($id, $this->productData());
        $variantModel->where('product_id', $id)->delete();
        $this->saveVariants($variantModel, (int) $id);

        $db->transComplete();

        return redirect()->to('/admin/products')->with('success', 'Produk berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $productModel = new ProductModel();

        if (!$productModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        $productModel->delete($id);

        return redirect()->to('/admin/products')->with('success', 'Produk berhasil dihapus.');
    }

    private function categories(): array
    {
        $categoryModel = new CategoryModel();

        return $categoryModel->orderBy('name', 'ASC')->findAll();
    }

    private function productData(): array
    {
        $categoryId = $this->request->getPost('category_id');

        return [
            'category_id' => $categoryId !== '' ? $categoryId : null,
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
            'image'       => trim((string) $this->request->getPost('image')) ?: null,
        ];
    }

    private function validateProductInput(): ?string
    {
        if (trim((string) $this->request->getPost('name')) === '') {
            return 'Nama produk wajib diisi.';
        }

        $sizes = (array) $this->request->getPost('variant_size');
        $hasVariant = false;

        foreach ($sizes as $size) {
            if (trim((string) $size) !== '') {
                $hasVariant = true;
                break;
            }
        }

        if (!$hasVariant) {
            return 'Minimal satu varian produk wajib diisi.';
        }

        return null;
    }

    private function saveVariants(ProductVariantModel $variantModel, int $productId): void
    {
        $sizes = (array) $this->request->getPost('variant_size');
        $prices = (array) $this->request->getPost('variant_price');
        $stocks = (array) $this->request->getPost('variant_stock');

        foreach ($sizes as $index => $size) {
            $size = trim((string) $size);

            if ($size === '') {
                continue;
            }

            $variantModel->insert([
                'product_id' => $productId,
                'size'       => $size,
                'price'      => max(0, (float) ($prices[$index] ?? 0)),
                'stock'      => max(0, (int) ($stocks[$index] ?? 0)),
            ]);
        }
    }
}
