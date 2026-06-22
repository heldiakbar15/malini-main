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

    <div class="dashboard-header">
        <div class="container">
            <div class="eyebrow">Customer Panel</div>
            <h1 style="margin:0;">Selamat datang, <?= esc(session()->get('name')) ?></h1>
            <p style="color:#bbb;">Kelola aktivitas belanja parfum Anda dengan mudah.</p>
        </div>
    </div>

    <div class="container">
        <div class="dashboard-card-grid customer-summary-grid">
            <div class="dashboard-card">
                <div class="eyebrow">Transaksi</div>
                <h3><?= esc($totalTransactions) ?></h3>
                <p>Total pesanan yang pernah dibuat.</p>
            </div>

            <div class="dashboard-card">
                <div class="eyebrow">Perlu Dipantau</div>
                <h3><?= esc($pendingTransactions) ?></h3>
                <p>Pesanan atau pembayaran masih pending.</p>
            </div>

            <div class="dashboard-card">
                <div class="eyebrow">Selesai</div>
                <h3><?= esc($completedTransactions) ?></h3>
                <p>Pesanan yang sudah selesai.</p>
            </div>

            <div class="dashboard-card">
                <div class="eyebrow">Total Belanja</div>
                <h3>Rp <?= number_format($totalSpending, 0, ',', '.') ?></h3>
                <p>Total dari transaksi yang sudah dibayar.</p>
            </div>
        </div>

        <div class="customer-dashboard-grid">
            <div class="table-card">
                <div class="section-mini-title">
                    <h3>Aktivitas Terbaru</h3>
                    <p>Ringkasan pesanan terakhir Anda di Malini Parfum.</p>
                </div>

                <?php if (!empty($latestTransactions)) : ?>
                    <table class="table stock-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latestTransactions as $transaction) : ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($transaction['invoice_number']) ?></strong><br>
                                        <small><?= esc(date('d/m/Y H:i', strtotime($transaction['created_at']))) ?></small>
                                    </td>
                                    <td>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="status-badge <?= $transaction['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                            <?= esc($transaction['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="/transactions/detail/<?= esc($transaction['id']) ?>" class="btn btn-outline">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="empty-text">Belum ada transaksi. Mulai pilih parfum favorit Anda.</p>
                <?php endif; ?>
            </div>

            <div class="table-card customer-side-panel">
                <div class="section-mini-title">
                    <h3>Belanja Cepat</h3>
                    <p>Akses cepat untuk melanjutkan aktivitas belanja.</p>
                </div>

                <div class="customer-quick-list">
                    <a href="/cart" class="customer-quick-item">
                        <span>Keranjang</span>
                        <strong><?= esc($cartItemCount) ?> item</strong>
                    </a>
                    <a href="/transactions" class="customer-quick-item">
                        <span>Riwayat</span>
                        <strong>Lihat pesanan</strong>
                    </a>
                    <a href="/#produk" class="customer-quick-item">
                        <span>Produk</span>
                        <strong>Belanja lagi</strong>
                    </a>
                </div>
            </div>
        </div>

        <div class="dashboard-card-grid customer-menu-grid">
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

        </div>
    </div>
</div>

</body>
</html>
