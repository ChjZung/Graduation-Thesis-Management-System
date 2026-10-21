@echo off
setlocal
cd /d "%~dp0"
title HUIT - Xu ly bao cao AI
set "AI_PHP="
for /f "delims=" %%P in ('where php 2^>nul') do call :try_php "%%P"
if not defined AI_PHP for /d %%D in ("C:\laragon\bin\php\php-*") do call :try_php "%%D\php.exe"
if not defined AI_PHP (
    echo Khong tim thay PHP 8.3 tro len. Hay mo Laragon Terminal va chay: php artisan ai:work
    pause
    exit /b 1
)
echo ============================================================
echo   DANG BAT TIEN TRINH XU LY BAO CAO AI
echo   Giu cua so nay mo khi su dung AI. Ctrl+C de dung.
echo   Khong can nop lai bai dang cho. Khong lam moi database.
echo ============================================================
"%AI_PHP%" artisan ai:work
set "AI_EXIT=%errorlevel%"
echo.
echo Tien trinh da dung. Neu bi loi, gui noi dung hien thi nay de kiem tra.
pause
exit /b %AI_EXIT%

:try_php
if defined AI_PHP exit /b
if not exist "%~1" exit /b
"%~1" -r "exit((int)(PHP_VERSION_ID < 80300));" >nul 2>nul
if not errorlevel 1 set "AI_PHP=%~1"
exit /b
