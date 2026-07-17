<?php
    $currentPage = $pager->getCurrentPage('admin_products');
    $pageCount = $pager->getPageCount('admin_products');
    $selectedCategory = (string) ($categoryId ?? '');
    $searchKeyword = (string) ($search ?? '');

    $pageUrl = static function (int $page) use ($searchKeyword, $selectedCategory): string {
        $query = ['page_admin_products' => $page];

        if ($searchKeyword !== '') {
            $query['q'] = $searchKeyword;
        }

        if ($selectedCategory !== '') {
            $query['category_id'] = $selectedCategory;
        }

        return '/admin/products?' . http_build_query($query);
    };
?>

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

        <form action="/admin/products" method="get" class="catalog-filter admin-product-filter">
            <div class="form-group">
                <label>Cari Produk</label>
                <input class="input" type="search" name="q" value="<?= esc($searchKeyword) ?>" placeholder="Nama atau deskripsi produk">
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="category_id">
                    <option value="">Semua kategori</option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?= esc($category['id']) ?>" <?= $selectedCategory === (string) $category['id'] ? 'selected' : '' ?>>
                            <?= esc($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="catalog-filter-actions">
                <button type="submit" class="btn">Terapkan</button>
                <a href="/admin/products" class="btn btn-outline">Reset</a>
            </div>
        </form>

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
                                        <form action="/admin/products/toggle-featured/<?= esc($product['id']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button
                                                type="submit"
                                                class="featured-button <?= ((int) ($product['is_featured'] ?? 0) === 1) ? 'active' : '' ?>"
                                                title="<?= ((int) ($product['is_featured'] ?? 0) === 1) ? 'Hapus dari Produk Unggulan' : 'Jadikan Produk Unggulan' ?>"
                                                aria-label="<?= ((int) ($product['is_featured'] ?? 0) === 1) ? 'Hapus dari Produk Unggulan' : 'Jadikan Produk Unggulan' ?>"
                                            >
                                                ★
                                            </button>
                                        </form>
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

                <?php if ($pageCount > 1) : ?>
                    <nav class="catalog-pagination" aria-label="Pagination produk admin">
                        <?php if ($currentPage > 1) : ?>
                            <a href="<?= esc($pageUrl($currentPage - 1)) ?>" class="btn btn-outline">Sebelumnya</a>
                        <?php endif; ?>

                        <div class="pagination-pages">
                            <?php for ($page = 1; $page <= $pageCount; $page++) : ?>
                                <a
                                    href="<?= esc($pageUrl($page)) ?>"
                                    class="pagination-link <?= $page === $currentPage ? 'active' : '' ?>"
                                >
                                    <?= esc($page) ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($currentPage < $pageCount) : ?>
                            <a href="<?= esc($pageUrl($currentPage + 1)) ?>" class="btn btn-outline">Berikutnya</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php else : ?>
                <p class="empty-text">Produk tidak ditemukan. Coba kata kunci atau kategori lain.</p>
                <a href="/admin/products" class="btn">Reset Filter</a>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>
