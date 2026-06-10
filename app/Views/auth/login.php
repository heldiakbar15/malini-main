<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-left">
        <div class="auth-brand">MALINI PARFUM</div>

        <div class="auth-copy">
            <div class="eyebrow">Premium Fragrance</div>
            <h1>Aroma favorit, lebih mudah ditemukan.</h1>
            <p>Masuk atau daftar untuk menikmati pengalaman belanja parfum yang lebih personal dan nyaman.</p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-card">
            <div class="eyebrow">Selamat Datang</div>
            <h2 style="margin:0;">Masuk ke Akun</h2>
            <div class="line"></div>

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')) : ?>
                <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <form action="/login" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Email</label>
                    <input class="input" type="email" name="email" placeholder="nama@email.com" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input class="input" type="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button class="btn btn-full" type="submit">Masuk</button>
            </form>

            <p style="text-align:center; margin-top:22px; color:#777;">
                Belum punya akun?
                <a href="/register" style="color:#b78300; font-weight:800;">Daftar sekarang</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>