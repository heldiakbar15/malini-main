<?php
    $productImage = !empty($product['image']) ? $product['image'] : 'dior.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($product['name']) ?> - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>

        <div class="nav-menu">
            <a href="/">Beranda</a>
            <a href="/#produk">Produk</a>
            <a href="/#tentang">Tentang Kami</a>

            <?php if (session()->get('logged_in')) : ?>
                <a href="/cart">Keranjang</a>
                <a href="/dashboard">Dashboard</a>
                <a href="/logout" class="btn">Logout</a>
            <?php else : ?>
                <a href="/login">Masuk</a>
                <a href="/register" class="btn">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="section-light">
    <div class="container breadcrumb">
        Beranda › Produk › <?= esc($product['name']) ?>
    </div>
</div>

<section class="detail-section">
    <div class="container detail-grid">

        <div class="detail-image">
            <img src="/assets/img/<?= esc($productImage) ?>" alt="<?= esc($product['name']) ?>">
        </div>

        <div class="detail-content">
            <span class="badge">Parfum Premium</span>

            <h1 class="section-title"><?= esc($product['name']) ?></h1>
            <div class="line"></div>

            <p class="detail-desc">
                <?= esc($product['description']) ?>
            </p>

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="/cart/add" method="post">
                <?= csrf_field() ?>

                <input type="hidden" name="product_id" value="<?= esc($product['id']) ?>">

                <div class="variant-box">
                    <strong>PILIHAN UKURAN</strong>

                    <br><br>

                    <?php if (!empty($variants)) : ?>
                        <?php foreach ($variants as $variant) : ?>
                            <label class="variant-item">
                                <div class="variant-left">
                                    <input 
                                        type="radio" 
                                        name="variant_id" 
                                        value="<?= esc($variant['id']) ?>" 
                                        required
                                    >

                                    <div>
                                        <strong><?= esc($variant['size']) ?></strong>
                                        <span>Stok: <?= esc($variant['stock']) ?></span>
                                    </div>
                                </div>

                                <strong style="color:#b78300;">
                                    Rp <?= number_format($variant['price'], 0, ',', '.') ?>
                                </strong>
                            </label>
                        <?php endforeach; ?>

                        <div class="form-group" style="margin-top:18px;">
                            <label>Jumlah</label>
                            <input 
                                class="input" 
                                type="number" 
                                name="quantity" 
                                value="1" 
                                min="1" 
                                required
                            >
                        </div>
                    <?php else : ?>
                        <p>Varian produk belum tersedia.</p>
                    <?php endif; ?>
                </div>

                <div class="detail-actions">
                    <?php if (session()->get('logged_in')) : ?>
                        <button type="submit" class="btn">
                            Tambah ke Keranjang
                        </button>
                    <?php else : ?>
                        <a href="/login" class="btn">
                            Login untuk Membeli
                        </a>
                    <?php endif; ?>

                    <a href="/" class="btn btn-outline">
                        ← Kembali
                    </a>
                </div>
            </form>
        </div>

    </div>
</section>

</body>
</html>