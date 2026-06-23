<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi - Admin Malini Parfum</title>
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

    return '<strong style="color:#b7791f;">Menunggu Pembayaran</strong>';
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
                        <div class="profile-avatar">
                            <?= esc(strtoupper(substr(session()->get('name') ?? 'A', 0, 1))) ?>
                        </div>
                        <div>
                            <strong><?= esc(session()->get('name') ?? 'Admin') ?></strong>
                            <span><?= esc(session()->get('email') ?? '-') ?></span>
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
            <a href="/admin/transactions">TRANSAKSI</a>
            <span>/</span>
            <strong>DETAIL</strong>
        </div>

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
                        <th>Customer</th>
                        <td><?= esc($transaction['customer_name']) ?></td>
                    </tr>

                    <tr>
                        <th>Email Akun</th>
                        <td><?= esc($transaction['user_email'] ?? '-') ?></td>
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
                        <td><?= esc(formatPaymentMethodAdmin($transaction)) ?></td>
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
                        <td><?= formatPaymentStatusAdmin($transaction['payment_status'] ?? 'pending') ?></td>
                    </tr>

                    <tr>
                        <th>Status Pesanan</th>
                        <td><?= esc(formatOrderStatusAdmin($transaction['order_status'] ?? 'pending')) ?></td>
                    </tr>
                </table>
            </div>

            <div class="table-card">
                <h3>Update Status Pesanan</h3>

                <div class="form-group">
                    <label>Status Pembayaran</label>
                    <input
                        class="input"
                        type="text"
                        value="<?php
                            if (($transaction['payment_status'] ?? '') === 'paid') {
                                echo 'Lunas';
                            } elseif (($transaction['payment_status'] ?? '') === 'failed') {
                                echo 'Gagal';
                            } else {
                                echo 'Menunggu Pembayaran';
                            }
                        ?>"
                        readonly
                    >
                    <small>Status pembayaran otomatis dari Midtrans dan tidak dapat diubah manual oleh admin.</small>
                </div>

                <form action="/admin/transactions/update-status/<?= esc($transaction['id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>Status Pesanan</label>
                        <select name="order_status" required>
                            <option value="pending" <?= ($transaction['order_status'] ?? '') === 'pending' ? 'selected' : '' ?>>
                                Pending
                            </option>
                            <option value="processing" <?= ($transaction['order_status'] ?? '') === 'processing' ? 'selected' : '' ?>>
                                Diproses
                            </option>
                            <option value="shipped" <?= ($transaction['order_status'] ?? '') === 'shipped' ? 'selected' : '' ?>>
                                Dikirim
                            </option>
                            <option value="completed" <?= ($transaction['order_status'] ?? '') === 'completed' ? 'selected' : '' ?>>
                                Selesai
                            </option>
                            <option value="cancelled" <?= ($transaction['order_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>
                                Dibatalkan
                            </option>
                        </select>
                    </div>

                    <button class="btn btn-full" type="submit" onclick="return confirm('Update status pesanan ini?')">
                        Simpan Status Pesanan
                    </button>
                </form>

                <br>

                <a href="/admin/transactions" class="btn btn-outline btn-full">
                    ← Kembali ke Transaksi
                </a>
            </div>
        </div>

        <br><br>

        <div class="table-card">
            <h3>Produk yang Dibeli</h3>

            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Ukuran</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($details)) : ?>
                        <?php $no = 1; foreach ($details as $detail) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= esc($detail['product_name']) ?></strong></td>
                                <td><?= esc($detail['variant_size']) ?></td>
                                <td>Rp <?= number_format((int) $detail['price'], 0, ',', '.') ?></td>
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
                            <td colspan="6">Detail produk tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

</body>
</html>