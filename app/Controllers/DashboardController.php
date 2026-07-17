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

        // Grafik penjualan produk dalam satu bulan, default bulan berjalan.
        $selectedSalesMonth = trim((string) $this->request->getGet('sales_month'));

        if (!preg_match('/^\d{4}-\d{2}$/', $selectedSalesMonth) || strtotime($selectedSalesMonth . '-01') === false) {
            $selectedSalesMonth = date('Y-m');
        }

        $salesMonthStart = $selectedSalesMonth . '-01 00:00:00';
        $salesMonthEnd = date('Y-m-01 00:00:00', strtotime($selectedSalesMonth . '-01 +1 month'));
        $selectedSalesMonthLabel = date('F Y', strtotime($selectedSalesMonth . '-01'));
        $chartPerPage = 4;
        $chartPage = max(1, (int) $this->request->getGet('chart_page'));

        $chartProducts = $db->table('products')
            ->select('products.id, products.name, COALESCE(SUM(product_variants.stock), 0) as current_stock')
            ->join('product_variants', 'product_variants.product_id = products.id', 'left')
            ->groupBy('products.id')
            ->groupBy('products.name')
            ->orderBy('products.name', 'ASC')
            ->get()
            ->getResultArray();

        $productIds = array_column($chartProducts, 'id');
        $salesByProduct = [];

        if (!empty($productIds)) {
            $salesData = $db->table('transaction_details')
                ->select('transaction_details.product_id, SUM(transaction_details.quantity) as quantity, SUM(transaction_details.subtotal) as total')
                ->join('transactions', 'transactions.id = transaction_details.transaction_id')
                ->where('transactions.payment_status', 'paid')
                ->where('transactions.created_at >=', $salesMonthStart)
                ->where('transactions.created_at <', $salesMonthEnd)
                ->whereIn('transaction_details.product_id', $productIds)
                ->groupBy('transaction_details.product_id')
                ->get()
                ->getResultArray();

            foreach ($salesData as $row) {
                $salesByProduct[(int) $row['product_id']] = [
                    'quantity' => (int) $row['quantity'],
                    'total' => (int) $row['total'],
                ];
            }
        }

        $salesSummaryRow = $db->table('transaction_details')
            ->select('COALESCE(SUM(transaction_details.quantity), 0) as quantity, COALESCE(SUM(transaction_details.subtotal), 0) as total')
            ->join('transactions', 'transactions.id = transaction_details.transaction_id')
            ->where('transactions.payment_status', 'paid')
            ->where('transactions.created_at >=', $salesMonthStart)
            ->where('transactions.created_at <', $salesMonthEnd)
            ->get()
            ->getRowArray();

        $salesChart = [];

        foreach ($chartProducts as $product) {
            $productSales = $salesByProduct[(int) $product['id']] ?? ['quantity' => 0, 'total' => 0];
            $stockReference = (int) $product['current_stock'] + $productSales['quantity'];
            $progress = $stockReference > 0
                ? min(100, (int) round(($productSales['quantity'] / $stockReference) * 100))
                : 0;

            $salesChart[] = [
                'label' => $product['name'],
                'quantity' => $productSales['quantity'],
                'total' => $productSales['total'],
                'stock' => $stockReference,
                'progress' => $progress,
            ];
        }

        $salesChartTotal = (int) ($salesSummaryRow['total'] ?? 0);
        $salesChartQuantity = (int) ($salesSummaryRow['quantity'] ?? 0);

        usort($salesChart, static function (array $first, array $second): int {
            if ($first['quantity'] === $second['quantity']) {
                return strcmp($first['label'], $second['label']);
            }

            return $second['quantity'] <=> $first['quantity'];
        });

        $chartProductCount = count($salesChart);
        $chartPageCount = max(1, (int) ceil($chartProductCount / $chartPerPage));
        $chartPage = min($chartPage, $chartPageCount);
        $chartOffset = ($chartPage - 1) * $chartPerPage;
        $salesChart = array_slice($salesChart, $chartOffset, $chartPerPage);

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

        $actionableOrderCount = $db->table('transactions')
            ->where('payment_status', 'paid')
            ->whereIn('order_status', ['pending', 'processing'])
            ->countAllResults();

        $latestActionableOrders = $db->table('transactions')
            ->select('id, invoice_number, customer_name, total_amount, payment_status, order_status, paid_at, created_at')
            ->where('payment_status', 'paid')
            ->whereIn('order_status', ['pending', 'processing'])
            ->orderBy('COALESCE(paid_at, created_at)', 'DESC', false)
            ->limit(4)
            ->get()
            ->getResultArray();

        $totalAdminNotifications = $actionableOrderCount
            + count($stockOutProducts)
            + count($lowStockProducts);

        return view('dashboard/admin', [
            'totalCustomers'    => $totalCustomers,
            'totalProducts'     => $totalProducts,
            'totalTransactions' => $totalTransactions,
            'totalRevenue'      => $totalRevenue,
            'salesChart'        => $salesChart,
            'salesChartTotal'   => $salesChartTotal,
            'salesChartQuantity' => $salesChartQuantity,
            'selectedSalesMonth' => $selectedSalesMonth,
            'selectedSalesMonthLabel' => $selectedSalesMonthLabel,
            'chartPage' => $chartPage,
            'chartPageCount' => $chartPageCount,
            'chartProductCount' => $chartProductCount,
            'stockOutProducts'  => $stockOutProducts,
            'lowStockProducts'  => $lowStockProducts,
            'actionableOrderCount' => $actionableOrderCount,
            'latestActionableOrders' => $latestActionableOrders,
            'totalAdminNotifications' => $totalAdminNotifications,
        ]);
    }
}
