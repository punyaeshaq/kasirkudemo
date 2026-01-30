# Panduan Koneksi Supabase

Panduan ini akan membantu Anda menghubungkan aplikasi KasirKu ke database Supabase (PostgreSQL).

## 1. Buat Proyek di Supabase

1. Buka [Supabase.com](https://supabase.com) dan login/register.
2. Klik **"New Project"**.
3. Pilih Organization Anda.
4. Isi detail proyek:
   - **Name**: KasirKu (atau nama lain)
   - **Database Password**: **PENTING!** Simpan password ini, Anda akan membutuhkannya nanti.
   - **Region**: Pilih region terdekat (misalnya Singapore).
5. Klik **"Create new project"** dan tunggu hingga setup selesai.

## 2. Dapatkan Kredensial Database

Setelah proyek siap:
1. Pergi ke menu **Project Settings** (ikon gir di bagian bawah sidebar kiri).
2. Pilih menu **Database**.
3. Scroll ke bagian **Connection parameters**.
4. Catat informasi berikut:
   - **Host**
   - **Database name** (biasanya `postgres`)
   - **Port** (biasanya `5432`)
   - **User** (biasanya `postgres`)

## 3. Update Konfigurasi Aplikasi (.env)

Buka file `.env` di folder proyek Anda dan ubah bagian konfigurasi database seperti berikut:

```env
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxxxxxxxxxx.supabase.co  <-- Isi dengan Host dari Supabase
DB_PORT=5432
DB_DATABASE=postgres                         <-- Isi dengan Database name
DB_USERNAME=postgres                         <-- Isi dengan User
DB_PASSWORD=password_anda                    <-- Isi Password yang Anda buat di langkah 1
```

## 4. Pastikan Ekstensi PHP Aktif

Jika Anda menjalan aplikasi di Local (XAMPP/Laragon):
1. Buka file `php.ini` (Di XAMPP biasanya di `xampp/php/php.ini`, klik Config di Apache pada Control Panel).
2. Cari baris berikut (gunakan CTRL+F):
   ```ini
   ;extension=pgsql
   ;extension=pdo_pgsql
   ```
3. Hilangkan tanda titik koma (`;`) di depan baris tersebut agar menjadi:
   ```ini
   extension=pgsql
   extension=pdo_pgsql
   ```
4. **Restart Apache** di XAMPP Control Panel.

## 5. Jalankan Migrasi Database

Buka terminal di folder proyek dan jalankan perintah:

```bash
php artisan migrate
```

Jika berhasil, tabel-tabel akan dibuat di database Supabase Anda.

## Catatan Tambahan

- **SSL Mode**: Konfigurasi Laravel secara default sudah mendukung SSL (`sslmode=prefer`), yang dibutuhkan oleh Supabase.
- **Trigger**: Kami telah memperbarui kode migrasi agar kompatibel dengan PostgreSQL (Supabase), jadi fungsi keamanan (seperti proteksi Super Admin) akan tetap berjalan.

## Mengatasi Masalah "Unknown host" (IPv6 Issue)

Jika Anda mengalami error `SQLSTATE[08006] ... could not translate host name ... Unknown host`, itu berarti jaringan internet Anda belum mendukung IPv6 yang digunakan oleh Direct Connection Supabase.

**Solusi: Gunakan Connection Pooler (IPv4)**

1. Di Dashboard Supabase, masuk ke **Project Settings > Database**.
2. Cari bagian **Connection Pooling** (bukan Direct Connection).
3. Salin konfigurasi baru:
   - **Host**: Biasanya berakhiran `.pooler.supabase.com` (contoh: `aws-0-ap-southeast-1.pooler.supabase.com`).
   - **Port**: Gunakan `5432` atu `6543`.
   - **User**: Perhatikan username mungkin berubah menjadi `postgres.projectid` (contoh: `postgres.itvuoknqvhjrgopdozpa`).
4. Update file `.env` Anda dengan data dari Pooler tersebut.

Contoh `.env` menggunakan Pooler:
```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.itvuoknqvhjrgopdozpa
DB_PASSWORD=password_anda
```
