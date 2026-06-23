<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class AdminTransactionController extends BaseController
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

        $transactionModel = new TransactionModel();

        $transactions = $transactionModel
            ->select('transactions.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = transactions.user_id')
            ->orderBy('transactions.id', 'DESC')
            ->findAll();

        return view('admin/transactions/index', [
            'transactions' => $transactions
        ]);
    }

    public function detail($id)
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $transactionModel = new TransactionModel();
        $detailModel = new TransactionDetailModel();

        $transaction = $transactionModel
            ->select('transactions.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = transactions.user_id')
            ->where('transactions.id', $id)
            ->first();

        if (!$transaction) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Transaksi tidak ditemukan');
        }

        $details = $detailModel
            ->where('transaction_id', $id)
            ->findAll();

        return view('admin/transactions/detail', [
            'transaction' => $transaction,
            'details'     => $details
        ]);
    }

    public function updateStatus($id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses.');
        }

        $transactionModel = new \App\Models\TransactionModel();

        $transaction = $transactionModel->find($id);

        if (!$transaction) {
            return redirect()->to('/admin/transactions')->with('error', 'Transaksi tidak ditemukan.');
        }

        $orderStatus = $this->request->getPost('order_status');

        if (!$orderStatus) {
            return redirect()->back()->with('error', 'Status pesanan wajib dipilih.');
        }

        $transactionModel->update($id, [
            'order_status' => $orderStatus,
        ]);

        return redirect()->to('/admin/transactions/detail/' . $id)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }
}