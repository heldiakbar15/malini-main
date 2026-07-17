<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use Midtrans\Config;
use Midtrans\Transaction as MidtransTransaction;

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

        if (($transaction['payment_status'] ?? '') === 'pending') {
            $transaction = $this->syncTransactionWithMidtrans($transaction);
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

        $orderId = $notification['order_id'] ?? null;

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

        $validationError = $this->validateNotificationPayload($notification, $transaction);

        if ($validationError !== null) {
            return $this->response->setJSON([
                'status'   => 'rejected',
                'message'  => $validationError['message'],
                'order_id' => $orderId,
            ])->setStatusCode($validationError['status_code']);
        }

        $updateData = $this->protectPaidStatus($transaction, $this->buildPaymentUpdateData($notification));
        $this->transactionModel->update($transaction['id'], $updateData);

        return $this->response->setJSON([
            'status'         => 'success',
            'message'        => 'Status pembayaran berhasil diperbarui',
            'order_id'       => $orderId,
            'payment_status' => $updateData['payment_status'],
            'order_status'   => $updateData['order_status'],
        ])->setStatusCode(200);
    }

    public function finish()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $transaction = $this->findTransactionFromReturn();

        if (!$transaction) {
            return redirect()->to('/transactions')
                ->with('error', 'Pembayaran selesai, tetapi transaksi tidak ditemukan untuk diverifikasi.');
        }

        if ((int) $transaction['user_id'] !== (int) session()->get('user_id')) {
            return redirect()->to('/transactions')
                ->with('error', 'Anda tidak memiliki akses ke transaksi ini.');
        }

        $transaction = $this->syncTransactionWithMidtrans($transaction);

        if (($transaction['payment_status'] ?? '') === 'paid') {
            return redirect()->to('/transactions/detail/' . $transaction['id'])
                ->with('success', 'Pembayaran berhasil. Status transaksi sudah diperbarui menjadi lunas.');
        }

        return redirect()->to('/transactions/detail/' . $transaction['id'])
            ->with('success', 'Pembayaran selesai diproses. Jika status masih pending, silakan tunggu notifikasi Midtrans beberapa saat.');
    }

    public function unfinish()
    {
        $transaction = $this->findTransactionFromReturn();

        if ($transaction && session()->get('logged_in') && (int) $transaction['user_id'] === (int) session()->get('user_id')) {
            $transaction = $this->syncTransactionWithMidtrans($transaction);

            return redirect()->to('/transactions/detail/' . $transaction['id'])
                ->with('error', 'Pembayaran belum selesai. Anda masih dapat melanjutkan pembayaran dari detail transaksi.');
        }

        return redirect()->to('/transactions')
            ->with('error', 'Pembayaran belum selesai. Anda masih dapat melanjutkan pembayaran dari detail transaksi.');
    }

    public function error()
    {
        $transaction = $this->findTransactionFromReturn();

        if ($transaction && session()->get('logged_in') && (int) $transaction['user_id'] === (int) session()->get('user_id')) {
            $transaction = $this->syncTransactionWithMidtrans($transaction);

            return redirect()->to('/transactions/detail/' . $transaction['id'])
                ->with('error', 'Pembayaran gagal atau dibatalkan.');
        }

        return redirect()->to('/transactions')
            ->with('error', 'Pembayaran gagal atau dibatalkan.');
    }

    private function findTransactionFromReturn(): ?array
    {
        $orderId = trim((string) $this->request->getGet('order_id'));
        $transactionId = trim((string) $this->request->getGet('transaction_id'));

        if ($orderId !== '') {
            return $this->transactionModel
                ->where('midtrans_order_id', $orderId)
                ->orWhere('invoice_number', $orderId)
                ->first();
        }

        if ($transactionId !== '') {
            return $this->transactionModel->find($transactionId);
        }

        return null;
    }

    private function validateNotificationPayload(array $notification, array $transaction): ?array
    {
        $serverKey = getenv('MIDTRANS_SERVER_KEY') ?: '';
        $orderId = (string) ($notification['order_id'] ?? '');
        $statusCode = (string) ($notification['status_code'] ?? '');
        $grossAmount = (string) ($notification['gross_amount'] ?? '');
        $signatureKey = (string) ($notification['signature_key'] ?? '');

        if ($serverKey === '' || $orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return [
                'status_code' => 403,
                'message' => 'Payload notifikasi Midtrans tidak lengkap.',
            ];
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (!hash_equals($expectedSignature, $signatureKey)) {
            return [
                'status_code' => 403,
                'message' => 'Signature notifikasi Midtrans tidak valid.',
            ];
        }

        if ((int) round((float) $grossAmount) !== (int) $transaction['total_amount']) {
            return [
                'status_code' => 400,
                'message' => 'Nominal pembayaran tidak sesuai dengan transaksi.',
            ];
        }

        return null;
    }

    private function syncTransactionWithMidtrans(array $transaction): array
    {
        $orderId = $transaction['midtrans_order_id'] ?? $transaction['invoice_number'] ?? null;

        if (!$orderId) {
            return $transaction;
        }

        try {
            $status = MidtransTransaction::status($orderId);
            $statusData = json_decode(json_encode($status), true) ?: [];

            if (!empty($statusData)) {
                $this->transactionModel->update($transaction['id'], $this->protectPaidStatus($transaction, $this->buildPaymentUpdateData($statusData)));

                return $this->transactionModel->find($transaction['id']) ?: $transaction;
            }
        } catch (\Throwable $exception) {
            log_message('error', 'Gagal sinkron status Midtrans order ' . $orderId . ': ' . $exception->getMessage());
        }

        return $transaction;
    }

    private function buildPaymentUpdateData(array $midtransData): array
    {
        $transactionStatus = $midtransData['transaction_status'] ?? null;
        $fraudStatus = $midtransData['fraud_status'] ?? null;
        $paymentType = $midtransData['payment_type'] ?? null;
        $bank = null;
        $vaNumber = null;

        if (isset($midtransData['va_numbers'][0])) {
            $bank = $midtransData['va_numbers'][0]['bank'] ?? null;
            $vaNumber = $midtransData['va_numbers'][0]['va_number'] ?? null;
        }

        if (!$bank && isset($midtransData['permata_va_number'])) {
            $bank = 'permata';
            $vaNumber = $midtransData['permata_va_number'];
        }

        $paymentStatus = 'pending';
        $orderStatus = 'pending';

        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && ($fraudStatus === 'accept' || $fraudStatus === null))) {
            $paymentStatus = 'paid';
            $orderStatus = 'processing';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel', 'failure'], true)) {
            $paymentStatus = 'failed';
            $orderStatus = 'cancelled';
        }

        $updateData = [
            'payment_status'              => $paymentStatus,
            'order_status'                => $orderStatus,
            'payment_method'              => $this->formatPaymentMethod($paymentType, $bank),
            'midtrans_transaction_id'     => $midtransData['transaction_id'] ?? null,
            'midtrans_payment_type'       => $paymentType,
            'midtrans_bank'               => $bank,
            'midtrans_va_number'          => $vaNumber,
            'midtrans_transaction_status' => $transactionStatus,
            'midtrans_fraud_status'       => $fraudStatus,
        ];

        if ($paymentStatus === 'paid') {
            $updateData['paid_at'] = $midtransData['settlement_time']
                ?? $midtransData['transaction_time']
                ?? date('Y-m-d H:i:s');
        }

        return $updateData;
    }

    private function protectPaidStatus(array $transaction, array $updateData): array
    {
        if (($transaction['payment_status'] ?? '') !== 'paid' || ($updateData['payment_status'] ?? '') === 'paid') {
            return $updateData;
        }

        $updateData['payment_status'] = 'paid';
        $updateData['order_status'] = $transaction['order_status'] ?? 'processing';

        if (!empty($transaction['paid_at'])) {
            $updateData['paid_at'] = $transaction['paid_at'];
        }

        return $updateData;
    }

    private function formatPaymentMethod(?string $paymentType, ?string $bank): string
    {
        if ($paymentType === 'bank_transfer' && $bank) {
            return 'VA ' . strtoupper($bank);
        }

        if ($paymentType === 'bank_transfer') {
            return 'Bank Transfer';
        }

        if ($paymentType === 'gopay') {
            return 'GoPay';
        }

        if ($paymentType === 'qris') {
            return 'QRIS';
        }

        if ($paymentType === 'shopeepay') {
            return 'ShopeePay';
        }

        if ($paymentType === 'credit_card') {
            return 'Kartu Kredit';
        }

        if (!empty($paymentType)) {
            return ucwords(str_replace('_', ' ', $paymentType));
        }

        return 'Midtrans';
    }
}
