@echo off
title He Thong Quan Ly Do An HUIT - Autostart
color 0A

echo ============================================================
echo   HE THONG QUAN LY DO AN & KHOA LUAN TOT NGHIEP - HUIT
echo ============================================================
echo.

if not exist .env (
    echo [*] Dang tao file cau hinh .env...
    copy .env.example .env > nul
    php artisan key:generate > nul
)

echo [*] Dang tao va nap du lieu mau vao CSDL MySQL (quanly_doan)...
php artisan migrate:fresh --seed --force

echo [*] Dang tao thiet lap storage link...
php artisan storage:link > nul 2>&1

echo [*] Dang xoa cache giao dien...
php artisan view:clear > nul 2>&1

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

php artisan serve
pause
