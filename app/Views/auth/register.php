<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-left">
        <div class="auth-brand">MALINI PARFUM</div>

        <div class="auth-copy">
            <div class="eyebrow">Premium Fragrance</div>
            <h1>Aroma favorit, lebih mudah ditemukan.</h1>
            <p>Daftar akun untuk mulai belanja parfum favorit dengan lebih mudah.</p>
        </div>
    </div>

    <div class="auth-right">
        <div class="auth-card">
            <div class="eyebrow">Akun Baru</div>
            <h2 style="margin:0;">Daftar Akun</h2>
            <div class="line"></div>

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="/register" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input class="input" type="text" name="name" placeholder="Nama Anda" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input class="input" type="email" name="email" placeholder="nama@email.com" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input class="input" type="password" name="password" placeholder="Minimal 8 karakter" required>
                </div>

                <button class="btn btn-full" type="submit">Daftar</button>
            </form>

            <p style="text-align:center; margin-top:22px; color:#777;">
                Sudah punya akun?
                <a href="/login" style="color:#b78300; font-weight:800;">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>