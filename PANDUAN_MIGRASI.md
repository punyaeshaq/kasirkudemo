# Panduan Migrasi / Pindahan Aplikasi KasirKu (Update 2026)

Panduan ini membimbing Anda memindahkan aplikasi KasirKu ke komputer lain dengan aman, termasuk memindahkan **Sistem Aktivasi** yang kini terpisah demi keamanan.

---

## A. Backup di Komputer Lama (Sumber)

Pastikan Anda menyalin **TIGA (3)** hal berikut dari komputer lama:

1.  **Database (`.sql`)**:
    *   Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
    *   Pilih database `kasirku` -> Pilih menu **Export** -> Klik **Go** (Download).
    *   Simpan file `.sql` tersebut.

2.  **Folder Aplikasi (`kasirku`)**:
    *   Copy seluruh folder dari `c:\xampp\htdocs\kasirku` ke Flashdisk/Drive.
    *   *Tips: Hapus folder `node_modules` sebelum copy agar lebih cepat (nanti diinstall ulang).*

3.  **Folder Aktivasi & Bot (`KasirKu-Activator`)** (PENTING!):
    *   Folder ini berisi Bot Telegram dan Generator Lisensi.
    *   Lokasinya ada di: `Documents/KasirKu-Activator` (di folder Dokumen user).
    *   **Wajib dicopy** jika Anda ingin bisa membuat kode aktivasi di komputer baru.

---

## B. Persiapan di Komputer Baru (Target)

Install software wajib ini di komputer baru:
1.  **XAMPP** (PHP 8.1/8.2).
2.  **Composer** (untuk PHP).
3.  **Node.js** (untuk Javascript).
4.  **Git** (jika ingin update dari GitHub).

---

## C. Proses Pemindahan & Instalasi

### 1. Restore Folder
*   Letakkan folder `kasirku` ke `C:\xampp\htdocs\kasirku`.
*   Letakkan folder `KasirKu-Activator` ke `Documents` komputer baru.

### 2. Restore Database
*   Nyalakan Apache & MySQL (XAMPP).
*   Buka phpMyAdmin, buat database baru bernama `kasirku`.
*   Import file `.sql` backup Anda ke database tersebut.

### 3. Setup Environment (.env) - KRITIKAL!
*   Buka folder `C:\xampp\htdocs\kasirku`.
*   Cek file `.env`. (Jika hilang, copy `.env.example` rename jadi `.env`).
*   **PENTING:** Pastikan file `.env` memiliki baris `ACTIVATION_SALT` yang **sama persis** dengan yang lama (atau sesuai dengan generator di folder Activator).
    ```ini
    DB_DATABASE=kasirku
    DB_PASSWORD=
    ...
    # KUNCI RAHASIA AKTIVASI (Wajib pakai tanda kutip dua " di awal dan akhir)
    ACTIVATION_SALT="KasirKu2026$#@!SuperSecretActivationKey_D0N0T5H4R3"
    ```
    *Jika kunci ini berbeda dengan generator, kode aktivasi tidak akan bekerja!*

### 4. Install Dependensi (Reset Pabrik)
Lakukan ini agar aplikasi "segar" kembali di komputer baru:

Buka Terminal di folder `kasirku` dan jalankan perintah satu per satu:

```bash
# 1. Install Library
composer install
npm install

# 2. Setup Kunci Aplikasi
php artisan key:generate
php artisan storage:link

# 3. Setup Cache & Config
php artisan config:clear
php artisan optimize:clear

# 4. Build Frontend
npm run build
```

---

## D. Menjalankan Bot & Aktivasi

Bot Telegram dan Generator Lisensi ada di folder terpisah (`Documents/KasirKu-Activator`). Folder ini **TIDAK** ikut ter-upload ke GitHub demi keamanan, jadi harus Anda jaga baik-baik.

**Cara menjalankan Bot di komputer baru:**
1.  Buka terminal di folder `Documents/KasirKu-Activator`.
2.  Jalankan:
    ```bash
    npm install
    node bot.js
    ```
3.  Bot Telegram Anda siap digunakan lagi.

---

## Troubleshooting

*   **Error "Kode Aktivasi Invalid"**:
    *   Cek file `.env` di folder aplikasi.
    *   Pastikan `ACTIVATION_SALT` isinya dibungkus tanda kutip `"..."` dan isinya sama dengan `SECRET_SALT` di file `KasirKu-Activator/activation-generator.js`.
    *   Jalankan `php artisan config:clear` setelah edit `.env`.

*   **Gambar Produk Hilang**:
    *   Jalankan `php artisan storage:link` lagi.
