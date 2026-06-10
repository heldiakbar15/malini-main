<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Customer - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="dashboard">
    <nav class="navbar">
        <div class="container navbar-inner">
            <a href="/" class="logo">MALINI PARFUM</a>
            <div class="nav-menu">
                <a href="/">Produk</a>
                <a href="/cart">Keranjang</a>
                <a href="/transactions">Riwayat</a>
                <a href="/logout" class="btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-header">
        <div class="container">
            <div class="eyebrow">Customer Panel</div>
            <h1 style="margin:0;">Selamat datang, <?= esc(session()->get('name')) ?></h1>
            <p style="color:#bbb;">Kelola aktivitas belanja parfum Anda dengan mudah.</p>
        </div>
    </div>

    <div class="container">
        <div class="dashboard-card-grid">
            <a href="/" class="dashboard-card">
                <div class="eyebrow">Produk</div>
                <h3>Lihat Produk</h3>
                <p>Jelajahi koleksi parfum Malini.</p>
            </a>

            <a href="/cart" class="dashboard-card">
                <div class="eyebrow">Keranjang</div>
                <h3>Keranjang Belanja</h3>
                <p>Lihat dan edit produk di keranjang.</p>
            </a>

            <a href="/transactions" class="dashboard-card">
                <div class="eyebrow">Transaksi</div>
                <h3>Riwayat Transaksi</h3>
                <p>Cek pesanan dan status pembayaran.</p>
            </a>

            <a href="/logout" class="dashboard-card">
                <div class="eyebrow">Akun</div>
                <h3>Logout</h3>
                <p>Keluar dari akun customer.</p>
            </a>
        </div>
    </div>
</div>

</body>
</html>