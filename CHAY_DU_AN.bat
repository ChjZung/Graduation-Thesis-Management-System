@echo off
title He Thong Quan Ly Do An HUIT - Autostart
color 0A

echo ============================================================
echo   HE THONG QUAN LY DO AN & KHOA LUAN TOT NGHIEP - HUIT
echo ============================================================
echo.

set PHP_EXE=php
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" (
        set PHP_EXE="C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe"
    )
)

if not exist .env (
    echo [*] Dang tao file cau hinh .env...
    copy .env.example .env > nul
    %PHP_EXE% artisan key:generate > nul
)

echo [*] Dang tao va nap du lieu mau vao CSDL MySQL (quanly_doan)...
%PHP_EXE% artisan migrate:fresh --seed --force

echo [*] Dang tao thiet lap storage link...
%PHP_EXE% artisan storage:link > nul 2>&1

echo [*] Dang xoa cache giao dien...
%PHP_EXE% artisan view:clear > nul 2>&1

echo.
echo ============================================================
echo [OK] KHOI DONG THANH CONG!
echo.
echo  👉 DANG CHAY TAI: http://localhost:8000
echo.
echo  🔑 Tai khoan dang nhap mau:
echo     - Admin:      admin  / 123456
echo     - Giang vien: gv01   / 123456
echo     - Sinh vien:  sv01   / 123456
echo.
echo  (Vui long KHONG TAT cua so nay trong khi dang dung web)
echo ============================================================
echo.

%PHP_EXE% artisan serve
pause
