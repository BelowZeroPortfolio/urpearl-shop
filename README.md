# UrPearl SHOP 🛍️

A modern e-commerce platform built with Laravel, featuring product management, shopping cart, order processing, and admin dashboard.

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- Node.js & npm
- MySQL/MariaDB

### Option 1: Automated Setup (Recommended)

**Windows:**
```bash
# Run the quick start script
quick-start.bat
```

**Linux/macOS:**
```bash
# Make script executable and run
chmod +x quick-start.sh
./quick-start.sh
```

### Option 2: Manual Setup

1. **Install dependencies:**
```bash
composer install
npm install
```

2. **Environment setup:**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Database setup:**
```bash
# Configure your database in .env, then:
php artisan migrate
php artisan db:seed
```

4. **Build assets:**
```bash
npm run build
```

5. **Start server:**
```bash
php artisan serve
```

## 🔗 Access Points

- **Main Site**: http://localhost:8000
- **Admin Dashboard**: http://localhost:8000/admin/dashboard
- **Email Testing**: http://localhost:8000/dev/mail-test (development only)

## 🔐 Default Credentials

**Admin User:**
- Email: `admin@urpearl-shop.com`
- Password: `password`

**Regular User:**
- Email: `user@urpearl-shop.com`
- Password: `password`

## ✨ Features

### Customer Features
- 🛍️ Product browsing and search
- 🛒 Shopping cart management
- 📦 Order placement and tracking
- ⭐ Product ratings and reviews
- 🔐 Google OAuth authentication

### Admin Features
- 📊 Dashboard with analytics
- 📦 Product management (CRUD)
- 📋 Inventory management with low-stock alerts
- 🛒 Order management and status updates
- 🔔 Notification system
- 📧 Email notifications

### Technical Features
- 🎨 Responsive design with Tailwind CSS
- 📧 Queue-based email system
- 🔍 Advanced search and filtering
- 📱 Mobile-friendly interface
- 🛡️ Role-based access control
- 🧪 Comprehensive test suite

## 📁 Project Structure

```
urpearl-shop/
├── app/
│   ├── Http/Controllers/    # Request handlers
│   ├── Models/             # Database models
│   ├── Services/           # Business logic
│   ├── Mail/               # Email templates
│   └── Enums/              # Type definitions
├── database/
│   ├── migrations/         # Database schema
│   ├── seeders/           # Sample data
│   └── factories/         # Test data generators
├── resources/
│   ├── views/             # Blade templates
│   └── js/                # Frontend assets
├── routes/                # Route definitions
└── tests/                 # Test files
```

## 🛠️ Development

### Running Tests
```bash
php artisan test
```

### Queue Management
```bash
# Start queue worker
php artisan queue:work

# Monitor queues
php artisan queue:monitor
```

### Email Testing
```bash
# Send test emails
php artisan email:test simple test@example.com
php artisan email:test low-stock test@example.com
php artisan email:test order-confirmation test@example.com
```

### Frontend Development
```bash
# Watch for changes
npm run dev

# Build for production
npm run build
```

## 📚 Documentation

- **[Local Setup Guide](LOCAL_SETUP_GUIDE.md)** - Detailed setup instructions
- **[Email Configuration](EMAIL_CONFIGURATION.md)** - Email system setup
- **[Google OAuth Setup](GOOGLE_OAUTH_SETUP.md)** - OAuth configuration

## 🧪 Sample Data

The seeder creates:
- Admin and regular users
- Product categories (Electronics, Clothing, Books, etc.)
- Products with inventory
- Sample orders and cart items
- Product ratings and reviews
- Admin notifications

## 🔧 Configuration

### Database
Update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=urpearl_shop
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Email (Optional)
For email functionality:
```env
# Development (logs emails)
MAIL_MAILER=log

# Production (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### Google OAuth (Optional)
```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

## 🚨 Troubleshooting

### Common Issues

1. **Database connection errors**
   - Check MySQL is running
   - Verify credentials in `.env`

2. **Permission errors (Linux/macOS)**
   ```bash
   sudo chown -R $USER:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

3. **Frontend assets not loading**
   ```bash
   npm run build
   php artisan view:clear
   ```

4. **Class not found errors**
   ```bash
   composer dump-autoload
   ```

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `php artisan test`
5. Submit a pull request

---

**Happy Shopping! 🛍️**