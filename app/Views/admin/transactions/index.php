<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Transaksi - Admin Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <?php
    function formatPaymentMethodAdmin($transaction)
    {
        $paymentMethod = $transaction['payment_method'] ?? null;
        $paymentType   = $transaction['midtrans_payment_type'] ?? null;
        $bank          = $transaction['midtrans_bank'] ?? null;

        if (!empty($paymentMethod) && $paymentMethod !== 'Midtrans') {
            return $paymentMethod;
        }

        if ($paymentType === 'bank_transfer' && !empty($bank)) {
            return 'VA ' . strtoupper($bank);
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

        return '-';
    }

    function formatPaymentStatusAdmin($status)
    {
        if ($status === 'paid') {
            return '<strong style="color:green;">Lunas</strong>';
        }

        if ($status === 'failed') {
            return '<strong style="color:#b3261e;">Gagal</strong>';
        }

        return '<strong style="color:#b7791f;">Menunggu</strong>';
    }

    function formatOrderStatusAdmin($status)
    {
        if ($status === 'processing') {
            return 'Diproses';
        }

        if ($status === 'shipped') {
            return 'Dikirim';
        }

        if ($status === 'completed') {
            return 'Selesai';
        }

        if ($status === 'cancelled') {
            return 'Dibatalkan';
        }

        return 'Pending';
    }
    ?>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/admin/dashboard" class="logo">MALINI ADMIN</a>
        <div class="nav-menu">
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/categories">Kategori</a>
            <a href="/admin/products">Produk</a>
            <a href="/admin/transactions">Transaksi</a>
            <a href="/admin/reports">Laporan</a>
            <details class="profile-menu">
                <summary class="nav-icon-button" aria-label="Profil">
                    <span class="nav-icon" aria-hidden="true">&#128100;</span>
                </summary>
                <div class="profile-panel">
                    <div class="profile-head">
                        <div class="profile-avatar"><?= esc(strtoupper(substr(session()->get('name'), 0, 1))) ?></div>
                        <div>
                            <strong><?= esc(session()->get('name')) ?></strong>
                            <span><?= esc(session()->get('email')) ?></span>
                        </div>
                    </div>
                    <div class="profile-meta">
                        <span>Role</span>
                        <strong>Administrator</strong>
                    </div>
                    <a href="/logout" class="profile-logout">Logout</a>
                </div>
            </details>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <div class="admin-breadcrumb">
            <a href="/admin/dashboard">Dashboard</a>
            <span>/</span>
            <strong>TRANSAKSI</strong>
        </div>
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
                                <td><?= esc(formatPaymentMethodAdmin($transaction)) ?></td>
                                <td><?= formatPaymentStatusAdmin($transaction['payment_status']) ?></td>
                                <td><?= esc(formatOrderStatusAdmin($transaction['order_status'])) ?></td>
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
