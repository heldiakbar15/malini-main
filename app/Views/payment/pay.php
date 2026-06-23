<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - Malini Parfum</title>
    <link rel="stylesheet" href="/assets/css/style.css">

    <?php if ($isProduction) : ?>
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="<?= esc($clientKey) ?>"></script>
    <?php else : ?>
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= esc($clientKey) ?>"></script>
    <?php endif; ?>
</head>
<body>

<nav class="navbar">
    <div class="container navbar-inner">
        <a href="/" class="logo">MALINI PARFUM</a>
        <div class="nav-menu">
            <a href="/">Produk</a>
            <a href="/cart">Keranjang</a>
            <a href="/transactions">Riwayat</a>
            <a href="/dashboard">Dashboard</a>
        </div>
    </div>
</nav>

<section class="section">
    <div class="container">
        <div class="eyebrow">Midtrans Payment</div>
        <h1 class="section-title">Pembayaran Pesanan</h1>
        <div class="line"></div>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert-error">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="payment-card">
            <div class="payment-card-head">
                <div>
                    <span>Invoice</span>
                    <strong><?= esc($transaction['invoice_number']) ?></strong>
                </div>

                <?php if (($transaction['payment_status'] ?? '') === 'paid') : ?>
                    <span class="status-badge success">Lunas</span>
                <?php elseif (($transaction['payment_status'] ?? '') === 'failed') : ?>
                    <span class="status-badge danger">Waktu Habis</span>
                <?php else : ?>
                    <span class="status-badge warning">Belum Dibayar</span>
                <?php endif; ?>
            </div>

            <div class="payment-total-box">
                <span>Total Pembayaran</span>
                <strong>Rp <?= number_format((int) $transaction['total_amount'], 0, ',', '.') ?></strong>
            </div>

            <div class="payment-info-list">
                <div class="payment-info-row">
                    <span>Nama Penerima</span>
                    <strong><?= esc($transaction['customer_name']) ?></strong>
                </div>

                <div class="payment-info-row">
                    <span>Status Pesanan</span>
                    <strong><?= esc($transaction['order_status'] ?? 'pending') ?></strong>
                </div>

                <div class="payment-info-row">
                    <span>Metode Pembayaran</span>
                    <strong>Midtrans Payment Gateway</strong>
                </div>
            </div>

            <?php if (($transaction['payment_status'] ?? '') === 'pending') : ?>

                <div class="alert-success">
                    Silakan klik tombol di bawah untuk memilih metode pembayaran di Midtrans.
                </div>

                <div class="payment-actions">
                    <button id="pay-button" class="btn btn-full">
                        Bayar Sekarang
                    </button>

                    <a href="/transactions/detail/<?= esc($transaction['id']) ?>" class="btn btn-outline btn-full">
                        Kembali ke Detail Transaksi
                    </a>
                </div>

            <?php elseif (($transaction['payment_status'] ?? '') === 'paid') : ?>

                <div class="alert-success">
                    Pembayaran transaksi ini sudah berhasil.
                </div>

                <div class="payment-actions">
                    <a href="/transactions/detail/<?= esc($transaction['id']) ?>" class="btn btn-full">
                        Lihat Detail Transaksi
                    </a>
                </div>

            <?php else : ?>

                <div class="alert-error">
                    Waktu pembayaran sudah habis. Transaksi ini tidak dapat dibayar lagi.
                </div>

                <div class="payment-actions payment-actions-inline">
                    <a href="/" class="btn">
                        Buat Pesanan Baru
                    </a>

                    <a href="/transactions/detail/<?= esc($transaction['id']) ?>" class="btn btn-outline">
                        Kembali ke Detail Transaksi
                    </a>
                </div>

            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (($transaction['payment_status'] ?? '') === 'pending') : ?>
<script>
    const payButton = document.getElementById('pay-button');

    if (payButton) {
        payButton.addEventListener('click', function () {
            snap.pay('<?= esc($transaction['snap_token']) ?>', {
                onSuccess: function(result) {
                    window.location.href = '/payment/finish';
                },
                onPending: function(result) {
                    window.location.href = '/payment/unfinish';
                },
                onError: function(result) {
                    window.location.href = '/payment/error';
                },
                onClose: function() {
                    alert('Anda menutup popup pembayaran sebelum menyelesaikan pembayaran.');
                }
            });
        });
    }
</script>
<?php endif; ?>

</body>
</html>
