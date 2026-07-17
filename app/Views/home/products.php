<?php
    $currentPage = $pager->getCurrentPage('products');
    $pageCount = $pager->getPageCount('products');
    $selectedCategory = (string) ($categoryId ?? '');
    $searchKeyword = (string) ($search ?? '');

    $pageUrl = static function (int $page) use ($searchKeyword, $selectedCategory): string {
        $query = ['page_products' => $page];

        if ($searchKeyword !== '') {
            $query['q'] = $searchKeyword;
        }

        if ($selectedCategory !== '') {
            $query['category_id'] = $selectedCategory;
        }

        return '/produk?' . http_build_query($query);
    };
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Produk - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>

        <div class="nav-menu">
            <a href="/">Beranda</a>
            <a href="/produk">Produk</a>
            <a href="/#tentang">Tentang Kami</a>

            <?php if (session()->get('logged_in')) : ?>
                <?php if (session()->get('role') === 'admin') : ?>
                    <a href="/admin/dashboard">Dashboard</a>
                <?php else : ?>
                    <a href="/dashboard">Dashboard</a>
                    <a href="/cart">Keranjang</a>
                <?php endif; ?>

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
                            <strong><?= session()->get('role') === 'admin' ? 'Administrator' : 'Customer' ?></strong>
                        </div>
                        <a href="/logout" class="profile-logout">Logout</a>
                    </div>
                </details>
            <?php else : ?>
                <a href="/login">Masuk</a>
                <a href="/register" class="btn">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<section class="section section-white">
    <div class="container">
        <div class="catalog-head">
            <div>
                <div class="eyebrow">Katalog</div>
                <h1 class="section-title">Semua Produk</h1>
                <div class="line"></div>
            </div>
            <a href="/" class="btn btn-outline">Kembali ke Beranda</a>
        </div>

        <form action="/produk" method="get" class="catalog-filter">
            <div class="form-group">
                <label>Cari Produk</label>
                <input class="input" type="search" name="q" value="<?= esc($searchKeyword) ?>" placeholder="Nama atau deskripsi parfum">
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
                <a href="/produk" class="btn btn-outline">Reset</a>
            </div>
        </form>

        <?php if (!empty($products)) : ?>
            <div class="product-grid">
                <?php foreach ($products as $product) : ?>
                    <?php $image = !empty($product['image']) ? $product['image'] : 'dior.png'; ?>

                    <div class="product-card">
                        <img src="/assets/img/<?= esc($image) ?>" alt="<?= esc($product['name']) ?>">

                        <div class="product-body">
                            <span class="badge"><?= esc($product['category_name'] ?? 'Parfum') ?></span>

                            <h3 class="product-title">
                                <?= esc($product['name']) ?>
                            </h3>

                            <p class="product-desc">
                                <?= esc($product['description'] ?? 'Parfum premium dengan aroma elegan dan tahan lama.') ?>
                            </p>

                            <div class="price-label">Mulai dari</div>

                            <div class="price">
                                <?php if (!empty($product['min_price'])) : ?>
                                    Rp <?= number_format($product['min_price'], 0, ',', '.') ?>
                                <?php else : ?>
                                    Harga belum tersedia
                                <?php endif; ?>
                            </div>

                            <a href="/produk/<?= esc($product['id']) ?>" class="btn btn-full">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($pageCount > 1) : ?>
                <nav class="catalog-pagination" aria-label="Pagination produk">
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
            <div class="table-card">
                <p class="empty-text">Produk tidak ditemukan. Coba kata kunci atau kategori lain.</p>
                <a href="/produk" class="btn">Lihat Semua Produk</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<footer class="footer">
    <div class="container footer-grid">
        <div>
            <div class="logo">MALINI PARFUM</div>
            <p>
                Toko parfum premium di Purwakarta.
                Koleksi aroma terbaik untuk setiap momen.
            </p>
        </div>

        <div>
            <div class="eyebrow">Navigasi</div>
            <p><a href="/">Beranda</a></p>
            <p><a href="/produk">Produk</a></p>
            <p><a href="/#tentang">Tentang Kami</a></p>
        </div>

        <div>
            <div class="eyebrow">Kategori</div>
            <?php foreach ($categories as $category) : ?>
                <p><a href="/produk?category_id=<?= esc($category['id']) ?>"><?= esc($category['name']) ?></a></p>
            <?php endforeach; ?>
        </div>

        <div>
            <div class="eyebrow">Akun</div>

            <?php if (session()->get('logged_in')) : ?>
                <?php if (session()->get('role') === 'admin') : ?>
                    <p><a href="/admin/dashboard">Dashboard Admin</a></p>
                <?php else : ?>
                    <p><a href="/dashboard">Dashboard Customer</a></p>
                    <p><a href="/cart">Keranjang</a></p>
                <?php endif; ?>
            <?php else : ?>
                <p><a href="/login">Masuk</a></p>
                <p><a href="/register">Daftar</a></p>
            <?php endif; ?>
        </div>
    </div>
</footer>

</body>
</html>
