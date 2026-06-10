Berikut **README.md** yang bisa langsung kamu copy ke file `README.md` di root project GitHub kamu.

````markdown
# Malini Parfum

Malini Parfum adalah aplikasi sistem penjualan parfum berbasis web yang dibuat menggunakan **CodeIgniter 4**. Aplikasi ini memiliki fitur katalog produk, detail produk, varian ukuran parfum, keranjang belanja, checkout, transaksi customer, serta panel admin untuk mengelola transaksi.

## Fitur Aplikasi

### Customer
- Melihat daftar produk parfum
- Melihat detail produk
- Memilih varian ukuran parfum
  - 10 ml
  - 30 ml
  - 50 ml
- Register akun
- Login akun
- Menambahkan produk ke keranjang
- Mengubah jumlah produk di keranjang
- Menghapus produk dari keranjang
- Checkout pesanan
- Melihat riwayat transaksi
- Melihat detail transaksi
- Logout

### Admin
- Login admin
- Masuk ke dashboard admin
- Melihat daftar transaksi customer
- Melihat detail transaksi
- Mengubah status pembayaran
- Mengubah status pesanan
- Logout

## Teknologi yang Digunakan

- PHP
- CodeIgniter 4
- MySQL
- HTML
- CSS
- Laragon / XAMPP
- Composer

## Requirement

Pastikan laptop sudah terinstall:

- PHP versi 8.1 atau lebih baru
- Composer
- MySQL
- Laragon / XAMPP
- Git

## Cara Install Project

### 1. Clone Repository

```bash
git clone https://github.com/heldiakbar15/malini-main.git
````

Masuk ke folder project:

```bash
cd malini-main
```

### 2. Install Dependency Composer

Jalankan perintah:

```bash
composer install
```

### 3. Buat File `.env`

Copy file `env` menjadi `.env`.

Jika menggunakan terminal:

```bash
copy env .env
```

Atau buat manual file baru bernama:

```text
.env
```

Lalu isi konfigurasi berikut:

```env
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = db_malini_parfum
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

Jika menggunakan Laragon, biasanya:

```text
username: root
password: 
```

Jika menggunakan XAMPP, biasanya juga:

```text
username: root
password: 
```

## 4. Buat Database

Buka phpMyAdmin atau HeidiSQL, lalu buat database baru:

```sql
CREATE DATABASE db_malini_parfum;
```

## 5. Jalankan Migration

Jalankan perintah:

```bash
php spark migrate
```

Perintah ini akan membuat tabel database yang dibutuhkan aplikasi.

## 6. Jalankan Seeder User

Untuk membuat akun admin dan customer demo, jalankan:

```bash
php spark db:seed UserSeeder
```

Akun default:

### Admin

```text
Email    : admin@gmail.com
Password : admin123
```

### Customer

```text
Email    : farhan@gmail.com
Password : farhan123
```

## 7. Tambahkan Data Produk Awal

Jika data produk belum tersedia, jalankan SQL berikut di phpMyAdmin:

