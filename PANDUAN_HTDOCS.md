# Panduan Migrasi ke Htdocs (XAMPP)

Panduan ini menjelaskan cara memindahkan aplikasi KasirKu ke folder `htdocs` (XAMPP) dan mengatur aplikasi Desktop (Electron) agar menggunakan server dari XAMPP, bukan menjalankan server sendiri.

## 1. Pindahkan File ke Htdocs

1.  Pastikan XAMPP sudah terinstall.
2.  Copy seluruh folder `kasirku` ke dalam folder `htdocs` di instalasi XAMPP Anda (biasanya `C:\xampp\htdocs\kasirku`).
3.  Buka folder `C:\xampp\htdocs\kasirku`.
4.  Copy file `.env.example` menjadi `.env` (jika belum ada).
5.  Edit file `.env` dan sesuaikan pengaturan database:
    ```ini
    APP_URL=http://localhost/kasirku/public
    DB_DATABASE=kasirku
    DB_USERNAME=root
    DB_PASSWORD=
    ```
6.  Jalankan perintah berikut di terminal (di dalam folder htdocs/kasirku):
    ```bash
    composer install
    php artisan key:generate
    php artisan migrate
    php artisan storage:link
    ```

## 2. Pindahkan Hosting config

1.  Di dalam folder aplikasi Electron (baik di source code maupun di hasil build/instalasi), cari file bernama `hosting.json.example`.
2.  Copy file tersebut dan ubah namanya menjadi `hosting.json`.
3.  Buka `hosting.json` dengan Notepad dan pastikan isinya:
    ```json
    {
        "url": "http://localhost/kasirku/public",
        "use_external_server": true
    }
    ```
    *Catatan: Sesuaikan `url` jika Anda menaruh folder dengan nama yang berbeda di htdocs.*

## 3. Jalankan Aplikasi

1.  Pastikan **Apache** dan **MySQL** di XAMPP sudah di-start.
2.  Buka aplikasi Desktop KasirKu (Electron).
3.  Aplikasi akan otomatis mendeteksi `hosting.json` dan membuka URL dari XAMPP tanpa menjalankan `php artisan serve` sendiri.

## Troubleshooting

-   **Aplikasi Desktop Error PHP tidak ditemukan**: Pastikan `hosting.json` sudah dibuat dengan benar. Jika file ini ada, aplikasi tidak akan mencari PHP.
-   **Halaman Putih / Error 404**: Pastikan URL di `hosting.json` sesuai dengan alamat akses di browser. Coba buka URL tersebut di Chrome/Edge, jika bisa dibuka, maka di aplikasi juga harusnya bisa.
-   **Gambar tidak muncul**: Pastikan perintah `php artisan storage:link` sudah dijalankan di folder htdocs.
