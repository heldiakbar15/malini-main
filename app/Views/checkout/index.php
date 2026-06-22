<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>
        <div class="nav-menu">
            <a href="/">Produk</a>
            <a href="/cart">Keranjang</a>
            <a href="/dashboard">Dashboard</a>
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
                        <strong>Customer</strong>
                    </div>
                    <a href="/logout" class="profile-logout">Logout</a>
                </div>
            </details>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <div class="eyebrow">Pembayaran</div>
        <h1 class="section-title">Checkout Pesanan</h1>
        <div class="line"></div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="detail-grid">
            <div class="table-card">
                <h3>Ringkasan Pesanan</h3>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Ukuran</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grandTotal = 0;
                        foreach ($cart as $item) : 
                            $grandTotal += $item['subtotal'];
                        ?>
                            <tr>
                                <td><strong><?= esc($item['name']) ?></strong></td>
                                <td><?= esc($item['size']) ?></td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td><?= esc($item['quantity']) ?></td>
                                <td><strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="4"><strong>Total Pembayaran</strong></td>
                            <td><strong>Rp <?= number_format($grandTotal, 0, ',', '.') ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-card">
                <h3>Data Pengiriman</h3>

                <form action="/checkout/process" method="post">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>Nama Penerima</label>
                        <input class="input" type="text" name="customer_name" value="<?= esc(session()->get('name')) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>No. HP</label>
                        <input class="input" type="text" name="customer_phone" placeholder="Contoh: 081234567890">
                    </div>

                    <div class="form-group">
                        <label>Alamat Pengiriman</label>
                        <textarea name="shipping_address" rows="5" required placeholder="Masukkan alamat lengkap"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <input class="input" type="text" value="QRIS OVO" readonly>
                        <input type="hidden" name="payment_method" value="QRIS OVO">
                    </div>

                    <button class="btn btn-full" type="submit" onclick="return confirm('Proses checkout sekarang?')">
                        Buat Pesanan
                    </button>

                    <br><br>

                    <a href="/cart" class="btn btn-outline btn-full">Kembali ke Keranjang</a>
                </form>
            </div>
        </div>
    </div>
</section>

</body>
</html>
