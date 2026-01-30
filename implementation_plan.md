# Rencana Implementasi Deployment Vercel

Tujuan: Mempersiapkan aplikasi Laravel agar siap di-deploy ke Vercel melalui GitHub.

## User Review Required
> [!NOTE]
> Pastikan Anda memiliki akun Vercel yang terhubung dengan GitHuB Anda.

## Proposed Changes

### Configuration
#### [NEW] [vercel.json](file:///c:/Users/eshaq/OneDrive/Dokumen/fati%20vscode/kasirku/vercel.json)
- Konfigurasi untuk mengatur routing request ke `api/index.php`.
- Menyetel environment variable yang dibutuhkan (APP_KEY, dll nanti diset di dashboard Vercel).

#### [NEW] [api/index.php](file:///c:/Users/eshaq/OneDrive/Dokumen/fati%20vscode/kasirku/api/index.php)
- Entry point khusus untuk Vercel Serverless Function, memuat Laravel framework.

### Documentation
#### [NEW] [PANDUAN_PRODUKSI_VERCEL.md](file:///c:/Users/eshaq/OneDrive/Dokumen/fati%20vscode/kasirku/PANDUAN_PRODUKSI_VERCEL.md)
- Panduan langkah demi langkah deploy ke Vercel via GitHub.
- Cara setting Environment Variable di Vercel Dashboard.

## Verification Plan

### Manual Verification
- User akan diminta untuk push ke GitHub.
- User akan diminta Import Project di Vercel.
- User memverifikasi bahwa aplikasi berjalan di URL Vercel.
