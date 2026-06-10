<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>
        <div class="nav-menu">
            <a href="/">Produk</a>
            <a href="/dashboard">Dashboard</a>
            <a href="/logout" class="btn">Logout</a>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <div class="eyebrow">Belanja</div>
        <h1 class="section-title">Keranjang Belanja</h1>
        <div class="line"></div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="table-card">
            <?php if (!empty($cart)) : ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Ukuran</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Stok</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
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
                                <td>
                                    <form action="/cart/update" method="post" style="display:flex; gap:8px;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="variant_id" value="<?= esc($item['variant_id']) ?>">
                                        <input 
                                            class="input"
                                            type="number" 
                                            name="quantity" 
                                            value="<?= esc($item['quantity']) ?>" 
                                            min="1" 
                                            max="<?= esc($item['stock']) ?>"
                                            style="width:80px;"
                                        >
                                        <button class="btn" type="submit">Update</button>
                                    </form>
                                </td>
                                <td><?= esc($item['stock']) ?></td>
                                <td><strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong></td>
                                <td>
                                    <a 
                                        href="/cart/remove/<?= esc($item['variant_id']) ?>" 
                                        onclick="return confirm('Hapus produk ini?')"
                                        style="color:#b3261e; font-weight:800;"
                                    >
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="5"><strong>Total</strong></td>
                            <td colspan="2">
                                <strong>Rp <?= number_format($grandTotal, 0, ',', '.') ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <br>

                <a href="/cart/clear" class="btn btn-outline" onclick="return confirm('Kosongkan keranjang?')">
                    Kosongkan Keranjang
                </a>

                <a href="/checkout" class="btn">
                    Checkout
                </a>
            <?php else : ?>
                <p>Keranjang belanja masih kosong.</p>
                <a href="/" class="btn">Belanja Sekarang</a>
            <?php endif; ?>
        </div>
    </div>
</section>

</body>
</html>