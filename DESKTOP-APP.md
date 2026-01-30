# KasirKu Desktop Application

## 📦 Aplikasi Desktop Berhasil Dibuat!

Aplikasi KasirKu sudah tersedia sebagai aplikasi desktop Windows.

---

## 🚀 Cara Menjalankan (Development)

### Opsi 1: Double-click `KasirKu.bat`
Yang paling mudah, cukup double-click file `KasirKu.bat` di folder project.

### Opsi 2: Via Terminal
```bash
npm run electron
```

---

## 📁 Aplikasi EXE Sudah Tersedia

Aplikasi EXE sudah dibuat dan tersedia di:

```
dist-electron/win-unpacked/KasirKu.exe
```

### Cara Mendistribusikan:
1. Copy seluruh folder `dist-electron/win-unpacked/` ke komputer tujuan
2. Pastikan PHP terinstall di komputer tujuan
3. Double-click `KasirKu.exe` atau `Jalankan KasirKu.bat`

### Isi Folder dist-electron/win-unpacked/:
- `KasirKu.exe` - File aplikasi utama (~200MB)
- `Jalankan KasirKu.bat` - Script untuk menjalankan dengan pengecekan PHP
- File DLL dan resources lainnya

---

## ⚙️ Prasyarat

### Di Komputer User:

1. **PHP 8.1+**
   - Download: https://windows.php.net/download/
   - Pastikan ada di PATH Windows
   - Cek: buka CMD, ketik `php -v`

2. **Database MySQL/SQLite**
   - Konfigurasi di file `.env`

---

## 🔧 Cara Build Ulang

Untuk build ulang aplikasi EXE:

```bash
# Build frontend assets
npm run build

# Build Electron app
npm run dist
```

Hasil: `dist-electron/win-unpacked/`

---

## ✨ Fitur

1. **🚀 Auto-start Server** - PHP Laravel otomatis berjalan
2. **📥 System Tray** - Minimize ke system tray
3. **⏳ Loading Screen** - Animasi saat memulai
4. **🔄 Smart Detection** - Deteksi server yang sudah berjalan

---

## ❓ Troubleshooting

### PHP tidak ditemukan
1. Download PHP dari https://windows.php.net/download/
2. Extract ke folder (misalnya `C:\php`)
3. Tambahkan ke PATH:
   - Buka Settings > System > About > Advanced System Settings
   - Environment Variables > PATH > Edit
   - Tambahkan path PHP (contoh: `C:\php`)
4. Restart komputer

### Aplikasi tidak berjalan
1. Pastikan PHP terinstall dan ada di PATH
2. Pastikan port 8000 tidak digunakan aplikasi lain
3. Coba jalankan `php artisan serve` manual untuk cek error

### Database error
1. Pastikan konfigurasi database di `.env` sudah benar
2. Jalankan `php artisan migrate` untuk membuat tabel

---

## 📝 Catatan

- Ukuran aplikasi ~200MB karena include Chromium (Electron)
- Server berjalan di `http://127.0.0.1:8000`
- Tutup dari system tray untuk keluar sepenuhnya
