<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Rating;
use App\Models\Notification;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Seed the database with comprehensive test data.
     * This seeder creates more extensive data for testing purposes.
     */
    public function run(): void
    {
        $this->command->info('🧪 Creating comprehensive test data...');

        // Create test admin user
        $testAdmin = User::factory()->admin()->create([
            'name' => 'Test Admin',
            'email' => 'test-admin@urpearl.com',
            'password' => bcrypt('password'),
        ]);

        // Create test buyer user
        $testBuyer = User::factory()->buyer()->create([
            'name' => 'Test Buyer',
            'email' => 'test-buyer@urpearl.com',
            'password' => bcrypt('password'),
        ]);

        // Create more buyers for testing
        $buyers = User::factory(50)->buyer()->create();
        $googleBuyers = User::factory(10)->buyer()->withGoogle()->create();

        // Ensure categories exist
        if (Category::count() === 0) {
            $this->call(CategorySeeder::class);
        }
        $categories = Category::all();

        // Create extensive product catalog
        $this->command->info('Creating extensive product catalog...');
        $products = collect();
        
        foreach ($categories as $category) {
            // Create 8-12 products per category for better testing
            $categoryProducts = Product::factory(rand(8, 12))
                ->forCategory($category->id)
                ->create();
            
            $products = $products->merge($categoryProducts);
            
            // Create some expensive products
            $expensiveProducts = Product::factory(2)
                ->expensive()
                ->forCategory($category->id)
                ->create();
            
            $products = $products->merge($expensiveProducts);
            
            // Create some affordable products
            $affordableProducts = Product::factory(3)
                ->affordable()
                ->forCategory($category->id)
                ->create();
            
            $products = $products->merge($affordableProducts);
        }

        // Create inventory with various stock levels
        $this->command->info('Setting up inventory with various stock levels...');
        foreach ($products as $product) {
            $stockType = rand(1, 10);
            
            if ($stockType <= 2) {
                // 20% out of stock
                Inventory::factory()->outOfStock()->create(['product_id' => $product->id]);
            } elseif ($stockType <= 4) {
                // 20% low stock
                Inventory::factory()->lowStock()->create(['product_id' => $product->id]);
            } else {
                // 60% in stock
                Inventory::factory()->inStock()->create(['product_id' => $product->id]);
            }
        }

        // Create comprehensive order scenarios
        $this->command->info('Creating comprehensive order scenarios...');
        $allBuyers = $buyers->merge($googleBuyers)->push($testBuyer);
        
        foreach ($allBuyers as $buyer) {
            $orderCount = rand(0, 5); // Some buyers have no orders
            
            for ($i = 0; $i < $orderCount; $i++) {
                $status = collect(OrderStatus::cases())->random();
                
                $order = Order::factory()->create([
                    'user_id' => $buyer->id,
                    'status' => $status,
                ]);
                
                // Create order items
                $orderProducts = $products->random(rand(1, 6));
                $totalAmount = 0;
                
                foreach ($orderProducts as $product) {
                    $quantity = rand(1, 4);
                    $price = $product->price;
                    $itemTotal = $quantity * $price;
                    $totalAmount += $itemTotal;
                    
                    OrderItem::factory()->create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'total' => $itemTotal,
                    ]);
                }
                
                $order->update(['total_amount' => $totalAmount]);
            }
        }

        // Create extensive cart scenarios
        $this->command->info('Creating cart scenarios...');
        foreach ($allBuyers->random(30) as $buyer) {
            $cartProducts = $products->random(rand(1, 8));
            
            foreach ($cartProducts as $product) {
                // Only add to cart if product is in stock
                if ($product->inventory && $product->inventory->quantity > 0) {
                    CartItem::factory()->create([
                        'user_id' => $buyer->id,
                        'product_id' => $product->id,
                        'quantity' => min(rand(1, 5), $product->inventory->quantity),
                    ]);
                }
            }
        }

        // Create comprehensive rating scenarios
        $this->command->info('Creating comprehensive rating scenarios...');
        $paidOrders = Order::where('status', OrderStatus::PAID)->with('orderItems')->get();
        
        // Verified purchase ratings
        foreach ($paidOrders as $order) {
            $ratingChance = rand(1, 100);
            
            if ($ratingChance <= 60) { // 60% chance to rate
                foreach ($order->orderItems as $orderItem) {
                    $ratingType = rand(1, 10);
                    
                    if ($ratingType <= 4) {
                        // 40% five star ratings
                        Rating::factory()->fiveStars()->verifiedPurchase()->create([
                            'user_id' => $order->user_id,
                            'product_id' => $orderItem->product_id,
                        ]);
                    } elseif ($ratingType <= 6) {
                        // 20% one star ratings
                        Rating::factory()->oneStar()->verifiedPurchase()->create([
                            'user_id' => $order->user_id,
                            'product_id' => $orderItem->product_id,
                        ]);
                    } else {
                        // 40% random ratings
                        Rating::factory()->verifiedPurchase()->create([
                            'user_id' => $order->user_id,
                            'product_id' => $orderItem->product_id,
                        ]);
                    }
                }
            }
        }

        // Unverified ratings
        foreach ($products->random(50) as $product) {
            Rating::factory()->unverifiedPurchase()->create([
                'user_id' => $allBuyers->random()->id,
                'product_id' => $product->id,
            ]);
        }

        // Create comprehensive notification scenarios
        $this->command->info('Creating notification scenarios...');
        $adminUsers = User::where('role', UserRole::ADMIN)->get();
        
        foreach ($adminUsers as $admin) {
            // Low stock notifications
            $lowStockProducts = Product::whereHas('inventory', function ($query) {
                $query->whereRaw('quantity <= low_stock_threshold');
            })->get();
            
            foreach ($lowStockProducts->random(min(10, $lowStockProducts->count())) as $product) {
                Notification::factory()->lowStock()->create([
                    'user_id' => $admin->id,
                    'payload' => [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'current_stock' => $product->inventory->quantity,
                        'threshold' => $product->inventory->low_stock_threshold,
                    ],
                ]);
            }
            
            // Order notifications
            $recentOrders = Order::latest()->take(20)->get();
            foreach ($recentOrders->random(10) as $order) {
                Notification::factory()->orderCreated()->create([
                    'user_id' => $admin->id,
                    'payload' => [
                        'order_id' => $order->id,
                        'customer_name' => $order->user->name,
                        'total_amount' => $order->total_amount,
                    ],
                ]);
            }
            
            // Order status change notifications
            foreach ($recentOrders->random(5) as $order) {
                Notification::factory()->orderStatusChanged()->create([
                    'user_id' => $admin->id,
                    'payload' => [
                        'order_id' => $order->id,
                        'old_status' => 'pending',
                        'new_status' => $order->status->value,
                    ],
                ]);
            }
            
            // Mix of read and unread notifications
            Notification::factory(5)->read()->create(['user_id' => $admin->id]);
            Notification::factory(8)->unread()->create(['user_id' => $admin->id]);
        }

        $this->command->info('✅ Comprehensive test data created successfully!');
        $this->command->info("Final counts:");
        $this->command->info("- " . User::count() . " users");
        $this->command->info("- " . Category::count() . " categories");
        $this->command->info("- " . Product::count() . " products");
        $this->command->info("- " . Order::count() . " orders");
        $this->command->info("- " . OrderItem::count() . " order items");
        $this->command->info("- " . CartItem::count() . " cart items");
        $this->command->info("- " . Rating::count() . " ratings");
        $this->command->info("- " . Notification::count() . " notifications");
        
        $this->command->info("Test accounts created:");
        $this->command->info("- Admin: test-admin@urpearl.com / password");
        $this->command->info("- Buyer: test-buyer@urpearl.com / password");
    }
}