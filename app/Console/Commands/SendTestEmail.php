<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {type} {email} {--queue : Send via queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test emails (types: low-stock, order-confirmation, simple)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $email = $this->argument('email');
        $useQueue = $this->option('queue');

        $this->info("Sending {$type} test email to {$email}...");

        try {
            switch ($type) {
                case 'low-stock':
                    $this->sendLowStockAlert($email, $useQueue);
                    break;
                case 'order-confirmation':
                    $this->sendOrderConfirmation($email, $useQueue);
                    break;
                case 'simple':
                    $this->sendSimpleEmail($email, $useQueue);
                    break;
                default:
                    $this->error("Unknown email type: {$type}");
                    $this->line("Available types: low-stock, order-confirmation, simple");
                    return 1;
            }

            $this->info("Email sent successfully!");
            
            if ($useQueue) {
                $this->line("Email was queued. Run 'php artisan queue:work' to process it.");
            }

        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Send low stock alert test email
     */
    private function sendLowStockAlert(string $email, bool $useQueue)
    {
        $product = Product::with('inventory')->first();
        $admin = User::where('role', 'admin')->first();

        if (!$product || !$admin) {
            $this->error('No sample data available. Please seed the database first.');
            return;
        }

        $mailable = new LowStockAlert($product, $admin);
        
        if ($useQueue) {
            Mail::to($email)->queue($mailable);
        } else {
            Mail::to($email)->send($mailable);
        }
    }

    /**
     * Send order confirmation test email
     */
    private function sendOrderConfirmation(string $email, bool $useQueue)
    {
        $order = Order::with(['orderItems.product', 'user'])->first();

        if (!$order) {
            $this->error('No sample order available. Please create an order first.');
            return;
        }

        $mailable = new OrderConfirmation($order);
        
        if ($useQueue) {
            Mail::to($email)->queue($mailable);
        } else {
            Mail::to($email)->send($mailable);
        }
    }

    /**
     * Send simple test email
     */
    private function sendSimpleEmail(string $email, bool $useQueue)
    {
        $callback = function ($message) use ($email) {
            $message->to($email)
                    ->subject('UrPearl SHOP - Email Configuration Test')
                    ->html('
                        <h1>Email Test Successful!</h1>
                        <p>This is a test email from UrPearl SHOP.</p>
                        <p>If you received this email, your mail configuration is working correctly.</p>
                        <p><strong>Timestamp:</strong> ' . now()->format('Y-m-d H:i:s') . '</p>
                        <hr>
                        <p style="color: #666; font-size: 12px;">This is an automated test email.</p>
                    ');
        };

        if ($useQueue) {
            Mail::queue([], [], $callback);
        } else {
            Mail::send([], [], $callback);
        }
    }
}
