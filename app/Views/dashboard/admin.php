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
                <a href="/admin/categories">Kategori</a>
                <a href="/admin/products">Produk</a>
                <a href="/admin/transactions">Transaksi</a>
                <a href="/admin/reports">Laporan</a>
                <details class="notification-menu">
                    <summary class="nav-icon-button" aria-label="Notifikasi">
                        <span class="nav-icon" aria-hidden="true">&#128276;</span>
                        <?php if ($totalAdminNotifications > 0) : ?>
                            <span class="notification-count"><?= esc($totalAdminNotifications) ?></span>
                        <?php endif; ?>
                    </summary>
                    <div class="notification-panel">
                        <div class="notification-panel-head">
                            <strong>Perlu Ditindak</strong>
                            <?php if ($totalAdminNotifications > 0) : ?>
                                <span><?= esc($totalAdminNotifications) ?> item aktif</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($totalAdminNotifications > 0) : ?>
                            <div class="notification-summary">
                                <a href="/admin/transactions" class="notification-stat">
                                    <strong><?= esc($pendingTransactionCount) ?></strong>
                                    <span>Transaksi perlu dicek</span>
                                </a>
                                <div class="notification-stat danger">
                                    <strong><?= count($stockOutProducts) ?></strong>
                                    <span>Stok habis</span>
                                </div>
                                <div class="notification-stat warning">
                                    <strong><?= count($lowStockProducts) ?></strong>
                                    <span>Stok menipis</span>
                                </div>
                            </div>

                            <?php if (!empty($latestPendingTransactions)) : ?>
                                <div class="notification-list">
                                    <?php foreach ($latestPendingTransactions as $transaction) : ?>
                                        <a href="/admin/transactions/detail/<?= esc($transaction['id']) ?>" class="notification-item">
                                            <span>
                                                <strong><?= esc($transaction['invoice_number']) ?></strong>
                                                <?= esc($transaction['customer_name']) ?>
                                            </span>
                                            <small>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <a href="/admin/transactions" class="notification-action">Lihat semua transaksi</a>
                        <?php else : ?>
                            <p class="notification-empty">Tidak ada notifikasi baru.</p>
                        <?php endif; ?>
                    </div>
                </details>
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

    <div class="dashboard-header">
        <div class="container">
            <div class="eyebrow">Administrator Panel</div>
            <h1 style="margin:0;">Dashboard Administrator</h1>
            <p style="color:#bbb;">Selamat datang, <?= esc(session()->get('name')) ?></p>
        </div>
    </div>

    <div class="container">

        <?php if (!empty($stockOutProducts)) : ?>
            <div class="stock-alert danger">
                <strong>Notifikasi Stok Habis</strong>
                <p>
                    Ada <?= count($stockOutProducts) ?> varian produk yang stoknya habis.
                    Segera lakukan penambahan stok.
                </p>
            </div>
        <?php endif; ?>

        <?php if (!empty($lowStockProducts)) : ?>
            <div class="stock-alert warning">
                <strong>Notifikasi Stok Menipis</strong>
                <p>
                    Ada <?= count($lowStockProducts) ?> varian produk dengan stok menipis.
                </p>
            </div>
        <?php endif; ?>

        <div class="dashboard-card-grid admin-summary-grid">
            <div class="dashboard-card">
                <div class="eyebrow">Customer</div>
                <h3><?= esc($totalCustomers) ?></h3>
                <p>Total customer terdaftar.</p>
            </div>

            <div class="dashboard-card">
                <div class="eyebrow">Produk</div>
                <h3><?= esc($totalProducts) ?></h3>
                <p>Total produk parfum.</p>
            </div>

            <div class="dashboard-card">
                <div class="eyebrow">Transaksi</div>
                <h3><?= esc($totalTransactions) ?></h3>
                <p>Total transaksi customer.</p>
            </div>

            <div class="dashboard-card">
                <div class="eyebrow">Pendapatan</div>
                <h3>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h3>
                <p>Total dari transaksi yang sudah dibayar.</p>
            </div>
        </div>

        <div class="admin-dashboard-grid">

            <div class="table-card">
                <div class="section-mini-title chart-title-row">
                    <div>
                        <h3>Grafik Penjualan</h3>
                        <p>Progres penjualan berdasarkan transaksi paid dalam 4 bulan terakhir.</p>
                    </div>
                    <div class="chart-total">
                        <span>Total periode</span>
                        <strong>Rp <?= number_format(array_sum(array_column($salesChart, 'total')), 0, ',', '.') ?></strong>
                    </div>
                </div>

                <div class="sales-chart">
                    <?php foreach ($salesChart as $sale) : ?>
                        <?php
                            $height = $sale['progress'];
                            $barHeight = $height > 0 && $height < 8 ? 8 : $height;
                            $growthClass = $sale['growth'] >= 0 ? 'up' : 'down';
                            $growthPrefix = $sale['growth'] > 0 ? '+' : '';
                        ?>

                        <div class="sales-bar-item">
                            <div class="sales-progress-info">
                                <strong><?= esc($height) ?>%</strong>
                                <span class="growth-badge <?= esc($growthClass) ?>">
                                    <?= esc($growthPrefix . $sale['growth']) ?>%
                                </span>
                            </div>
                            <div class="sales-bar-wrapper">
                                <div class="sales-bar" style="height: <?= esc($barHeight) ?>%;"></div>
                            </div>
                            <small><?= esc($sale['label']) ?></small>
                            <strong class="sales-value">Rp <?= number_format($sale['total'], 0, ',', '.') ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="table-card">
                <div class="section-mini-title">
                    <h3>Stok Produk</h3>
                    <p>Daftar produk dengan stok habis dan stok menipis.</p>
                </div>

                <h4>Stok Habis</h4>

                <?php if (!empty($stockOutProducts)) : ?>
                    <table class="table stock-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Ukuran</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stockOutProducts as $item) : ?>
                                <tr>
                                    <td><?= esc($item['product_name']) ?></td>
                                    <td><?= esc($item['size']) ?></td>
                                    <td><span class="status-badge danger">Habis</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="empty-text">Tidak ada produk yang stoknya habis.</p>
                <?php endif; ?>

                <br>

                <h4>Stok Menipis</h4>

                <?php if (!empty($lowStockProducts)) : ?>
                    <table class="table stock-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Ukuran</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $item) : ?>
                                <tr>
                                    <td><?= esc($item['product_name']) ?></td>
                                    <td><?= esc($item['size']) ?></td>
                                    <td><span class="status-badge warning"><?= esc($item['stock']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="empty-text">Tidak ada produk dengan stok menipis.</p>
                <?php endif; ?>
            </div>

        </div>

        <div class="dashboard-card-grid admin-menu-grid">
            <a href="/admin/categories" class="dashboard-card">
                <div class="eyebrow">Master Data</div>
                <h3>Kelola Kategori</h3>
                <p>Mengelola kategori parfum.</p>
            </a>

            <a href="/admin/products" class="dashboard-card">
                <div class="eyebrow">Produk</div>
                <h3>Kelola Produk</h3>
                <p>Mengelola produk dan varian ukuran.</p>
            </a>

            <a href="/admin/transactions" class="dashboard-card">
                <div class="eyebrow">Transaksi</div>
                <h3>Kelola Transaksi</h3>
                <p>Verifikasi pembayaran dan status pesanan.</p>
            </a>

            <a href="/admin/reports" class="dashboard-card">
                <div class="eyebrow">Laporan</div>
                <h3>Laporan Penjualan</h3>
                <p>Melihat laporan transaksi penjualan.</p>
            </a>
        </div>
    </div>
</div>

</body>
</html>
