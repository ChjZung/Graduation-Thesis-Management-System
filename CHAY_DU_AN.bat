@echo off
setlocal
cd /d "%~dp0"
title He Thong Quan Ly Khoa Luan HUIT
set "APP_PHP="
for /f "delims=" %%P in ('where php 2^>nul') do call :try_php "%%P"
if not defined APP_PHP for /d %%D in ("C:\laragon\bin\php\php-*") do call :try_php "%%D\php.exe"
if not defined APP_PHP (
    echo Can PHP 8.3 tro len. Hay kiem tra Laragon.
    pause
    exit /b 1
)
if not exist .env (
    echo Chua co .env. Hay cau hinh du an truoc khi khoi dong.
    pause
    exit /b 1
)
if not exist vendor\autoload.php (
    echo Chua co thu vien PHP. Hay chay composer install.
    pause
    exit /b 1
)
if not exist storage\logs mkdir storage\logs
"%APP_PHP%" artisan config:clear
"%APP_PHP%" artisan view:clear
"%APP_PHP%" artisan storage:link >nul 2>nul
rem ai:work owns a single-worker lock; repeated launches cannot create duplicates.
start "" /B "%APP_PHP%" artisan ai:work >> storage\logs\ai-worker-launcher.log 2>&1
echo.
echo Web: http://127.0.0.1:8000
echo AI chay nen; tien do va ket qua duoc luu theo tung lan nop.
echo Du lieu hien co duoc giu nguyen. Migration phai thuc hien rieng khi can.
echo Giu cua so nay mo trong khi su dung. Ctrl+C de dung web.
"%APP_PHP%" artisan serve --host=127.0.0.1 --port=8000
exit /b %errorlevel%

:try_php
if defined APP_PHP exit /b
if not exist "%~1" exit /b
"%~1" -r "exit((int)(PHP_VERSION_ID < 80300));" >nul 2>nul
if not errorlevel 1 set "APP_PHP=%~1"
exit /b
