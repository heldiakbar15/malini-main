<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout Berhasil - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php
    $paymentStatus = $transaction['payment_status'] ?? 'pending';
    $orderStatus   = $transaction['order_status'] ?? 'pending';
?>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>

        <div class="nav-menu">
            <a href="/">Produk</a>
            <a href="/dashboard">Dashboard</a>

            <?php if ($paymentStatus === 'paid') : ?>
                <a href="/transactions">Riwayat</a>
            <?php endif; ?>

            <details class="profile-menu">
                <summary class="nav-icon-button" aria-label="Profil">
                    <span class="nav-icon" aria-hidden="true">&#128100;</span>
                </summary>

                <div class="profile-panel">
                    <div class="profile-head">
                        <div class="profile-avatar">
                            <?= esc(strtoupper(substr(session()->get('name') ?? 'U', 0, 1))) ?>
                        </div>

                        <div>
                            <strong><?= esc(session()->get('name') ?? 'User') ?></strong>
                            <span><?= esc(session()->get('email') ?? '-') ?></span>
                        </div>
                    </div>

                    <div class="profile-meta">
                        <span>Role</span>
                        <strong>Customer</strong>
                    </div>

                    <a href="/logout" class="profile-logout">Logout</a>
                </div>
            </details>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">

        <?php if ($paymentStatus === 'paid') : ?>
            <div class="eyebrow">Pembayaran Berhasil</div>
            <h1 class="section-title">Pembayaran Berhasil</h1>
        <?php elseif ($paymentStatus === 'failed') : ?>
            <div class="eyebrow">Pembayaran Gagal</div>
            <h1 class="section-title">Pembayaran Gagal</h1>
        <?php else : ?>
            <div class="eyebrow">Menunggu Pembayaran</div>
            <h1 class="section-title">Lanjutkan Pembayaran</h1>
        <?php endif; ?>

        <div class="line"></div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="table-card" style="max-width:800px;">

            <?php if ($paymentStatus === 'paid') : ?>
                <p>
                    Pembayaran berhasil diterima. Pesanan Anda sudah masuk ke riwayat transaksi.
                </p>
            <?php elseif ($paymentStatus === 'failed') : ?>
                <p>
                    Pesanan berhasil dibuat, tetapi pembayaran gagal atau dibatalkan.
                    Silakan buat pesanan ulang.
                </p>
            <?php else : ?>
                <p>
                    Pesanan berhasil dibuat, tetapi belum dibayar.
                    Silakan lanjutkan pembayaran melalui Midtrans.
                </p>
            <?php endif; ?>

            <table class="table">
                <tr>
                    <th>No. Invoice</th>
                    <td><?= esc($transaction['invoice_number']) ?></td>
                </tr>

                <tr>
                    <th>Nama Penerima</th>
                    <td><?= esc($transaction['customer_name']) ?></td>
                </tr>

                <tr>
                    <th>Total Pembayaran</th>
                    <td>
                        <strong>
                            Rp <?= number_format((int) $transaction['total_amount'], 0, ',', '.') ?>
                        </strong>
                    </td>
                </tr>

                <tr>
                    <th>Metode Pembayaran</th>
                    <td><?= esc($transaction['payment_method'] ?? 'Midtrans') ?></td>
                </tr>

                <tr>
                    <th>Status Pembayaran</th>
                    <td>
                        <?php if ($paymentStatus === 'paid') : ?>
                            <strong style="color:green;">Lunas</strong>
                        <?php elseif ($paymentStatus === 'failed') : ?>
                            <strong style="color:#b3261e;">Gagal</strong>
                        <?php else : ?>
                            <strong style="color:#b7791f;">Menunggu Pembayaran</strong>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th>Status Pesanan</th>
                    <td>
                        <?php if ($paymentStatus === 'paid') : ?>
                            <?= esc($orderStatus) ?>
                        <?php elseif ($paymentStatus === 'failed') : ?>
                            Dibatalkan
                        <?php else : ?>
                            Belum Diproses
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <br>

            <?php if ($paymentStatus === 'paid') : ?>

                <div class="alert-success">
                    Pembayaran berhasil. Pesanan Anda sedang diproses.
                </div>

                <br>

                <a href="/transactions/detail/<?= esc($transaction['id']) ?>" class="btn">
                    Lihat Detail Transaksi
                </a>

                <a href="/transactions" class="btn btn-outline">
                    Lihat Riwayat Transaksi
                </a>

                <a href="/" class="btn btn-outline">
                    Kembali ke Produk
                </a>

            <?php elseif ($paymentStatus === 'failed') : ?>

                <div class="alert-error">
                    Pembayaran gagal. Transaksi ini tidak masuk ke riwayat transaksi.
                </div>

                <br>

                <a href="/" class="btn">
                    Buat Pesanan Baru
                </a>

            <?php else : ?>

                <div class="alert-success">
                    Transaksi belum masuk ke riwayat karena pembayaran belum berhasil.
                </div>

                <br>

                <a href="/payment/pay/<?= esc($transaction['id']) ?>" class="btn">
                    Bayar Sekarang
                </a>

                <button 
                    type="button" 
                    class="btn btn-outline" 
                    disabled 
                    style="opacity:0.5; cursor:not-allowed;"
                >
                    Detail Transaksi Tersedia Setelah Pembayaran
                </button>

                <a href="/" class="btn btn-outline">
                    Kembali ke Produk
                </a>

            <?php endif; ?>

        </div>
    </div>
</section>

</body>
</html>