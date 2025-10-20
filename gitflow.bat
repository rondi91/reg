@echo off
:: GitFlow sederhana untuk proyek Wireless Monitor
:: Jalankan: gitflow [opsi]
:: Opsi: init | commit | push | status | feature <nama>

setlocal enabledelayedexpansion

set ACTION=%1
set FEATURE=%2
set DATE=%date% %time%
set LOGFILE=learning-log.txt

if "%ACTION%"=="" (
    echo.
    echo 🔧 GitFlow Usage:
    echo   gitflow init           = Inisialisasi branch main/dev
    echo   gitflow commit         = Commit otomatis semua perubahan
    echo   gitflow push           = Push ke GitHub
    echo   gitflow feature <nama> = Buat branch fitur baru
    echo   gitflow status         = Lihat status repo
    exit /b
)

if "%ACTION%"=="init" (
    echo 🌀 Inisialisasi Git repository...
    git init
    git branch -M main
    git checkout -b dev
    echo [%DATE%] Repository diinisialisasi (main & dev dibuat) >> %LOGFILE%
    echo Selesai inisialisasi.
    exit /b
)

if "%ACTION%"=="commit" (
    echo 💾 Menyimpan perubahan ke Git...
    git add .
    git commit -m "Auto commit pada %DATE%"
    echo [%DATE%] Auto commit tersimpan. >> %LOGFILE%
    exit /b
)

if "%ACTION%"=="push" (
    echo 🚀 Push ke GitHub...
    git push
    echo [%DATE%] Push ke remote repository. >> %LOGFILE%
    exit /b
)

if "%ACTION%"=="feature" (
    if "%FEATURE%"=="" (
        echo ❌ Masukkan nama fitur. Contoh:
        echo    gitflow feature live-search
        exit /b
    )
    echo 🌱 Membuat branch fitur: %FEATURE%
    git checkout -b feature-%FEATURE%
    echo [%DATE%] Branch fitur feature-%FEATURE% dibuat. >> %LOGFILE%
    exit /b
)

if "%ACTION%"=="status" (
    git status
    exit /b
)

echo ❌ Perintah tidak dikenal: %ACTION%
