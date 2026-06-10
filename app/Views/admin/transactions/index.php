<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Transaksi - Admin Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/admin/dashboard" class="logo">MALINI ADMIN</a>
        <div class="nav-menu">
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/transactions">Transaksi</a>
            <a href="/logout" class="btn">Logout</a>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <div class="eyebrow">Admin Panel</div>
        <h1 class="section-title">Kelola Transaksi</h1>
        <div class="line"></div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="table-card">
            <?php if (!empty($transactions)) : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Invoice</th>
                            <th>Customer</th>
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
                                <td>
                                    <?= esc($transaction['customer_name']) ?><br>
                                    <small><?= esc($transaction['user_email']) ?></small>
                                </td>
                                <td>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></td>
                                <td><?= esc($transaction['payment_method']) ?></td>
                                <td><?= esc($transaction['payment_status']) ?></td>
                                <td><?= esc($transaction['order_status']) ?></td>
                                <td><?= esc($transaction['created_at']) ?></td>
                                <td>
                                    <a class="btn" href="/admin/transactions/detail/<?= esc($transaction['id']) ?>">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Belum ada transaksi.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>