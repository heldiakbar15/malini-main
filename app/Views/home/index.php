<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>

        <div class="nav-menu">
            <a href="/">Beranda</a>
            <a href="#produk">Produk</a>
            <a href="#tentang">Tentang Kami</a>

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

<section class="hero">
    <div class="container hero-grid">
        <div>
            <div class="eyebrow">Premium Fragrance</div>
            <h1>Malini Perfume Collection</h1>
            <p>
                Temukan aroma terbaik untuk menemani setiap langkah dan momen spesial Anda.
                Pilihan parfum pria, wanita, dan unisex tersedia dalam berbagai ukuran.
            </p>
            <br>
            <a href="#produk" class="btn">Jelajahi Produk →</a>
        </div>

        <div class="hero-gallery">
            <img src="/assets/img/dior.png" alt="Dior Sauvage">
            <img src="/assets/img/creed.png" alt="Creed Aventus">
            <img src="/assets/img/black-opium.png" alt="Black Opium">
            <img src="/assets/img/baccarat.png" alt="Baccarat Rouge">
        </div>
    </div>
</section>

<section class="section section-light">
    <div class="container">
        <div class="eyebrow">Kategori</div>
        <h2 class="section-title">Kategori Parfum</h2>
        <div class="line"></div>

        <div class="category-grid">
            <div class="category-card">
                <div class="category-icon">★</div>
                <strong>Pria</strong>
            </div>

            <div class="category-card">
                <div class="category-icon">★</div>
                <strong>Wanita</strong>
            </div>

            <div class="category-card">
                <div class="category-icon">★</div>
                <strong>Unisex</strong>
            </div>
        </div>
    </div>
</section>

<section class="section section-white" id="produk">
    <div class="container">
        <div class="eyebrow">Terlaris</div>
        <h2 class="section-title">Produk Unggulan</h2>
        <div class="line"></div>

        <?php if (!empty($products)) : ?>
            <div class="product-grid">
                <?php foreach ($products as $product) : ?>
                    <?php
                        $image = !empty($product['image']) ? $product['image'] : 'dior.png';
                    ?>

                    <div class="product-card">
                        <img src="/assets/img/<?= esc($image) ?>" alt="<?= esc($product['name']) ?>">

                        <div class="product-body">
                            <span class="badge">Parfum</span>

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
        <?php else : ?>
            <div class="table-card">
                <p>Produk belum tersedia.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section home-about" id="tentang">
    <div class="container home-about-grid">
        <div>
            <div class="eyebrow">Tentang Kami</div>
            <h2 class="section-title">Malini Parfum</h2>
            <div class="line"></div>

            <p style="max-width:650px; color:#bfbfbf; line-height:1.8;">
                Malini Parfum menyediakan berbagai pilihan aroma pria, wanita, dan unisex dengan kualitas terbaik.
                Kami berkomitmen memberikan produk berkualitas dengan harga terjangkau serta pelayanan yang memuaskan
                bagi setiap pelanggan.
            </p>
        </div>

        <div>
            <div class="about-feature">
                <strong>Produk Premium</strong>
                <p>Koleksi parfum pilihan dengan aroma elegan dan tahan lama.</p>
            </div>

            <div class="about-feature">
                <strong>Varian Ukuran</strong>
                <p>Tersedia ukuran 10 ml, 30 ml, dan 50 ml sesuai kebutuhan pelanggan.</p>
            </div>

            <div class="about-feature">
                <strong>Transaksi Mudah</strong>
                <p>Pelanggan dapat memilih produk, memasukkan ke keranjang, dan melakukan checkout.</p>
            </div>
        </div>
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
            <p><a href="#produk">Produk</a></p>
            <p><a href="#tentang">Tentang Kami</a></p>
        </div>

        <div>
            <div class="eyebrow">Kategori</div>
            <p>Parfum Pria</p>
            <p>Parfum Wanita</p>
            <p>Unisex</p>
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
