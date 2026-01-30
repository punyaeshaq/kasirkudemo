# Panduan Deployment ke Vercel

Panduan ini akan membantu Anda men-deploy aplikasi KasirKu ke Vercel secara gratis.

## Prasyarat

1.  Akun [GitHub](https://github.com).
2.  Akun [Vercel](https://vercel.com) (Login menggunakan GitHub).
3.  Proyek KasirKu sudah di-push ke repository GitHub Anda.

## Langkah 1: Push ke GitHub

Pastikan semua perubahan terbaru (termasuk file `vercel.json` dan `api/index.php` yang baru saja dibuat) sudah di-upload ke GitHub.

```bash
git add .
git commit -m "Persiapan deployment Vercel"
git push origin main
```

## Langkah 2: Import Proyek di Vercel

1.  Buka Dashboard Vercel: https://vercel.com/dashboard
2.  Klik tombol **"Add New..."** > **"Project"**.
3.  Di bagian "Import Git Repository", cari repository **kasirku** Anda dan klik **Import**.

## Langkah 3: Konfigurasi Proyek

Di halaman konfigurasi "Configure Project":

1.  **Framework Preset**: Biarkan `Other` (Vercel biasanya mendeteksi sendiri atau biarkan default).
2.  **Root Directory**: Biarkan `./` (kosong/default).
3.  **Environment Variables**:
    Anda **WAJIB** memasukkan variable lingkungan agar aplikasi berjalan dan bisa konek ke database. Buka file `.env` di komputer Anda, dan salin isinya ke sini SATU PER SATU.
    
    Variable Penting yang HARUS ada:
    - `APP_KEY` (Salin dari .env)
    - `DB_CONNECTION` = `pgsql`
    - `DB_HOST` (Gunakan host Pooler Supabase Anda)
    - `DB_PORT` = `5432`
    - `DB_DATABASE` = `postgres`
    - `DB_USERNAME` (User Pooler Supabase)
    - `DB_PASSWORD` (Password database Supabase)
    
    *Tips: Anda bisa copy-paste isi seluruh file `.env` ke satu kotak input di Vercel, dan Vercel otomatis memecahnya.*

4.  Klik **Deploy**.

## Langkah 4: Selesai

Tunggu proses build selesai (biasanya 1-2 menit). Jika sukses, Anda akan melihat kartu pratinjau aplikasi Anda dengan tombol **"Visit"**.

Klik tombol tersebut, dan aplikasi KasirKu Anda sekarang sudah online!

---

## Troubleshooting

### Halaman Putih / Error 500
- Cek **Logs** di dashboard Vercel untuk melihat detail error.
- Pastikan `APP_KEY` sudah benar.
- Pastikan koneksi database menggunakan **Supabase Connection Pooler** (bukan Direct).

### Asset (CSS/JS) Tidak Muncul
- Pastikan build step di `package.json` dijalankan. Biasanya Vercel menjalankannya otomatis.
- Cek konfigurasi `vite.config.js` jika perlu penyesuaian base URL (biasanya tidak perlu).
