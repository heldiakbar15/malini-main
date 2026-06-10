<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="dashboard">
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

    <div class="dashboard-header">
        <div class="container">
            <div class="eyebrow">Administrator Panel</div>
            <h1 style="margin:0;">Dashboard Administrator</h1>
            <p style="color:#bbb;">Selamat datang, <?= esc(session()->get('name')) ?></p>
        </div>
    </div>

    <div class="container">
        <div class="dashboard-card-grid">
            <a href="#" class="dashboard-card">
                <div class="eyebrow">Master Data</div>
                <h3>Kelola Kategori</h3>
                <p>Mengelola kategori parfum.</p>
            </a>

            <a href="#" class="dashboard-card">
                <div class="eyebrow">Produk</div>
                <h3>Kelola Produk</h3>
                <p>Mengelola produk dan varian ukuran.</p>
            </a>

            <a href="/admin/transactions" class="dashboard-card">
                <div class="eyebrow">Transaksi</div>
                <h3>Kelola Transaksi</h3>
                <p>Verifikasi pembayaran dan status pesanan.</p>
            </a>

            <a href="#" class="dashboard-card">
                <div class="eyebrow">Laporan</div>
                <h3>Laporan Penjualan</h3>
                <p>Melihat laporan transaksi penjualan.</p>
            </a>
        </div>
    </div>
</div>

</body>
</html>