```sql
INSERT INTO categories (name, created_at, updated_at)
VALUES 
('Parfum Pria', NOW(), NOW()),
('Parfum Wanita', NOW(), NOW()),
('Parfum Unisex', NOW(), NOW());

INSERT INTO products (category_id, name, description, image, created_at, updated_at)
VALUES
(1, 'Dior Sauvage', 'Parfum pria dengan aroma maskulin, segar, dan elegan.', 'dior.png', NOW(), NOW()),
(1, 'Creed Aventus', 'Parfum pria premium dengan karakter aroma kuat dan mewah.', 'creed.png', NOW(), NOW()),
(2, 'Black Opium', 'Parfum wanita dengan aroma manis, elegan, dan modern.', 'black-opium.png', NOW(), NOW()),
(3, 'Baccarat Rouge', 'Parfum unisex yang mewah, elegan, dan cocok digunakan untuk acara spesial.', 'baccarat.png', NOW(), NOW());

INSERT INTO product_variants (product_id, size, price, stock, created_at, updated_at)
VALUES
(1, '10 ml', 50000, 20, NOW(), NOW()),
(1, '30 ml', 120000, 15, NOW(), NOW()),
(1, '50 ml', 180000, 10, NOW(), NOW()),

(2, '10 ml', 55000, 20, NOW(), NOW()),
(2, '30 ml', 130000, 15, NOW(), NOW()),
(2, '50 ml', 200000, 10, NOW(), NOW()),

(3, '10 ml', 45000, 20, NOW(), NOW()),
(3, '30 ml', 110000, 15, NOW(), NOW()),
(3, '50 ml', 165000, 10, NOW(), NOW()),

(4, '10 ml', 55000, 30, NOW(), NOW()),
(4, '30 ml', 130000, 20, NOW(), NOW()),
(4, '50 ml', 200000, 10, NOW(), NOW());
```

## 8. Jalankan Project

Jalankan server CodeIgniter:

```bash
php spark serve
```

Lalu buka di browser:

```text
http://localhost:8080
```

Jika port 8080 sudah digunakan, jalankan dengan port lain:

```bash
php spark serve --port 8081
```

Lalu buka:

```text
http://localhost:8081
```

## Struktur Folder Penting

```text
app/
├── Controllers/
│   ├── AuthController.php
│   ├── HomeController.php
│   ├── CartController.php
│   ├── CheckoutController.php
│   ├── AdminTransactionController.php
│   └── CustomerTransactionController.php
│
├── Models/
│   ├── UserModel.php
│   ├── ProductModel.php
│   ├── ProductVariantModel.php
│   ├── TransactionModel.php
│   └── TransactionDetailModel.php
│
├── Views/
│   ├── auth/
│   ├── home/
│   ├── cart/
│   ├── checkout/
│   ├── dashboard/
│   ├── admin/
│   └── customer/
│
public/
└── assets/
    ├── css/
    │   └── style.css
    └── img/
        ├── auth-bg.png
        ├── dior.png
        ├── creed.png
        ├── black-opium.png
        └── baccarat.png
```

## Alur Sistem

### Customer

```text
Register / Login
        ↓
Melihat Produk
        ↓
Melihat Detail Produk
        ↓
Memilih Varian Ukuran
        ↓
Tambah ke Keranjang
        ↓
Edit / Hapus Keranjang
        ↓
Checkout
        ↓
Riwayat Transaksi
```

### Admin

```text
Login Admin
        ↓
Dashboard Admin
        ↓
Kelola Transaksi
        ↓
Lihat Detail Transaksi
        ↓
Update Status Pembayaran
        ↓
Update Status Pesanan
```

## Status Pembayaran

Status pembayaran yang digunakan:

```text
pending
paid
failed
```

## Status Pesanan

Status pesanan yang digunakan:

```text
pending
processing
shipped
completed
cancelled
```

## Catatan Penting

Jika muncul error database seperti:

```text
Unable to connect to the database
```

Pastikan konfigurasi `.env` sudah benar dan database sudah dibuat.

Jika muncul error view seperti:

```text
Invalid file
```

Pastikan file view berada di folder yang benar, misalnya:

```text
app/Views/customer/transactions/index.php
```

Jika gambar tidak muncul, pastikan file gambar ada di:

```text
public/assets/img/
```

Dan nama gambar di database sesuai dengan nama file, contoh:

```text
dior.png
creed.png
black-opium.png
baccarat.png
```

## Developer

Project ini dibuat untuk kebutuhan pengembangan sistem penjualan parfum berbasis web pada Toko Malini Parfum.

```text
Nama Project : Malini Parfum
Framework    : CodeIgniter 4
Database     : MySQL
```

````

Saran tambahan: setelah README ini dibuat, push ke GitHub dengan perintah:

```bash
git add README.md
git commit -m "Add README installation guide"
git push
````
