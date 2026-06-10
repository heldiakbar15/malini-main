<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout Berhasil - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>
        <div class="nav-menu">
            <a href="/">Produk</a>
            <a href="/dashboard">Dashboard</a>
            <a href="/transactions">Riwayat</a>
            <a href="/logout" class="btn">Logout</a>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <div class="eyebrow">Pesanan Berhasil</div>
        <h1 class="section-title">Checkout Berhasil</h1>
        <div class="line"></div>

        <div class="table-card" style="max-width:800px;">
            <p>Pesanan berhasil dibuat. Silakan lakukan pembayaran sesuai instruksi toko.</p>

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
                    <td><strong>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></strong></td>
                </tr>
                <tr>
                    <th>Metode Pembayaran</th>
                    <td>QRIS</td>
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
                Pesanan akan diproses setelah pembayaran diverifikasi oleh admin.
            </div>

            <a href="/transactions" class="btn">Lihat Riwayat Transaksi</a>
            <a href="/" class="btn btn-outline">Kembali ke Produk</a>
        </div>
    </div>
</section>

</body>
</html>