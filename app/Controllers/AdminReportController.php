<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class AdminReportController extends BaseController
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

        $request = service('request');

        $startDate = $request->getGet('start_date');
        $endDate   = $request->getGet('end_date');

        $reportData = $this->getReportData($startDate, $endDate);

        return view('admin/reports/index', $reportData);
    }

    public function exportExcel()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $request = service('request');

        $startDate = $request->getGet('start_date');
        $endDate   = $request->getGet('end_date');

        $reportData = $this->getReportData($startDate, $endDate);

        $filename = 'laporan-penjualan-' . date('Ymd-His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody(view('admin/reports/excel', $reportData));
    }

    public function exportPdf()
    {
        if ($redirect = $this->checkAdmin()) {
            return $redirect;
        }

        $request = service('request');

        $startDate = $request->getGet('start_date');
        $endDate   = $request->getGet('end_date');

        $reportData = $this->getReportData($startDate, $endDate);

        $html = view('admin/reports/pdf', $reportData);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'laporan-penjualan-' . date('Ymd-His') . '.pdf';

        $dompdf->stream($filename, [
            'Attachment' => false
        ]);

        exit;
    }

    private function getReportData($startDate = null, $endDate = null)
    {
        $transactionModel = new TransactionModel();

        $builder = $transactionModel
            ->select("
                transactions.*,
                COALESCE(SUM(transaction_details.quantity), 0) AS total_items,
                GROUP_CONCAT(
                    CONCAT(
                        transaction_details.product_name,
                        ' ',
                        transaction_details.variant_size,
                        ' x',
                        transaction_details.quantity
                    )
                    SEPARATOR ', '
                ) AS product_summary
            ")
            ->join(
                'transaction_details',
                'transaction_details.transaction_id = transactions.id',
                'left'
            )
            ->where('transactions.payment_status', 'paid')
            ->groupBy('transactions.id')
            ->orderBy('transactions.created_at', 'DESC');

        if (!empty($startDate)) {
            $builder->where('DATE(transactions.created_at) >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('DATE(transactions.created_at) <=', $endDate);
        }

        $transactions = $builder->findAll();

        $totalRevenue = 0;
        $totalItems = 0;
        $totalTransactions = count($transactions);

        foreach ($transactions as $transaction) {
            $totalRevenue += (int) $transaction['total_amount'];
            $totalItems += (int) $transaction['total_items'];
        }

        $averageRevenue = $totalTransactions > 0
            ? $totalRevenue / $totalTransactions
            : 0;

        $periodLabel = $this->makePeriodLabel($startDate, $endDate);

        return [
            'transactions'      => $transactions,
            'totalRevenue'      => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'totalItems'        => $totalItems,
            'averageRevenue'    => $averageRevenue,
            'periodLabel'       => $periodLabel,
            'startDate'         => $startDate,
            'endDate'           => $endDate,
        ];
    }

    private function makePeriodLabel($startDate = null, $endDate = null)
    {
        if (!empty($startDate) && !empty($endDate)) {
            return date('d F Y', strtotime($startDate)) . ' - ' . date('d F Y', strtotime($endDate));
        }

        if (!empty($startDate)) {
            return 'Mulai ' . date('d F Y', strtotime($startDate));
        }

        if (!empty($endDate)) {
            return 'Sampai ' . date('d F Y', strtotime($endDate));
        }

        return date('d F Y');
    }
}