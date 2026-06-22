<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin Malini Parfum</title>
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
                    <strong>PRODUK</strong>
                </div>
                <h1 class="section-title">Kelola Produk</h1>
            </div>
            <a href="/admin/products/create" class="btn">Tambah Produk</a>
        </div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="table-card">
            <?php if (!empty($products)) : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga Mulai</th>
                            <th>Varian</th>
                            <th>Total Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product) : ?>
                            <?php $productImage = !empty($product['image']) ? $product['image'] : 'dior.png'; ?>
                            <tr>
                                <td>
                                    <div class="admin-product-cell">
                                        <img src="/assets/img/<?= esc($productImage) ?>" alt="<?= esc($product['name']) ?>">
                                        <div>
                                            <strong><?= esc($product['name']) ?></strong>
                                            <small><?= esc($product['description'] ?: '-') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($product['category_name'] ?? 'Tanpa kategori') ?></td>
                                <td>
                                    <?php if ($product['min_price'] !== null) : ?>
                                        Rp <?= number_format($product['min_price'], 0, ',', '.') ?>
                                    <?php else : ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($product['variant_count']) ?></td>
                                <td><?= esc($product['total_stock']) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/products/edit/<?= esc($product['id']) ?>" class="btn btn-outline">Edit</a>
                                        <form action="/admin/products/delete/<?= esc($product['id']) ?>" method="post" onsubmit="return confirm('Hapus produk ini beserta variannya?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="empty-text">Belum ada produk.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>
