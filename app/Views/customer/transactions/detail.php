<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

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
                        <div class="profile-avatar"><?= esc(strtoupper(substr(session()->get('name'), 0, 1))) ?></div>
                        <div>
                            <strong><?= esc(session()->get('name')) ?></strong>
                            <span><?= esc(session()->get('email')) ?></span>
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

                <br>

                <div class="alert-success">
                    Status pembayaran dan pesanan akan diperbarui oleh admin.
                </div>
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
                        <?php foreach ($details as $detail) : ?>
                            <tr>
                                <td>
                                    <strong><?= esc($detail['product_name']) ?></strong><br>
                                    <small>Rp <?= number_format($detail['price'], 0, ',', '.') ?></small>
                                </td>
                                <td><?= esc($detail['variant_size']) ?></td>
                                <td><?= esc($detail['quantity']) ?></td>
                                <td><strong>Rp <?= number_format($detail['subtotal'], 0, ',', '.') ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <br>

                <a href="/transactions" class="btn btn-outline">← Kembali ke Riwayat</a>
            </div>
        </div>
    </div>
</section>

</body>
</html>
