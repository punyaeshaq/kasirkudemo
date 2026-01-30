# Panduan Instalasi dan Menjalankan Aplikasi KasirKu

Panduan ini menjelaskan cara mengunduh (clone), menginstal, dan menjalankan aplikasi KasirKu dari repository GitHub hingga siap digunakan.

## 1. Prasyarat Sistem

Sebelum memulai, pastikan komputer Anda telah terinstal software berikut:

*   **XAMPP** (untuk PHP dan MySQL/MariaDB). Pastikan versi PHP adalah **8.1** atau lebih baru.
*   **Composer** (Dependency manager untuk PHP). [Download Composer](https://getcomposer.org/download/)
*   **Node.js** (Runtime untuk JavaScript, dibutuhkan untuk Vite dan Electron). Disarankan versi LTS (18.x atau 20.x). [Download Node.js](https://nodejs.org/)
*   **Git** (Untuk mengunduh source code). [Download Git](https://git-scm.com/)

## 2. Mengunduh Source Code (Clone)

Buka terminal (Command Prompt, PowerShell, atau Git Bash) dan jalankan perintah berikut untuk mengunduh source code dari GitHub:

```bash
git clone https://github.com/punyaeshaq/kasirku.git
cd kasirku
```

## 3. Instalasi Backend (Laravel)

Jalankan perintah berikut di dalam folder `kasirku` untuk menginstal dependency PHP:

```bash
composer install
```

## 4. Konfigurasi Environment

Duplikasi file `.env.example` menjadi `.env`:

```bash
copy .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

## 5. Konfigurasi Database

1.  Pastikan modul **Apache** dan **MySQL** di XAMPP sudah berjalan (klik tombol **Start**).
2.  Buka browser dan akses `http://localhost/phpmyadmin`.
3.  Buat database baru dengan nama `kasirku` (atau nama lain yang Anda inginkan).
4.  Buka file `.env` dengan text editor (Notepad, VS Code, dll).
5.  Cari bagian konfigurasi database dan sesuaikan (biasanya default XAMPP user `root` tanpa password):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasirku
DB_USERNAME=root
DB_PASSWORD=
```

## 6. Migrasi Database

Jalankan perintah ini untuk membuat tabel-tabel di database:

```bash
php artisan migrate
```

*(Opsional) Jika ingin mengisi data dummy awal:*
```bash
php artisan migrate --seed
```

## 7. Instalasi Frontend & Electron

Instal dependency JavaScript:

```bash
npm install
```

## 8. Menjalankan Aplikasi

Aplikasi ini bisa dijalankan dalam dua mode: **Mode Web** (di browser) atau **Mode Desktop** (aplikasi Electron).

### Cara 1: Mode Web (Browser)

Anda perlu menjalankan dua terminal secara bersamaan.

**Terminal 1 (Menjalankan Server Laravel):**
```bash
php artisan serve
```
*Server akan berjalan di http://127.0.0.1:8000*

**Terminal 2 (Menjalankan Vite Hot Reload):**
```bash
npm run dev
```

Buka browser dan akses `http://127.0.0.1:8000`.

### Cara 2: Mode Desktop (Electron)

Untuk mode pengembangan (Development), pastikan **Terminal 1 (php artisan serve)** sudah berjalan. Lalu jalankan:

```bash
npm run electron:dev
```
Aplikasi desktop akan terbuka.

### Cara 3: Build Aplikasi Desktop (.exe)

Untuk membuat file instalasi `.exe` (Windows):

1.  Pastikan `AppLayout.vue` atau konfigurasi URL di `electron.cjs` sudah mengarah ke production URL atau localhost yang sesuai jika ingin offline-first (tergantung implementasi).
2.  Jalankan perintah build:

```bash
npm run electron:build
```

Hasil build (file `.exe`) akan muncul di folder `dist-electron` atau `dist-electron-new`.

## 9. Troubleshooting Umum

*   **Error "Vite manifest not found":** Pastikan Anda menjalankan `npm run dev` (untuk development) atau `npm run build` (untuk production).
*   **Database Error:** Pastikan XAMPP MySQL berjalan dan konfigurasi `.env` sudah benar.
*   **Tampilan Berantakan:** Pastikan `npm run dev` berjalan jika dalam mode development.

---
**Selesai!** Aplikasi KasirKu siap digunakan.
