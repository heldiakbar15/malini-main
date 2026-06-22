<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background: #f6c23e; font-weight: bold; }
        .summary td { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Laporan Penjualan Malini Parfum</h2>
    <p>Periode: <?= esc($periodLabel) ?></p>

    <table>
        <tr class="summary">
            <td>Total Penjualan</td>
            <td>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></td>
            <td>Total Transaksi</td>
            <td><?= esc($totalTransactions) ?></td>
        </tr>
        <tr class="summary">
            <td>Produk Terjual</td>
            <td><?= esc($totalItems) ?></td>
            <td>Rata-rata Transaksi</td>
            <td>Rp <?= number_format($averageRevenue, 0, ',', '.') ?></td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Produk</th>
                <th>Item</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($transactions)) : ?>
                <?php $no = 1; foreach ($transactions as $transaction) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc(date('d/m/Y H:i', strtotime($transaction['created_at']))) ?></td>
                        <td><?= esc($transaction['invoice_number']) ?></td>
                        <td><?= esc($transaction['customer_name']) ?></td>
                        <td><?= esc($transaction['product_summary'] ?: '-') ?></td>
                        <td><?= esc($transaction['total_items']) ?></td>
                        <td>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7">Tidak ada transaksi paid pada periode ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
