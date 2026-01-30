@echo off
title Build KasirKu
color 0B

echo ==========================================
echo     Build KasirKu Desktop App
echo ==========================================
echo.

REM Check prerequisites
where php >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] PHP tidak ditemukan!
    pause
    exit /b 1
)

where node >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Node.js tidak ditemukan!
    pause
    exit /b 1
)

echo [OK] Prerequisites terpenuhi
echo.

REM Install dependencies if needed
if not exist "node_modules" (
    echo [INFO] Menginstall npm dependencies...
    call npm install
    echo.
)

REM Build frontend assets
echo [INFO] Building frontend assets...
call npm run build
echo.

REM Build Electron app
echo [INFO] Building Electron app...
echo Ini mungkin memakan waktu beberapa menit...
echo.
call npm run dist

echo.
echo ==========================================
echo Build selesai!
echo File installer ada di folder: dist-electron
echo ==========================================
echo.
pause
