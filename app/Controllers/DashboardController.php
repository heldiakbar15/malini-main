<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function customer()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('user_id');
        $cart = session()->get('cart') ?? [];

        $totalTransactions = $db->table('transactions')
            ->where('user_id', $userId)
            ->countAllResults();

        $pendingTransactions = $db->table('transactions')
            ->where('user_id', $userId)
            ->groupStart()
                ->where('payment_status', 'pending')
                ->orWhere('order_status', 'pending')
            ->groupEnd()
            ->countAllResults();

        $completedTransactions = $db->table('transactions')
            ->where('user_id', $userId)
            ->where('order_status', 'completed')
            ->countAllResults();

        $spendingRow = $db->table('transactions')
            ->selectSum('total_amount', 'total_spending')
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->get()
            ->getRowArray();

        $latestTransactions = $db->table('transactions')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(4)
            ->get()
            ->getResultArray();

        return view('dashboard/customer', [
            'totalTransactions' => $totalTransactions,
            'pendingTransactions' => $pendingTransactions,
            'completedTransactions' => $completedTransactions,
            'totalSpending' => $spendingRow['total_spending'] ?? 0,
            'cartItemCount' => count($cart),
            'latestTransactions' => $latestTransactions,
        ]);
    }

    public function admin()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        if (session()->get('role') !== 'admin') {
            return redirect()->to('/dashboard');
        }

        $db = \Config\Database::connect();

        // Total customer
        $totalCustomers = $db->table('users')
            ->where('role', 'customer')
            ->countAllResults();

        // Total produk
        $totalProducts = $db->table('products')
            ->countAllResults();

        // Total transaksi
        $totalTransactions = $db->table('transactions')
            ->countAllResults();

        // Total pendapatan dari transaksi yang sudah dibayar
        $revenueRow = $db->table('transactions')
            ->selectSum('total_amount', 'total_revenue')
            ->where('payment_status', 'paid')
            ->get()
            ->getRowArray();

        $totalRevenue = $revenueRow['total_revenue'] ?? 0;

        // Grafik penjualan 4 bulan terakhir
        $startMonth = date('Y-m-01', strtotime('-3 months'));

        $salesData = $db->table('transactions')
            ->select("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total", false)
            ->where('payment_status', 'paid')
            ->where('created_at >=', $startMonth)
            ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        $salesChart = [];

        for ($i = 3; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-$i months"));
            $monthLabel = date('M Y', strtotime("-$i months"));

            $salesChart[$monthKey] = [
                'label' => $monthLabel,
                'total' => 0,
                'progress' => 0,
                'growth' => 0,
            ];
        }

        foreach ($salesData as $row) {
            if (isset($salesChart[$row['month']])) {
                $salesChart[$row['month']]['total'] = (int) $row['total'];
            }
        }

        $salesChart = array_values($salesChart);

        $maxSales = 0;

        foreach ($salesChart as $sale) {
            if ($sale['total'] > $maxSales) {
                $maxSales = $sale['total'];
            }
        }

        foreach ($salesChart as $index => $sale) {
            $salesChart[$index]['progress'] = $maxSales > 0
                ? (int) round(($sale['total'] / $maxSales) * 100)
                : 0;

            $previousTotal = $index > 0 ? $salesChart[$index - 1]['total'] : 0;

            if ($previousTotal > 0) {
                $salesChart[$index]['growth'] = (int) round((($sale['total'] - $previousTotal) / $previousTotal) * 100);
            } elseif ($sale['total'] > 0) {
                $salesChart[$index]['growth'] = 100;
            }
        }

        // Produk stok habis
        $stockOutProducts = $db->table('product_variants')
            ->select('product_variants.*, products.name as product_name')
            ->join('products', 'products.id = product_variants.product_id')
            ->where('product_variants.stock <=', 0)
            ->orderBy('products.name', 'ASC')
            ->get()
            ->getResultArray();

        // Produk stok menipis
        $lowStockProducts = $db->table('product_variants')
            ->select('product_variants.*, products.name as product_name')
            ->join('products', 'products.id = product_variants.product_id')
            ->where('product_variants.stock >', 0)
            ->where('product_variants.stock <=', 5)
            ->orderBy('product_variants.stock', 'ASC')
            ->get()
            ->getResultArray();

        $pendingTransactionCount = $db->table('transactions')
            ->groupStart()
                ->where('payment_status', 'pending')
                ->orWhere('order_status', 'pending')
            ->groupEnd()
            ->countAllResults();

        $latestPendingTransactions = $db->table('transactions')
            ->select('id, invoice_number, customer_name, total_amount, payment_status, order_status, created_at')
            ->groupStart()
                ->where('payment_status', 'pending')
                ->orWhere('order_status', 'pending')
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->limit(4)
            ->get()
            ->getResultArray();

        $totalAdminNotifications = $pendingTransactionCount
            + count($stockOutProducts)
            + count($lowStockProducts);

        return view('dashboard/admin', [
            'totalCustomers'    => $totalCustomers,
            'totalProducts'     => $totalProducts,
            'totalTransactions' => $totalTransactions,
            'totalRevenue'      => $totalRevenue,
            'salesChart'        => $salesChart,
            'stockOutProducts'  => $stockOutProducts,
            'lowStockProducts'  => $lowStockProducts,
            'pendingTransactionCount' => $pendingTransactionCount,
            'latestPendingTransactions' => $latestPendingTransactions,
            'totalAdminNotifications' => $totalAdminNotifications,
        ]);
    }
}
