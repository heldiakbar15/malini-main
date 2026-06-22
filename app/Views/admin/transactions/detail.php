<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi - Admin Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

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
            <a href="/admin/transactions">TRANSAKSI</a>
            <span>/</span>
            <strong>DETAIL</strong>
        </div>
        <h1 class="section-title">Detail Transaksi</h1>
        <div class="line"></div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
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
                        <td><?= esc($transaction['user_email']) ?></td>
                    </tr>
                    <tr>
                        <th>No. HP</th>
                        <td><?= esc($transaction['customer_phone']) ?></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td><?= esc($transaction['shipping_address']) ?></td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td><strong>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></strong></td>
                    </tr>
                    <tr>
                        <th>Metode Pembayaran</th>
                        <td><?= esc($transaction['payment_method']) ?></td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td><?= esc($transaction['payment_status']) ?></td>
                    </tr>
                    <tr>
                        <th>Status Pesanan</th>
                        <td><?= esc($transaction['order_status']) ?></td>
                    </tr>
                </table>
            </div>

            <div class="table-card">
                <h3>Update Status</h3>

                <form action="/admin/transactions/update-status/<?= esc($transaction['id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>Status Pembayaran</label>
                        <select name="payment_status" required>
                            <option value="pending" <?= $transaction['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $transaction['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="failed" <?= $transaction['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status Pesanan</label>
                        <select name="order_status" required>
                            <option value="pending" <?= $transaction['order_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $transaction['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $transaction['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="completed" <?= $transaction['order_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $transaction['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <button class="btn btn-full" type="submit" onclick="return confirm('Update status transaksi ini?')">
                        Simpan Status
                    </button>
                </form>

                <br>

                <a href="/admin/transactions" class="btn btn-outline btn-full">← Kembali ke Transaksi</a>
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
                    <?php $no = 1; foreach ($details as $detail) : ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= esc($detail['product_name']) ?></strong></td>
                            <td><?= esc($detail['variant_size']) ?></td>
                            <td>Rp <?= number_format($detail['price'], 0, ',', '.') ?></td>
                            <td><?= esc($detail['quantity']) ?></td>
                            <td><strong>Rp <?= number_format($detail['subtotal'], 0, ',', '.') ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

</body>
</html>
