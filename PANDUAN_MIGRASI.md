# Panduan Migrasi / Pindahan Aplikasi KasirKu

Panduan ini akan membantu Anda memindahkan seluruh aplikasi (Source Code + Database + Aplikasi Desktop) ke komputer lain agar berjalan dengan sempurna.

## A. Persiapan di Komputer Lama (Sumber)

1.  **Backup Database**:
    *   Buka **phpMyAdmin** (biasanya `http://localhost/phpmyadmin`).
    *   Pilih database `kasirku`.
    *   Klik menu **Export** di bagian atas.
    *   Klik **Export** (tombol Go) untuk mendownload file `.sql` (misal: `kasirku.sql`). Simpan file ini.

2.  **Backup File Aplikasi**:
    *   Copy seluruh folder `c:\xampp\htdocs\kasirku` ke Flashdisk atau Google Drive.
    *   *(Opsional tapi disarankan)*: Agar proses copy lebih cepat, Anda bisa men-zip folder tersebut terlebih dahulu. **HINDARI** meng-copy folder `node_modules` dan `vendor` untuk menghindari error path, sebaiknya folder tersebut dihapus atau di-exclude sebelum dicopy, karena kita akan menginstall ulang dependensinya di komputer baru. Namun, jika Anda ingin cara cepat (copy-paste), pastikan path di komputer baru PERSIS SAMA (`c:\xampp\htdocs\kasirku`). Jika berbeda, wajib install ulang dependensi.

## B. Persiapan di Komputer Baru (Target)

Di komputer baru, Anda WAJIB menginstall software berikut:

1.  **XAMPP**: Untuk server (Apache) dan Database (MySQL).
    *   Download dan Install XAMPP versi PHP 8.1 atau 8.2.
2.  **Composer**: Untuk menginstall library PHP (Laravel).
    *   Download di [getcomposer.org](https://getcomposer.org/download/). Install dan arahkan ke `php.exe` di dalam folder XAMPP (biasanya `C:\xampp\php\php.exe`).
3.  **Node.js**: Untuk membangun aset frontend (Vue.js) dan Electron.
    *   Download versi LTS di [nodejs.org](https://nodejs.org/).

## C. Proses Pemindahan dan Instalasi

1.  **Pindahkan Folder**:
    *   Letakkan folder `kasirku` yang sudah dicopy tadi ke `C:\xampp\htdocs\`.
    *   Hasil akhirnya harus: `C:\xampp\htdocs\kasirku`.

2.  **Restore Database**:
    *   Nyalakan **Apache** dan **MySQL** di XAMPP Control Panel.
    *   Buka browser ke `http://localhost/phpmyadmin`.
    *   Buat database baru dengan nama `kasirku`.
    *   Klik database `kasirku` yang baru dibuat.
    *   Klik menu **Import**.
    *   Pilih file `.sql` yang tadi Anda backup dari komputer lama.
    *   Klik **Go** / **Import**.

3.  **Konfigurasi Environment**:
    *   Buka folder `C:\xampp\htdocs\kasirku`.
    *   Cek apakah ada file bernama `.env`. Jika tidak ada, copy file `.env.example` lalu rename menjadi `.env`.
    *   Buka file `.env` dengan Notepad/Text Editor, pastikan isinya sesuai:
        ```ini
        APP_URL=http://localhost/kasirku/public
        DB_DATABASE=kasirku
        DB_USERNAME=root
        DB_PASSWORD=
        ```
        *(Sesuaikan DB_PASSWORD jika Anda menset password di MySQL XAMPP komputer baru)*.

4.  **Install Dependensi (Wajib dilakukan agar tidak error)**:
    *   Buka terminal (Command Prompt / PowerShell) di dalam folder `kasirku`. Caranya: Buka folder `kasirku`, klik kanan di ruang kosong -> **Open in Terminal** (atau ketik `cmd` di address bar).
    *   Jalankan perintah-perintah berikut satu per satu (tunggu sampai selesai):

    a. **Install Library PHP**:
    ```bash
    composer install
    ```
    *(Jika error extension, pastikan extension zip, gd, fileinfo aktif di php.ini XAMPP)*

    b. **Generate Key (Jika .env baru)**:
    ```bash
    php artisan key:generate
    ```

    c. **Link Storage (Supaya gambar muncul)**:
    *(PENTING: Hapus dulu folder `public/storage` jika sudah ada, baru jalankan ini)*
    ```bash
    php artisan storage:link
    ```

    d. **Install Library Javascript & Build**:
    ```bash
    npm install
    npm run build
    ```

    e. **Migrasi Database (Memastikan struktur DB update)**:
    ```bash
    php artisan migrate
    ```

## D. Menjalankan Aplikasi

Ada dua cara menjalankan aplikasi:

### Cara 1: Menggunakan Browser (Mode Web)
1.  Pastikan Apache dan MySQL di XAMPP running.
2.  Buka browser: `http://localhost/kasirku/public`

### Cara 2: Menggunakan Aplikasi Desktop (Electron)
Jika Anda ingin membuat aplikasi `.exe` atau menjalankan mode desktop:

1.  **Konfigurasi Koneksi Desktop**:
    *   Pastikan file `hosting.json` ada di root folder (`C:\xampp\htdocs\kasirku\hosting.json`).
    *   Isinya harus mengarah ke XAMPP:
        ```json
        {
            "url": "http://localhost/kasirku/public",
            "use_external_server": true
        }
        ```
2.  **Jalankan Mode Dev Desktop**:
    ```bash
    npm run electron
    ```
3.  **Build Aplikasi Desktop (.exe)**:
    *   Untuk membuat file .exe yang bisa dicopy-copy:
        ```bash
        npm run electron:build
        ```
    *   Hasilnya ada di folder `dist-electron-new`. Anda bisa meng-copy file `.exe` (biasanya `KasirKu Setup 1.0.0.exe` atau folder `win-unpacked`) ke Desktop komputer baru.
    *   **Catatan**: Aplikasi .exe ini tetap membutuhkan **XAMPP (Apache+MySQL) yang Menyala** karena dia hanya "Browser Khusus" yang membuka `http://localhost/kasirku/public`.

## Ringkasan Cepat Setiap Kali Menyalakan Komputer Baru:
1.  Buka **XAMPP Control Panel**.
2.  Start **Apache** dan **MySQL**.
3.  Buka Aplikasi **KasirKu** (shortcut di desktop) atau Browser.
