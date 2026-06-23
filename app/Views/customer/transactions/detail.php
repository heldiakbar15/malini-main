<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php
function formatPaymentMethod($transaction)
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

    return 'Midtrans';
}

function formatPaymentStatus($status)
{
    if ($status === 'paid') {
        return '<strong style="color:green;">Lunas</strong>';
    }

    if ($status === 'failed') {
        return '<strong style="color:#b3261e;">Waktu Pembayaran Habis</strong>';
    }

    return '<strong style="color:#b7791f;">Belum Dibayar</strong>';
}

function formatOrderStatus($status)
{
    if ($status === 'processing') {
        return 'Sedang Diproses';
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
        <a href="/" class="logo">MALINI PARFUM</a>

        <div class="nav-menu">
            <a href="/transactions">Riwayat</a>
            <a href="/dashboard">Dashboard</a>

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
        <div class="eyebrow">Detail Pesanan</div>
        <h1 class="section-title">Detail Transaksi</h1>
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

        <div class="detail-grid">
            <div class="table-card">
                <h3>Data Transaksi</h3>

                <table class="table">
                    <tr>
                        <th>Invoice</th>
                        <td><?= esc($transaction['invoice_number']) ?></td>
                    </tr>

                    <tr>
                        <th>Nama Penerima</th>
                        <td><?= esc($transaction['customer_name']) ?></td>
                    </tr>

                    <tr>
                        <th>No. HP</th>
                        <td><?= esc($transaction['customer_phone'] ?? '-') ?></td>
                    </tr>

                    <tr>
                        <th>Alamat</th>
                        <td><?= esc($transaction['shipping_address']) ?></td>
                    </tr>

                    <tr>
                        <th>Total</th>
                        <td>
                            <strong>
                                Rp <?= number_format((int) $transaction['total_amount'], 0, ',', '.') ?>
                            </strong>
                        </td>
                    </tr>

                    <tr>
                        <th>Metode Pembayaran</th>
                        <td><?= esc(formatPaymentMethod($transaction)) ?></td>
                    </tr>

                    <?php if (!empty($transaction['midtrans_bank'])) : ?>
                        <tr>
                            <th>Bank</th>
                            <td><?= esc(strtoupper($transaction['midtrans_bank'])) ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php if (!empty($transaction['midtrans_va_number'])) : ?>
                        <tr>
                            <th>Nomor Virtual Account</th>
                            <td><?= esc($transaction['midtrans_va_number']) ?></td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <th>Status Pembayaran</th>
                        <td><?= formatPaymentStatus($transaction['payment_status'] ?? 'pending') ?></td>
                    </tr>

                    <tr>
                        <th>Status Pesanan</th>
                        <td><?= esc(formatOrderStatus($transaction['order_status'] ?? 'pending')) ?></td>
                    </tr>
                </table>

                <br>

                <?php if (($transaction['payment_status'] ?? '') === 'paid') : ?>

                    <div class="alert-success">
                        Pembayaran berhasil. Pesanan Anda sedang diproses.
                    </div>

                <?php elseif (($transaction['payment_status'] ?? '') === 'failed') : ?>

                    <div class="alert-error">
                        Waktu pembayaran sudah habis. Transaksi ini tidak dapat dibayar lagi.
                    </div>

                <?php else : ?>

                    <div class="alert-success">
                        Transaksi ini belum dibayar. Anda masih dapat melanjutkan pembayaran melalui Midtrans.
                    </div>

                <?php endif; ?>
            </div>

            <div class="table-card">
                <h3>Produk yang Dibeli</h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Ukuran</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($details)) : ?>
                            <?php foreach ($details as $detail) : ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($detail['product_name']) ?></strong><br>
                                        <small>
                                            Rp <?= number_format((int) $detail['price'], 0, ',', '.') ?>
                                        </small>
                                    </td>

                                    <td><?= esc($detail['variant_size']) ?></td>
                                    <td><?= esc($detail['quantity']) ?></td>

                                    <td>
                                        <strong>
                                            Rp <?= number_format((int) $detail['subtotal'], 0, ',', '.') ?>
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4">Detail produk tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <br>

                <?php if (($transaction['payment_status'] ?? '') === 'pending') : ?>
                    <a href="/payment/pay/<?= esc($transaction['id']) ?>" class="btn">
                        Bayar Sekarang
                    </a>
                <?php elseif (($transaction['payment_status'] ?? '') === 'failed') : ?>
                    <a href="/" class="btn">
                        Buat Pesanan Baru
                    </a>
                <?php endif; ?>

                <a href="/transactions" class="btn btn-outline">
                    ← Kembali ke Riwayat
                </a>
            </div>
        </div>
    </div>
</section>

</body>
</html>