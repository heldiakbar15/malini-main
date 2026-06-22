<?php

namespace App\Controllers;

use App\Models\CategoryModel;

class AdminCategoryController extends BaseController
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

        $categoryModel = new CategoryModel();

        return view('admin/categories/index', [
            'categories' => $categoryModel->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        return view('admin/categories/form', [
            'category' => null,
            'action'   => '/admin/categories/store',
            'title'    => 'Tambah Kategori',
        ]);
    }

    public function store()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama kategori wajib diisi.');
        }

        $categoryModel = new CategoryModel();
        $categoryModel->insert(['name' => $name]);

        return redirect()->to('/admin/categories')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $categoryModel = new CategoryModel();
        $category = $categoryModel->find($id);

        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan');
        }

        return view('admin/categories/form', [
            'category' => $category,
            'action'   => '/admin/categories/update/' . $id,
            'title'    => 'Edit Kategori',
        ]);
    }

    public function update($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $name = trim((string) $this->request->getPost('name'));

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama kategori wajib diisi.');
        }

        $categoryModel = new CategoryModel();

        if (!$categoryModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan');
        }

        $categoryModel->update($id, ['name' => $name]);

        return redirect()->to('/admin/categories')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $categoryModel = new CategoryModel();

        if (!$categoryModel->find($id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Kategori tidak ditemukan');
        }

        $categoryModel->delete($id);

        return redirect()->to('/admin/categories')->with('success', 'Kategori berhasil dihapus.');
    }
}
