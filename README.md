# MStore - All-in-One Multi-Tenant SaaS Platform untuk Bisnis & UMKM

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**MStore** adalah platform kasir (POS), manajemen inventaris, dan operasional bisnis berbasis **Multi-Tenant SaaS**. Dirancang khusus agar siap digunakan untuk berbagai jenis bisnis UMKM, mulai dari toko retail & grosir, jasa & servis, hingga booking acara dan reservasi.

MStore dilengkapi dengan landing page modern, pendaftaran mandiri toko baru (*self-service registration*) dengan **14 Hari Free Trial**, serta pembagian paket langganan dinamis (*Starter, Service, Pro, Enterprise*).

---

## 🚀 4 Pilar Modul Solusi Bisnis

MStore dirancang modular sehingga setiap toko/tenant hanya melihat fitur yang sesuai dengan paket dan kebutuhan usahanya:

| Modul | Target Bisnis | Fitur Utama |
| :--- | :--- | :--- |
| **🛒 Toko & Retail** | Minimarket, Toko Kelontong, ATK, Fashion, Toko Bangunan, Grosir | • POS Kasir cepat dengan barcode scanner<br>• Cetak struk termal (58mm/80mm) & nota digital<br>• Manajemen multi-gudang & kartu stok<br>• Alert stok menipis (*low stock warning*)<br>• Penyesuaian stok (*stock opname*) & riwayat mutasi |
| **🧼 Jasa & Layanan** | Cuci Kendaraan (Carwash/Motor), Laundry, Bengkel, Salon & Barbershop | • Kasir alur antrian jasa (antri, proses, selesai)<br>• Perhitungan komisi pegawai/mekanik otomatis<br>• Riwayat plat nomor / kendaraan pelanggan<br>• Program loyalty & voucher reward |
| **📅 Booking & Reservasi** | Wedding Organizer, Sewa Studio/Venue, Rental Alat, Event Organizer | • Manajemen reservasi & kalender ketersediaan<br>• Skema pembayaran bertahap (DP, termin, pelunasan)<br>• Manajemen paket layanan & kustomisasi acara<br>• Invoice & kuitansi profesional PDF |
| **💼 Multi-Branch & Keuangan** | Semua skala bisnis & pemilik multi-cabang | • Pencatatan kas masuk & kas keluar<br>• Laporan laba rugi real-time & performa cabang<br>• Manajemen multi-cabang terpusat<br>• Role & hak akses bertingkat (Owner, Admin, Kasir) |

---

## 💎 Paket Langganan SaaS (Subscription Tiers)

Aplikasi memiliki sistem modul dinamis (*Module Gate*) yang membatasi akses menu dan fitur sesuai tingkatan paket:

1. **Starter Plan** (Rp 99.000 / bln)
   - Modul: Toko & Retail (POS, Stok, Pembelian, Penjualan).
   - Kuota: 1 Cabang, Maksimal 2 User Kasir.
2. **Service Plan** (Rp 149.000 / bln)
   - Modul: Jasa & Layanan (Kasir Antrian, Manajemen Komisi Karyawan, Loyalty).
   - Kuota: 1 Cabang, Maksimal 3 User.
3. **Pro Plan** (Rp 249.000 / bln) — ⭐ *Paling Populer*
   - Modul: **Semua Modul Aktif** (Retail + Jasa + Booking & Reservasi).
   - Kuota: 3 Cabang, Hingga 10 User.
4. **Enterprise Plan** (Rp 499.000 / bln)
   - Modul: Akses Penuh tanpa batas, Unlimited Cabang & User, Prioritas Support.

---

## 🌐 Alur Self-Service Onboarding

1. Calon pemilik toko mengakses Landing Page publik di `/`.
2. Klik tombol **Daftar Gratis 14 Hari** atau buka URL `/register`.
3. Mengisi nama toko, nama owner, email, password, nomor WhatsApp, alamat, dan memilih tipe bisnis awal.
4. Sistem otomatis:
   - Membuat entitas Tenant/Company baru.
   - Mengaktifkan **Free Trial selama 14 hari**.
   - Menghubungkan paket awal dan modul yang relevan.
   - Login otomatis dan mengarahkan langsung ke Dashboard Toko.

