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
        $search = trim((string) $this->request->getGet('q'));
        $categoryId = trim((string) $this->request->getGet('category_id'));

        $productModel
            ->select('products.*, categories.name as category_name, COUNT(product_variants.id) as variant_count, COALESCE(SUM(product_variants.stock), 0) as total_stock, MIN(product_variants.price) as min_price')
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
            ->orderBy('products.is_featured', 'DESC')
            ->orderBy('products.id', 'DESC')
            ->paginate(8, 'admin_products');

        return view('admin/products/index', [
            'products'   => $products,
            'pager'      => $productModel->pager,
            'categories' => $this->categories(),
            'search'     => $search,
            'categoryId' => $categoryId,
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

        try {
            $imageName = $this->storeUploadedProductImage();
        } catch (\RuntimeException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $productId = $productModel->insert($this->productData($imageName));
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

        $product = $productModel->find($id);

        if (!$product) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Produk tidak ditemukan');
        }

        $validationError = $this->validateProductInput();

        if ($validationError !== null) {
            return redirect()->back()->withInput()->with('error', $validationError);
        }

        $variantModel = new ProductVariantModel();
        try {
            $imageName = $this->storeUploadedProductImage();
        } catch (\RuntimeException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $productModel->update($id, $this->productData($imageName, $product));
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

    public function toggleFeatured($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $productModel = new ProductModel();
        $product = $productModel->find($id);

        if (!$product) {
            return redirect()->to('/admin/products')->with('error', 'Produk tidak ditemukan.');
        }

        $isFeatured = (int) ($product['is_featured'] ?? 0) === 1;

        if (!$isFeatured) {
            $featuredCount = $productModel->where('is_featured', 1)->countAllResults();

            if ($featuredCount >= 4) {
                return redirect()->back()->with('error', 'Maksimal 4 produk unggulan untuk ditampilkan di beranda.');
            }
        }

        $productModel->update($id, [
            'is_featured' => $isFeatured ? 0 : 1,
        ]);

        return redirect()->back()->with('success', $isFeatured
            ? 'Produk dihapus dari Produk Unggulan.'
            : 'Produk ditambahkan ke Produk Unggulan.');
    }

    private function categories(): array
    {
        $categoryModel = new CategoryModel();

        return $categoryModel->orderBy('name', 'ASC')->findAll();
    }

    private function productData(?string $imageName = null, ?array $existingProduct = null): array
    {
        $categoryId = $this->request->getPost('category_id');

        return [
            'category_id' => $categoryId !== '' ? $categoryId : null,
            'name'        => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
            'image'       => $imageName ?? ($existingProduct['image'] ?? null),
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

    private function storeUploadedProductImage(): ?string
    {
        $image = $this->request->getFile('image_file');

        if (!$image || $image->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (!$image->isValid()) {
            throw new \RuntimeException('Upload gambar gagal. Silakan pilih file gambar yang valid.');
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower($image->getClientExtension());

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \RuntimeException('Format gambar harus JPG, JPEG, PNG, atau WEBP.');
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($image->getMimeType(), $allowedMimeTypes, true)) {
            throw new \RuntimeException('File yang diupload harus berupa gambar JPG, PNG, atau WEBP.');
        }

        if ($image->getSize() > (2 * 1024 * 1024)) {
            throw new \RuntimeException('Ukuran gambar maksimal 2 MB.');
        }

        $uploadPath = rtrim(FCPATH, '\\/') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $fileName = $image->getRandomName();
        $image->move($uploadPath, $fileName);

        return $fileName;
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
