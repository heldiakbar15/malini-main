<?php
    $oldSizes = old('variant_size');
    $oldPrices = (array) old('variant_price');
    $oldStocks = (array) old('variant_stock');

    if (is_array($oldSizes)) {
        $variantRows = [];

        foreach ($oldSizes as $index => $size) {
            $variantRows[] = [
                'size'  => $size,
                'price' => $oldPrices[$index] ?? '',
                'stock' => $oldStocks[$index] ?? '',
            ];
        }
    } else {
        $variantRows = $variants;
    }

    while (count($variantRows) < 3) {
        $variantRows[] = ['size' => '', 'price' => '', 'stock' => ''];
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?> - Admin Malini Parfum</title>
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
        <div class="admin-breadcrumb">
            <a href="/admin/dashboard">Dashboard</a>
            <span>/</span>
            <a href="/admin/products">PRODUK</a>
            <span>/</span>
            <strong><?= esc(strtoupper($title)) ?></strong>
        </div>
        <h1 class="section-title"><?= esc($title) ?></h1>
        <div class="line"></div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form action="<?= esc($action) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="admin-form-grid">
                <div class="table-card">
                    <div class="section-mini-title">
                        <h3>Data Produk</h3>
                        <p>Isi informasi utama produk parfum.</p>
                    </div>

                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input class="input" type="text" name="name" value="<?= esc(old('name', $product['name'] ?? '')) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category_id">
                            <option value="">Tanpa kategori</option>
                            <?php foreach ($categories as $category) : ?>
                                <?php $selectedCategory = old('category_id', $product['category_id'] ?? ''); ?>
                                <option value="<?= esc($category['id']) ?>" <?= (string) $selectedCategory === (string) $category['id'] ? 'selected' : '' ?>>
                                    <?= esc($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Foto Produk</label>

                        <?php if (!empty($product['image'])) : ?>
                            <div class="product-image-preview">
                                <img src="/assets/img/<?= esc($product['image']) ?>" alt="<?= esc($product['name'] ?? 'Foto produk') ?>">
                                <div>
                                    <strong>Foto saat ini</strong>
                                    <small><?= esc($product['image']) ?></small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <input class="input" type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <small>Upload JPG, PNG, atau WEBP maksimal 2 MB. Jika dikosongkan saat edit, foto lama tetap digunakan.</small>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description"><?= esc(old('description', $product['description'] ?? '')) ?></textarea>
                    </div>
                </div>

                <div class="table-card">
                    <div class="section-mini-title">
                        <h3>Varian Produk</h3>
                        <p>Minimal isi satu varian. Kosongkan baris yang tidak dipakai.</p>
                    </div>

                    <div class="variant-admin-list">
                        <?php foreach ($variantRows as $row) : ?>
                            <div class="variant-admin-row">
                                <div class="form-group">
                                    <label>Ukuran</label>
                                    <input class="input" type="text" name="variant_size[]" value="<?= esc($row['size'] ?? '') ?>" placeholder="30ml">
                                </div>
                                <div class="form-group">
                                    <label>Harga</label>
                                    <input class="input" type="number" name="variant_price[]" value="<?= esc($row['price'] ?? '') ?>" min="0" step="1000" placeholder="150000">
                                </div>
                                <div class="form-group">
                                    <label>Stok</label>
                                    <input class="input" type="number" name="variant_stock[]" value="<?= esc($row['stock'] ?? '') ?>" min="0" step="1" placeholder="10">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <a href="/admin/products" class="btn btn-outline">Kembali</a>
                        <button type="submit" class="btn">Simpan Produk</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

</body>
</html>
