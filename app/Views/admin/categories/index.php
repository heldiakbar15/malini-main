<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori - Admin Malini Parfum</title>
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
                    <strong>KATEGORI</strong>
                </div>
                <h1 class="section-title">Kelola Kategori</h1>
            </div>
            <a href="/admin/categories/create" class="btn">Tambah Kategori</a>
        </div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="table-card">
            <?php if (!empty($categories)) : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($categories as $category) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= esc($category['name']) ?></strong></td>
                                <td><?= esc($category['created_at'] ?? '-') ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/categories/edit/<?= esc($category['id']) ?>" class="btn btn-outline">Edit</a>
                                        <form action="/admin/categories/delete/<?= esc($category['id']) ?>" method="post" onsubmit="return confirm('Hapus kategori ini? Produk terkait akan menjadi tanpa kategori.');">
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
                <p class="empty-text">Belum ada kategori.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>
