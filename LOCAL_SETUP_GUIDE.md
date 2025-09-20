# UrPearl SHOP - Local Development Setup Guide

This guide will help you set up and run the UrPearl SHOP project on your local machine.

## Prerequisites

Make sure you have the following installed:

- **PHP 8.1+** with extensions: mbstring, xml, ctype, json, bcmath, openssl, pdo, tokenizer, fileinfo
- **Composer** (PHP dependency manager)
- **Node.js & npm** (for frontend assets)
- **MySQL 8.0+** or **MariaDB 10.3+**
- **Git**

### Windows Installation
```bash
# Install PHP, Composer, Node.js from their official websites
# Or use Chocolatey:
choco install php composer nodejs mysql

# Or use XAMPP/WAMP for an all-in-one solution
```

### macOS Installation
```bash
# Using Homebrew:
brew install php composer node mysql
```

### Linux (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install php8.1 php8.1-cli php8.1-mbstring php8.1-xml php8.1-mysql php8.1-zip php8.1-curl composer nodejs npm mysql-server
```

## Step-by-Step Setup

### 1. Clone and Navigate to Project
```bash
# If you haven't cloned yet:
git clone <repository-url>
cd urpearl-shop
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Node.js Dependencies
```bash
npm install
```

### 4. Environment Configuration
```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database

#### Option A: Using MySQL Command Line
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE urpearl_shop;
CREATE USER 'urpearl_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON urpearl_shop.* TO 'urpearl_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Option B: Using the provided setup script
```bash
# Run the database setup script
php create_db.php
```

### 6. Update .env File

Edit your `.env` file with your database credentials:

```env
APP_NAME="UrPearl SHOP"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=urpearl_shop
DB_USERNAME=urpearl_user
DB_PASSWORD=your_password

# For development - use sync queue
QUEUE_CONNECTION=sync

# For email testing - use log driver
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@urpearl-shop.local"
MAIL_FROM_NAME="UrPearl SHOP"
```

### 7. Run Database Migrations and Seeders
```bash
# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

### 8. Build Frontend Assets
```bash
# For development
npm run dev

# Or for production build
npm run build
```

### 9. Start the Development Server
```bash
# Start Laravel development server
php artisan serve
```

Your application will be available at: **http://localhost:8000**

## Optional: Advanced Setup

### Google OAuth Setup (Optional)
If you want to test Google login:

1. Follow the guide in `GOOGLE_OAUTH_SETUP.md`
2. Update your `.env` with Google credentials:
```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### Email Testing Setup (Optional)

#### Option A: Use Mailpit (Recommended for Development)
```bash
# Install Mailpit
go install github.com/axllent/mailpit@latest

# Or download from: https://github.com/axllent/mailpit/releases

# Run Mailpit
mailpit

# Update .env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

Access Mailpit web interface at: http://localhost:8025

#### Option B: Use Log Driver (Simplest)
```env
MAIL_MAILER=log
```
Emails will be logged to `storage/logs/laravel.log`

### Queue Setup (Optional)
For better performance with emails and background jobs:

```bash
# Update .env
QUEUE_CONNECTION=database

# Run queue worker
php artisan queue:work
```

## Testing the Application

### 1. Access the Application
- **Frontend**: http://localhost:8000
- **Admin Dashboard**: http://localhost:8000/admin/dashboard
- **Email Testing** (dev only): http://localhost:8000/dev/mail-test

### 2. Default Login Credentials
After seeding, you should have:

**Admin User:**
- Email: admin@urpearl-shop.com
- Password: password

**Regular User:**
- Email: user@urpearl-shop.com  
- Password: password

### 3. Test Key Features
- Browse products
- Add items to cart
- Place orders
- Admin product management
- Inventory management
- Email notifications

## Development Commands

### Useful Artisan Commands
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Run tests
php artisan test

# Monitor queues
php artisan queue:monitor

# Send test emails
php artisan email:test simple test@example.com

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Frontend Development
```bash
# Watch for changes (hot reload)
npm run dev

# Build for production
npm run build
```

## Troubleshooting

### Common Issues

#### 1. "Class not found" errors
```bash
composer dump-autoload
```

#### 2. Permission errors (Linux/macOS)
```bash
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### 3. Database connection errors
- Check MySQL is running
- Verify database credentials in `.env`
- Ensure database exists

#### 4. Frontend assets not loading
```bash
npm run build
php artisan view:clear
```

#### 5. Email not working
- Check mail configuration in `.env`
- For development, use `MAIL_MAILER=log`
- Check `storage/logs/laravel.log` for errors

### Getting Help

1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode: `APP_DEBUG=true` in `.env`
3. Run with verbose output: `php artisan serve --verbose`

## Project Structure

```
urpearl-shop/
├── app/                    # Application code
│   ├── Http/Controllers/   # Controllers
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic
│   └── Mail/              # Email classes
├── database/              # Migrations, seeders, factories
├── resources/             # Views, assets
│   ├── views/             # Blade templates
│   └── js/                # Frontend JavaScript
├── routes/                # Route definitions
├── storage/               # Logs, cache, uploads
└── tests/                 # Test files
```

## Next Steps

Once you have the project running:

1. Explore the codebase
2. Check out the admin dashboard
3. Test the shopping cart functionality
4. Try the email testing interface
5. Review the API endpoints
6. Run the test suite

Happy coding! 🚀