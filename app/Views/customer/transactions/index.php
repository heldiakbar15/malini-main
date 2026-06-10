<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>
        <div class="nav-menu">
            <a href="/">Produk</a>
            <a href="/cart">Keranjang</a>
            <a href="/dashboard">Dashboard</a>
            <a href="/logout" class="btn">Logout</a>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <div class="eyebrow">Customer</div>
        <h1 class="section-title">Riwayat Transaksi</h1>
        <div class="line"></div>

        <div class="table-card">
            <?php if (!empty($transactions)) : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Invoice</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Status Bayar</th>
                            <th>Status Pesanan</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($transactions as $transaction) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= esc($transaction['invoice_number']) ?></strong></td>
                                <td>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></td>
                                <td><?= esc($transaction['payment_method']) ?></td>
                                <td><?= esc($transaction['payment_status']) ?></td>
                                <td><?= esc($transaction['order_status']) ?></td>
                                <td><?= esc($transaction['created_at']) ?></td>
                                <td>
                                    <a class="btn" href="/transactions/detail/<?= esc($transaction['id']) ?>">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Belum ada riwayat transaksi.</p>
                <a href="/" class="btn">Belanja Sekarang</a>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>