---

## 🛠️ Persyaratan Sistem

- **PHP**: Versi 8.2 atau lebih tinggi (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `curl`, `zip`)
- **Database**: MySQL 8.0+ atau MariaDB 10.4+
- **Web Server**: Apache / Nginx
- **Composer**: Versi 2.x
- **Node.js & NPM**: Versi 18+ (untuk kompilasi aset frontend)

---

## 📥 Panduan Instalasi di Windows (XAMPP / Laragon)

### 1. Clone Repository
Buka terminal (PowerShell atau Git Bash):
```bash
git clone https://github.com/dhedyds9-ops/mstore.git
cd mstore
```

### 2. Instalasi Dependensi PHP & Node.js
```bash
composer install
npm install
npm run build
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example`:
```bash
copy .env.example .env
```
Buka file `.env` dan sesuaikan koneksi database Anda:
```env
APP_NAME=MStore
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mstore
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Setup Database & Storage Link
```bash
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
```

### 5. Jalankan Aplikasi
```bash
php artisan serve --port=8080
```
Buka browser dan akses:
- **Landing Page & Pendaftaran:** `http://127.0.0.1:8080/`
- **Pendaftaran Toko Baru (Trial 14 Hari):** `http://127.0.0.1:8080/register`
- **Login Aplikasi:** `http://127.0.0.1:8080/login`

---

## 🐧 Panduan Instalasi di Server Linux (Ubuntu / Debian)

### 1. Update Paket & Install Dependensi
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install nginx mysql-server php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-gd unzip git curl -y
```

### 2. Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. Clone & Setup Project
```bash
cd /var/www
sudo git clone https://github.com/dhedyds9-ops/mstore.git
cd mstore

sudo composer install --optimize-autoloader --no-dev
npm install && npm run build

sudo cp .env.example .env
sudo nano .env
```

### 4. Generate Key & Migrasi Database
```bash
php artisan key:generate
php artisan storage:link
php artisan migrate --force --seed
```

### 5. Atur Hak Akses Direktori
```bash
sudo chown -R www-data:www-data /var/www/mstore
sudo chmod -R 775 /var/www/mstore/storage /var/www/mstore/bootstrap/cache
```

### 6. Konfigurasi Nginx
Buat file konfigurasi: `sudo nano /etc/nginx/sites-available/mstore`
```nginx
server {
    listen 80;
    server_name mstore.yourdomain.com;
    root /var/www/mstore/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php index.html;
    charset utf-8;
    client_max_body_size 64M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Aktifkan dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/mstore /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 🔑 Kredensial Akun Default (Seeder)

Setelah menjalankan `php artisan migrate --seed`, akun demo siap pakai:

| Role | Email | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Super Admin / Owner** | `admin@mstore.local` | `password` | Akses penuh seluruh cabang & modul |
| **Kasir Toko (Retail)** | `kasir@mstore.local` | `password` | Akses modul POS Retail & Stok |
| **Kasir Jasa (Service)** | `service@mstore.local` | `password` | Akses modul Antrian Jasa & Komisi |

> **Catatan:** Anda juga dapat langsung membuat akun toko baru melalui tombol **Daftar Sekarang** di halaman `/register` untuk menguji alur multi-tenant mandiri.

---

## 🧪 Pengujian Otomatis (Feature Tests)

MStore telah dilengkapi unit & feature test untuk memastikan fitur registrasi, gating langganan, dan POS berjalan lancar:
```bash
php artisan test --filter TenantRegisterTest
```

---

## 🔄 Pembaruan Sistem (Update)

Untuk memperbarui sistem dari remote repository:
```bash
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📄 Lisensi
Sistem ini dirilis di bawah lisensi **MIT License**. Bebas dikembangkan dan digunakan untuk operasional bisnis UMKM dan komersial.
