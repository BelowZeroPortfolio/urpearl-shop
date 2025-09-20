# Email Configuration Guide

This guide explains how to configure and test the email system in UrPearl SHOP.

## Overview

The application uses Laravel's mail system with the following features:
- **Low Stock Alerts**: Automated notifications when inventory is low
- **Order Confirmations**: Email receipts sent to customers after purchase
- **Queue Support**: Emails are queued for better performance
- **Development Testing**: Built-in tools for testing email templates

## Configuration

### 1. Environment Variables

Update your `.env` file with the following email settings:

#### For Gmail SMTP (Production)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@urpearl-shop.com"
MAIL_FROM_NAME="UrPearl SHOP"
```

#### For Development (Mailpit)
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="UrPearl SHOP"
```

### 2. Queue Configuration

For better performance, emails are queued:

```env
QUEUE_CONNECTION=database
```

### 3. Gmail App Password Setup

1. Enable 2-Factor Authentication on your Gmail account
2. Go to Google Account settings > Security > App passwords
3. Generate an app password for "Mail"
4. Use this app password in `MAIL_PASSWORD` (not your regular Gmail password)

## Email Templates

### Low Stock Alert
- **File**: `resources/views/emails/low-stock-alert.blade.php`
- **Triggered**: When inventory reaches low stock threshold
- **Recipients**: Admin users
- **Features**: Product details, current stock, threshold, action buttons

### Order Confirmation
- **File**: `resources/views/emails/order-confirmation.blade.php`
- **Triggered**: After successful order creation
- **Recipients**: Customer who placed the order
- **Features**: Order details, items, shipping address, status tracking

## Queue Management

### Running the Queue Worker

Start the queue worker to process email jobs:

```bash
php artisan queue:work
```

### For Development
```bash
php artisan queue:work --timeout=60
```

### For Production (with Supervisor)
```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

### Queue Monitoring

Use the custom queue monitor command:

```bash
# Check queue status
php artisan queue:monitor

# Clear failed jobs
php artisan queue:monitor --clear
```

### Database Setup

Create the jobs table:

```bash
php artisan queue:table
php artisan migrate
```

## Development Testing

### Email Testing Interface

Access the email testing interface (development only):
```
http://your-app.local/dev/mail-test
```

Features:
- Preview email templates
- Send test emails
- Check mail configuration
- View sample data requirements

### Manual Testing Commands

```bash
# Test mail configuration
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });

# Send test low stock alert
>>> $product = App\Models\Product::first();
>>> $admin = App\Models\User::where('role', 'admin')->first();
>>> Mail::to('test@example.com')->send(new App\Mail\LowStockAlert($product, $admin));

# Send test order confirmation
>>> $order = App\Models\Order::first();
>>> Mail::to('test@example.com')->send(new App\Mail\OrderConfirmation($order));
```

## Troubleshooting

### Common Issues

1. **Gmail Authentication Failed**
   - Ensure 2FA is enabled
   - Use App Password, not regular password
   - Check if "Less secure app access" is disabled (it should be)

2. **Emails Not Sending**
   - Check queue worker is running: `php artisan queue:work`
   - Verify mail configuration: Visit `/dev/mail-test/config`
   - Check failed jobs: `php artisan queue:monitor`

3. **Queue Jobs Failing**
   - Check logs: `storage/logs/laravel.log`
   - Restart queue worker: `php artisan queue:restart`
   - Clear failed jobs: `php artisan queue:monitor --clear`

4. **Templates Not Loading**
   - Clear view cache: `php artisan view:clear`
   - Check file permissions on `resources/views/emails/`

### Log Files

Check these log files for debugging:
- `storage/logs/laravel.log` - Application logs
- Queue worker output - Console output when running `queue:work`

### Testing Checklist

- [ ] Mail configuration is correct
- [ ] Queue worker is running
- [ ] Database has jobs and failed_jobs tables
- [ ] Email templates render correctly
- [ ] Test emails are received
- [ ] Low stock alerts trigger properly
- [ ] Order confirmations send after purchase

## Production Deployment

### Supervisor Configuration

Create `/etc/supervisor/conf.d/urpearl-queue.conf`:

```ini
[program:urpearl-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/urpearl-shop/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/urpearl-shop/storage/logs/queue.log
stopwaitsecs=3600
```

### Restart Commands

```bash
# Restart supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start urpearl-queue:*

# Restart queue workers
php artisan queue:restart
```

## Email Templates Customization

### Styling Guidelines

The email templates use inline CSS for maximum compatibility:
- **Colors**: Pink (#ec4899), Beige (#f5f5dc), White (#ffffff)
- **Fonts**: Arial, Segoe UI for compatibility
- **Layout**: Responsive design with max-width 600px
- **Branding**: UrPearl SHOP logo and consistent styling

### Customizing Templates

1. Edit template files in `resources/views/emails/`
2. Test changes using `/dev/mail-test`
3. Clear view cache: `php artisan view:clear`
4. Send test emails to verify appearance

### Adding New Email Types

1. Create new Mailable class: `php artisan make:mail YourEmailName`
2. Create template in `resources/views/emails/your-email.blade.php`
3. Add preview/test routes in `MailTestController`
4. Update this documentation

## Security Considerations

- Never commit email credentials to version control
- Use environment variables for all sensitive data
- Implement rate limiting for email sending
- Monitor for spam/abuse patterns
- Use queue workers to prevent blocking web requests
- Regularly rotate email credentials