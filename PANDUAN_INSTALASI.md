# Panduan Instalasi KasirKu

Panduan ini berisi langkah-langkah untuk menjalankan aplikasi KasirKu dari repository GitHub di lingkungan lokal Anda.

## 1. Prasyarat Sistem

Pastikan komputer Anda memiliki:
*   **XAMPP** (PHP 8.1+ & MySQL)
*   **Composer** (PHP Dependency Manager)
*   **Node.js** (v18 ke atas) + NPM
*   **Git**

## 2. Instalasi Project

1.  **Clone Repository**
    ```bash
    git clone https://github.com/punyaeshaq/kasirku.git
    cd kasirku
    ```

2.  **Install Dependencies**
    ```bash
    # Install dependency PHP
    composer install

    # Install dependency JavaScript
    npm install
    ```

## 3. Konfigurasi Environment

1.  Salin file contoh konfigurasi:
    ```bash
    cp .env.example .env
    ```
    *(Di Windows CMD: `copy .env.example .env`)*

2.  Generate Key Aplikasi:
    ```bash
    php artisan key:generate
    ```

3.  Link Storage (untuk gambar):
    ```bash
    php artisan storage:link
    ```

## 4. Setup Database

1.  Nyalakan **Apache** dan **MySQL** di XAMPP.
2.  Buka [phpMyAdmin](http://localhost/phpmyadmin).
3.  Buat database baru dengan nama `kasirku` (atau sesuaikan dengan kebutuhan).
4.  Edit file `.env` dan sesuaikan koneksi database Anda:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=kasirku
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5.  Jalankan migrasi database:
    ```bash
    php artisan migrate
    ```

## 5. Menjalankan Aplikasi

Anda memiliki dua pilihan untuk menjalankan aplikasi:

### A. Mode Web (Browser)

Buka dua terminal terpisah:

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

Akses aplikasi di: `http://localhost:8000`

### B. Mode Desktop (Electron)

Untuk menjalankan versi desktop dalam mode development:

```bash
# Pastikan 'php artisan serve' di terminal lain sudah berjalan
npm run electron:dev
```

Untuk membuat file `.exe` (Build):
```bash
npm run electron:build
```

---
**Catatan:** Aplikasi ini membutuhkan konfigurasi lanjutan dari Administrator untuk penggunaan penuh. Silakan hubungi pengelola repositori untuk informasi lebih lanjut.
