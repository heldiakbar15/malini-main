<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Malini Parfum</title>

    <style>
        @page {
            margin: 20px 24px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #111;
        }

        h2 {
            margin: 0;
            font-size: 22px;
            font-weight: bold;
        }

        .period {
            margin-top: 14px;
            margin-bottom: 18px;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 7px 8px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f6c23e;
            color: #000;
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-table td {
            font-weight: bold;
            background-color: #fff;
        }

        .summary-label {
            width: 23%;
        }

        .summary-value {
            width: 27%;
        }

        .data-table th {
            background-color: #f6c23e;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .nowrap {
            white-space: nowrap;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            color: #555;
            text-align: right;
        }
    </style>
</head>
<body>

    <h2>Laporan Penjualan Malini Parfum</h2>

    <div class="period">
        Periode: <?= esc($periodLabel) ?>
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-label">Total Penjualan</td>
            <td class="summary-value">Rp <?= number_format((int) $totalRevenue, 0, ',', '.') ?></td>
            <td class="summary-label">Total Transaksi</td>
            <td class="summary-value"><?= esc($totalTransactions) ?></td>
        </tr>
        <tr>
            <td class="summary-label">Produk Terjual</td>
            <td class="summary-value"><?= esc($totalItems) ?></td>
            <td class="summary-label">Rata-rata Transaksi</td>
            <td class="summary-value">Rp <?= number_format((int) $averageRevenue, 0, ',', '.') ?></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 14%;">Tanggal</th>
                <th style="width: 19%;">Invoice</th>
                <th style="width: 12%;">Customer</th>
                <th style="width: 28%;">Produk</th>
                <th style="width: 7%;" class="text-center">Item</th>
                <th style="width: 15%;" class="text-right">Total</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($transactions)) : ?>
                <?php $no = 1; foreach ($transactions as $transaction) : ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="nowrap">
                            <?= esc(date('d/m/Y H:i', strtotime($transaction['created_at']))) ?>
                        </td>
                        <td><?= esc($transaction['invoice_number']) ?></td>
                        <td><?= esc($transaction['customer_name']) ?></td>
                        <td><?= esc($transaction['product_summary'] ?: '-') ?></td>
                        <td class="text-center"><?= esc($transaction['total_items']) ?></td>
                        <td class="text-right">
                            Rp <?= number_format((int) $transaction['total_amount'], 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center">
                        Tidak ada transaksi paid pada periode ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada <?= date('d/m/Y H:i') ?>
    </div>

</body>
</html>