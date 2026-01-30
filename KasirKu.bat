@echo off
title KasirKu - Point of Sale
color 0A

echo ==========================================
echo        KasirKu - Point of Sale
echo ==========================================
echo.

REM Check if PHP is available
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] PHP tidak ditemukan!
    echo Pastikan PHP sudah terinstall dan ada di PATH.
    echo.
    echo Download PHP: https://windows.php.net/download/
    pause
    exit /b 1
)

echo [OK] PHP ditemukan

REM Check if node_modules exists
if not exist "node_modules" (
    echo.
    echo [INFO] Menginstall dependencies...
    call npm install
)

echo.
echo [INFO] Memulai aplikasi...
echo.

REM Start Electron
call npx electron .

exit /b 0
