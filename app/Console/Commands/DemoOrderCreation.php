<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OrderService;
use App\Services\InventoryService;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;

class DemoOrderCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:order-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate order creation and inventory management integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Order Creation Demo...');
        
        try {
            // Create services
            $inventoryService = new InventoryService();
            $orderService = new OrderService($inventoryService);
            
            $this->info('✅ Services instantiated successfully');
            
            // Create demo data if not exists
            $this->createDemoData();
            
            // Get demo user and product
            $user = User::where('email', 'demo@example.com')->first();
            $product = Product::where('sku', 'DEMO-001')->first();
            
            if (!$user || !$product) {
                $this->error('❌ Demo data not found. Please run migrations and seeders first.');
                return 1;
            }
            
            $this->info("📦 Product: {$product->name} (Stock: {$product->inventory->quantity})");
            
            // Demonstrate order creation from cart
            $this->info('🛒 Adding item to cart...');
            
            // Clear existing cart items
            $user->cartItems()->delete();
            
            // Add item to cart
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => 2,
            ]);
            
            $this->info('✅ Item added to cart');
            
            // Create order from cart
            $this->info('📋 Creating order from cart...');
            
            $shippingAddress = [
                'name' => 'Demo User',
                'address_line_1' => '123 Demo Street',
                'city' => 'Demo City',
                'state' => 'Demo State',
                'postal_code' => '12345',
                'country' => 'Demo Country',
            ];
            
            $order = $orderService->createOrderFromCart($user, $shippingAddress);
            
            $this->info("✅ Order created successfully!");
            $this->info("   Order ID: {$order->id}");
            $this->info("   Total Amount: ₱{$order->total_amount}");
            $this->info("   Status: {$order->status->value}");
            
            // Check inventory after order
            $product->inventory->refresh();
            $this->info("📦 Updated Stock: {$product->inventory->quantity}");
            
            // Check if low stock notification was created
            $lowStockNotifications = \App\Models\Notification::where('type', \App\Enums\NotificationType::LOW_STOCK)
                ->whereJsonContains('payload->product_id', $product->id)
                ->count();
                
            if ($lowStockNotifications > 0) {
                $this->warn("⚠️  Low stock notification created for product");
            }
            
            // Demonstrate order statistics
            $stats = $orderService->getOrderStats();
            $this->info('📊 Order Statistics:');
            $this->info("   Total Orders: {$stats['total_orders']}");
            $this->info("   Pending Orders: {$stats['pending_orders']}");
            $this->info("   Total Revenue: ₱{$stats['total_revenue']}");
            
            $this->info('🎉 Demo completed successfully!');
            
        } catch (\Exception $e) {
            $this->error("❌ Demo failed: {$e->getMessage()}");
            return 1;
        }
        
        return 0;
    }
    
    private function createDemoData()
    {
        DB::transaction(function () {
            // Create demo user if not exists
            $user = User::firstOrCreate(
                ['email' => 'demo@example.com'],
                [
                    'name' => 'Demo User',
                    'role' => UserRole::BUYER,
                ]
            );
            
            // Create demo category if not exists
            $category = Category::firstOrCreate(
                ['slug' => 'demo-category'],
                ['name' => 'Demo Category']
            );
            
            // Create demo product if not exists
            $product = Product::firstOrCreate(
                ['sku' => 'DEMO-001'],
                [
                    'name' => 'Demo Product',
                    'slug' => 'demo-product',
                    'description' => 'This is a demo product for testing order creation',
                    'price' => 150.00,
                    'category_id' => $category->id,
                ]
            );
            
            // Create inventory if not exists
            Inventory::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity' => 8,
                    'low_stock_threshold' => 5,
                ]
            );
            
            // Create admin user for notifications
            User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Demo Admin',
                    'role' => UserRole::ADMIN,
                ]
            );
        });
        
        $this->info('✅ Demo data created/verified');
    }
}