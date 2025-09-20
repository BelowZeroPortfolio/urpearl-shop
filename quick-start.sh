#!/bin/bash

echo "========================================"
echo "UrPearl SHOP - Quick Start Script"
echo "========================================"
echo

echo "[1/8] Installing PHP dependencies..."
composer install
if [ $? -ne 0 ]; then
    echo "ERROR: Composer install failed"
    exit 1
fi

echo "[2/8] Installing Node.js dependencies..."
npm install
if [ $? -ne 0 ]; then
    echo "ERROR: npm install failed"
    exit 1
fi

echo "[3/8] Copying environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Environment file created from .env.example"
else
    echo "Environment file already exists"
fi

echo "[4/8] Generating application key..."
php artisan key:generate

echo "[5/8] Running database migrations..."
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo "ERROR: Database migration failed"
    echo "Please check your database configuration in .env"
    exit 1
fi

echo "[6/8] Seeding database with sample data..."
php artisan db:seed
if [ $? -ne 0 ]; then
    echo "ERROR: Database seeding failed"
    exit 1
fi

echo "[7/8] Building frontend assets..."
npm run build
if [ $? -ne 0 ]; then
    echo "ERROR: Frontend build failed"
    exit 1
fi

echo "[8/8] Setup complete!"
echo
echo "========================================"
echo "SUCCESS! UrPearl SHOP is ready to run"
echo "========================================"
echo
echo "Default login credentials:"
echo "Admin: admin@urpearl-shop.com / password"
echo "User:  user@urpearl-shop.com / password"
echo
echo "To start the development server:"
echo "  php artisan serve"
echo
echo "Then visit: http://localhost:8000"
echo "Admin panel: http://localhost:8000/admin/dashboard"
echo "Email testing: http://localhost:8000/dev/mail-test"
echo