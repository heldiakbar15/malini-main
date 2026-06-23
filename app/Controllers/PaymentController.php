<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use Midtrans\Config;

class PaymentController extends BaseController
{
    protected TransactionModel $transactionModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();

        Config::$serverKey    = getenv('MIDTRANS_SERVER_KEY');
        Config::$isProduction = getenv('MIDTRANS_IS_PRODUCTION') === 'true';
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function pay($transactionId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $transaction = $this->transactionModel->find($transactionId);

        if (!$transaction) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Transaksi tidak ditemukan');
        }

        if ((int) $transaction['user_id'] !== (int) session()->get('user_id')) {
            return redirect()->to('/transactions')->with('error', 'Anda tidak memiliki akses ke transaksi ini.');
        }

        if (($transaction['payment_status'] ?? '') === 'paid') {
            return redirect()->to('/transactions/detail/' . $transactionId)
                ->with('success', 'Transaksi ini sudah dibayar.');
        }

        if (($transaction['payment_status'] ?? '') === 'failed') {
            return redirect()->to('/transactions/detail/' . $transactionId)
                ->with('error', 'Waktu pembayaran sudah habis. Silakan buat pesanan baru.');
        }

        if (empty($transaction['snap_token'])) {
            return redirect()->to('/transactions/detail/' . $transactionId)
                ->with('error', 'Token pembayaran belum tersedia.');
        }

        return view('payment/pay', [
            'transaction'  => $transaction,
            'clientKey'    => getenv('MIDTRANS_CLIENT_KEY'),
            'isProduction' => getenv('MIDTRANS_IS_PRODUCTION') === 'true',
        ]);
    }

    public function notification()
    {
        $json = $this->request->getBody();
        $notification = json_decode($json, true);

        if (!$notification) {
            return $this->response->setJSON([
                'status'  => 'ignored',
                'message' => 'Payload kosong atau tidak valid'
            ])->setStatusCode(200);
        }

        $orderId           = $notification['order_id'] ?? null;
        $transactionStatus = $notification['transaction_status'] ?? null;
        $fraudStatus       = $notification['fraud_status'] ?? null;
        $midtransTransId   = $notification['transaction_id'] ?? null;
        $paymentType       = $notification['payment_type'] ?? null;
        $settlementTime    = $notification['settlement_time'] ?? null;

        if (!$orderId) {
            return $this->response->setJSON([
                'status'  => 'ignored',
                'message' => 'Order ID tidak ditemukan pada payload'
            ])->setStatusCode(200);
        }

        $transaction = $this->transactionModel
            ->where('midtrans_order_id', $orderId)
            ->first();

        if (!$transaction) {
            return $this->response->setJSON([
                'status'   => 'ignored',
                'message'  => 'Transaksi tidak ditemukan. Kemungkinan ini test notification dari Midtrans.',
                'order_id' => $orderId
            ])->setStatusCode(200);
        }

        $bank = null;
        $vaNumber = null;

        if (isset($notification['va_numbers'][0])) {
            $bank = $notification['va_numbers'][0]['bank'] ?? null;
            $vaNumber = $notification['va_numbers'][0]['va_number'] ?? null;
        }

        if (!$bank && isset($notification['permata_va_number'])) {
            $bank = 'permata';
            $vaNumber = $notification['permata_va_number'];
        }

        $paymentStatus = 'pending';
        $orderStatus   = 'pending';

        if ($transactionStatus === 'settlement') {
            $paymentStatus = 'paid';
            $orderStatus   = 'processing';
        } elseif ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $paymentStatus = 'paid';
                $orderStatus   = 'processing';
            }
        } elseif ($transactionStatus === 'pending') {
            $paymentStatus = 'pending';
            $orderStatus   = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
            $paymentStatus = 'failed';
            $orderStatus   = 'cancelled';
        }

        $displayPaymentMethod = 'Midtrans';

        if ($paymentType === 'bank_transfer' && $bank) {
            $displayPaymentMethod = 'VA ' . strtoupper($bank);
        } elseif ($paymentType === 'bank_transfer') {
            $displayPaymentMethod = 'Bank Transfer';
        } elseif ($paymentType === 'gopay') {
            $displayPaymentMethod = 'GoPay';
        } elseif ($paymentType === 'qris') {
            $displayPaymentMethod = 'QRIS';
        } elseif ($paymentType === 'shopeepay') {
            $displayPaymentMethod = 'ShopeePay';
        } elseif ($paymentType === 'credit_card') {
            $displayPaymentMethod = 'Kartu Kredit';
        } elseif (!empty($paymentType)) {
            $displayPaymentMethod = ucwords(str_replace('_', ' ', $paymentType));
        }

        $updateData = [
            'payment_status'              => $paymentStatus,
            'order_status'                => $orderStatus,
            'payment_method'              => $displayPaymentMethod,
            'midtrans_transaction_id'     => $midtransTransId,
            'midtrans_payment_type'       => $paymentType,
            'midtrans_bank'               => $bank,
            'midtrans_va_number'          => $vaNumber,
            'midtrans_transaction_status' => $transactionStatus,
            'midtrans_fraud_status'       => $fraudStatus,
        ];

        if ($paymentStatus === 'paid') {
            $updateData['paid_at'] = $settlementTime ?: date('Y-m-d H:i:s');
        }

        $this->transactionModel->update($transaction['id'], $updateData);

        return $this->response->setJSON([
            'status'         => 'success',
            'message'        => 'Status pembayaran berhasil diperbarui',
            'order_id'       => $orderId,
            'payment_status' => $paymentStatus,
            'order_status'   => $orderStatus,
        ])->setStatusCode(200);
    }

    public function finish()
    {
        return redirect()->to('/transactions')
            ->with('success', 'Pembayaran selesai diproses. Status pembayaran akan diperbarui otomatis oleh Midtrans.');
    }

    public function unfinish()
    {
        return redirect()->to('/transactions')
            ->with('error', 'Pembayaran belum selesai. Anda masih dapat melanjutkan pembayaran dari detail transaksi.');
    }

    public function error()
    {
        return redirect()->to('/transactions')
            ->with('error', 'Pembayaran gagal atau dibatalkan.');
    }
}