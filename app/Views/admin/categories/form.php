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
    <div class="container admin-form-container">
        <div class="admin-breadcrumb">
            <a href="/admin/dashboard">Dashboard</a>
            <span>/</span>
            <a href="/admin/categories">KATEGORI</a>
            <span>/</span>
            <strong><?= esc(strtoupper($title)) ?></strong>
        </div>
        <h1 class="section-title"><?= esc($title) ?></h1>
        <div class="line"></div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="table-card">
            <form action="<?= esc($action) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input class="input" type="text" name="name" value="<?= esc(old('name', $category['name'] ?? '')) ?>" required>
                </div>

                <div class="form-actions">
                    <a href="/admin/categories" class="btn btn-outline">Kembali</a>
                    <button type="submit" class="btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</section>

</body>
</html>
