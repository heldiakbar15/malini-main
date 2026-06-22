<?php
    $query = http_build_query([
        'type'  => $type,
        'date'  => $date,
        'month' => $month,
    ]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Admin Malini Parfum</title>
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
        <div class="admin-page-head">
            <div>
                <div class="admin-breadcrumb">
                    <a href="/admin/dashboard">Dashboard</a>
                    <span>/</span>
                    <strong>LAPORAN</strong>
                </div>
                <h1 class="section-title">Laporan Penjualan</h1>
                <p class="report-subtitle">Preview penjualan paid untuk periode <?= esc($periodLabel) ?>.</p>
            </div>
            <div class="report-export-actions">
                <a href="/admin/reports/export-excel?<?= esc($query) ?>" class="btn btn-outline">Export Excel</a>
                <a href="/admin/reports/export-pdf?<?= esc($query) ?>" class="btn btn-dark">Export PDF</a>
            </div>
        </div>

        <div class="table-card report-filter-card">
            <form action="/admin/reports" method="get" class="report-filter-form">
                <div class="form-group">
                    <label>Jenis Laporan</label>
                    <select name="type" id="reportType">
                        <option value="day" <?= $type === 'day' ? 'selected' : '' ?>>Per Hari</option>
                        <option value="month" <?= $type === 'month' ? 'selected' : '' ?>>Per Bulan</option>
                    </select>
                </div>

                <div class="form-group report-date-field">
                    <label>Tanggal</label>
                    <input class="input" type="date" name="date" value="<?= esc($date) ?>">
                </div>

                <div class="form-group report-month-field">
                    <label>Bulan</label>
                    <input class="input" type="month" name="month" value="<?= esc($month) ?>">
                </div>

                <button type="submit" class="btn">Tampilkan</button>
            </form>
        </div>

        <div class="report-summary-grid">
            <div class="dashboard-card report-summary-card">
                <div class="eyebrow">Total Penjualan</div>
                <h3>Rp <?= number_format($totalRevenue, 0, ',', '.') ?></h3>
                <p>Akumulasi transaksi paid.</p>
            </div>
            <div class="dashboard-card report-summary-card">
                <div class="eyebrow">Transaksi</div>
                <h3><?= esc($totalTransactions) ?></h3>
                <p>Jumlah invoice paid.</p>
            </div>
            <div class="dashboard-card report-summary-card">
                <div class="eyebrow">Produk Terjual</div>
                <h3><?= esc($totalItems) ?></h3>
                <p>Total item dari detail transaksi.</p>
            </div>
            <div class="dashboard-card report-summary-card">
                <div class="eyebrow">Rata-rata</div>
                <h3>Rp <?= number_format($averageRevenue, 0, ',', '.') ?></h3>
                <p>Rata-rata nilai transaksi.</p>
            </div>
        </div>

        <div class="admin-form-grid report-grid">
            <div class="table-card">
                <div class="section-mini-title">
                    <h3>Preview Transaksi</h3>
                    <p>Detail transaksi yang sudah dibayar pada periode laporan.</p>
                </div>

                <?php if (!empty($transactions)) : ?>
                    <table class="table report-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Produk</th>
                                <th>Item</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($transactions as $transaction) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc(date('d/m/Y H:i', strtotime($transaction['created_at']))) ?></td>
                                    <td><strong><?= esc($transaction['invoice_number']) ?></strong></td>
                                    <td><?= esc($transaction['customer_name']) ?></td>
                                    <td><?= esc($transaction['product_summary'] ?: '-') ?></td>
                                    <td><?= esc($transaction['total_items']) ?></td>
                                    <td><strong>Rp <?= number_format($transaction['total_amount'], 0, ',', '.') ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="empty-text">Tidak ada transaksi paid pada periode ini.</p>
                <?php endif; ?>
            </div>

            <div class="table-card">
                <div class="section-mini-title">
                    <h3>Produk Terlaris</h3>
                    <p>Rekap produk dan varian berdasarkan kuantitas serta subtotal.</p>
                </div>

                <?php if (!empty($productRows)) : ?>
                    <table class="table stock-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productRows as $row) : ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($row['product_name']) ?></strong><br>
                                        <small><?= esc($row['variant_size']) ?></small>
                                    </td>
                                    <td><?= esc($row['total_qty']) ?></td>
                                    <td>Rp <?= number_format($row['total_sales'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="empty-text">Belum ada produk terjual pada periode ini.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
    const reportType = document.getElementById('reportType');
    const dateField = document.querySelector('.report-date-field');
    const monthField = document.querySelector('.report-month-field');

    function syncReportFields() {
        const isMonth = reportType.value === 'month';
        dateField.style.display = isMonth ? 'none' : 'block';
        monthField.style.display = isMonth ? 'block' : 'none';
    }

    reportType.addEventListener('change', syncReportFields);
    syncReportFields();
</script>

</body>
</html>
