@echo off
echo ========================================
echo UrPearl SHOP - Quick Start Script
echo ========================================
echo.

echo [1/8] Installing PHP dependencies...
composer install
if %errorlevel% neq 0 (
    echo ERROR: Composer install failed
    pause
    exit /b 1
)

echo [2/8] Installing Node.js dependencies...
npm install
if %errorlevel% neq 0 (
    echo ERROR: npm install failed
    pause
    exit /b 1
)

echo [3/8] Copying environment file...
if not exist .env (
    copy .env.example .env
    echo Environment file created from .env.example
) else (
    echo Environment file already exists
)

echo [4/8] Generating application key...
php artisan key:generate

echo [5/8] Running database migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo ERROR: Database migration failed
    echo Please check your database configuration in .env
    pause
    exit /b 1
)

echo [6/8] Seeding database with sample data...
php artisan db:seed
if %errorlevel% neq 0 (
    echo ERROR: Database seeding failed
    pause
    exit /b 1
)

echo [7/8] Building frontend assets...
npm run build
if %errorlevel% neq 0 (
    echo ERROR: Frontend build failed
    pause
    exit /b 1
)

echo [8/8] Setup complete!
echo.
echo ========================================
echo SUCCESS! UrPearl SHOP is ready to run
echo ========================================
echo.
echo Default login credentials:
echo Admin: admin@urpearl-shop.com / password
echo User:  user@urpearl-shop.com / password
echo.
echo To start the development server:
echo   php artisan serve
echo.
echo Then visit: http://localhost:8000
echo Admin panel: http://localhost:8000/admin/dashboard
echo Email testing: http://localhost:8000/dev/mail-test
echo.
pause