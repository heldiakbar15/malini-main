<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class CustomerTransactionController extends BaseController
{
    private function checkCustomer()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'customer') {
            return redirect()->to('/admin/dashboard')->with('error', 'Akses hanya untuk customer.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->checkCustomer()) {
            return $redirect;
        }

        $transactionModel = new TransactionModel();

        $transactions = $transactionModel
            ->where('user_id', session()->get('user_id'))
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('customer/transactions/index', [
            'transactions' => $transactions
        ]);
    }

    public function detail($id)
    {
        if ($redirect = $this->checkCustomer()) {
            return $redirect;
        }

        $transactionModel = new TransactionModel();
        $detailModel = new TransactionDetailModel();

        $transaction = $transactionModel
            ->where('id', $id)
            ->where('user_id', session()->get('user_id'))
            ->first();

        if (!$transaction) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Transaksi tidak ditemukan');
        }

        $details = $detailModel
            ->where('transaction_id', $id)
            ->findAll();

        return view('customer/transactions/detail', [
            'transaction' => $transaction,
            'details'     => $details
        ]);
    }
}