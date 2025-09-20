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
use App\Enums\NotificationType;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // Clear existing data
        $this->command->info('Clearing existing data...');
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('inventories')->truncate();
        \DB::table('order_items')->truncate();
        \DB::table('cart_items')->truncate();
        \DB::table('ratings')->truncate();
        \DB::table('products')->truncate();
        \DB::table('categories')->truncate();
        \DB::table('notifications')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed users and categories
        $this->call([
            AdminUserSeeder::class,
            CustomerUserSeeder::class,
            CategorySeeder::class,  // This will create all our clothing categories
        ]);

        // Create regular users
        $this->command->info('Creating regular users...');
        $users = User::factory(5)->create([
            'role' => UserRole::BUYER,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $categories = Category::all();
        $products = collect();

        // Sample clothing products
        $this->command->info('Creating sample clothing products...');
        $sampleProducts = [
            // T-Shirts
            [
                'name' => 'Classic White T-Shirt',
                'description' => '100% cotton classic fit t-shirt. Comfortable and versatile for any casual occasion.',
                'price' => 24.99,
                'category_id' => $categories->where('slug', 't-shirts')->first()->id,
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
                'is_new_arrival' => true,
                'is_best_seller' => true,
            ],
            [
                'name' => 'Black Oversized T-Shirt',
                'description' => 'Oversized fit black t-shirt made from soft cotton. Perfect for a relaxed look.',
                'price' => 29.99,
                'category_id' => $categories->where('slug', 't-shirts')->first()->id,
                'image' => 'https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
                'is_new_arrival' => true,
            ],
            // Jeans
            [
                'name' => 'Slim Fit Blue Jeans',
                'description' => 'Classic blue denim jeans with a modern slim fit. 98% cotton, 2% elastane for comfort.',
                'price' => 59.99,
                'category_id' => $categories->where('slug', 'jeans')->first()->id,
                'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
                'is_best_seller' => true,
            ],
            // Dresses
            [
                'name' => 'Summer Floral Dress',
                'description' => 'Lightweight floral dress perfect for summer. Made from breathable fabric with a comfortable fit.',
                'price' => 49.99,
                'category_id' => $categories->where('slug', 'dresses')->first()->id,
                'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
                'is_new_arrival' => true,
                'is_best_seller' => true,
            ],
            // Jackets
            [
                'name' => 'Classic Denim Jacket',
                'description' => 'Timeless denim jacket with a regular fit. Features front button closure and multiple pockets.',
                'price' => 79.99,
                'category_id' => $categories->where('slug', 'jackets')->first()->id,
                'image' => 'https://images.unsplash.com/photo-1551028719-001e63a0d1f8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80',
            ],
        ];

        // Create products with inventory
        foreach ($sampleProducts as $productData) {
            // Generate slug from name if not provided
            if (!isset($productData['slug'])) {
                $productData['slug'] = Str::slug($productData['name']);
            }
            
            $product = Product::create($productData);
            
            // Create inventory record
            $product->inventory()->create([
                'quantity' => rand(5, 50),
                'low_stock_threshold' => 5,
            ]);
            
            $products->push($product);
        }

        // Create some low stock products for testing notifications (max 3 or total products if less)
        $lowStockCount = min(3, $products->count());
        $lowStockProducts = $products->count() > 0 ? $products->random($lowStockCount) : collect();
        
        foreach ($lowStockProducts as $product) {
            $product->inventory->update([
                'quantity' => rand(1, 4),
                'low_stock_threshold' => 10,
            ]);
        }

        // Create orders with order items
        $this->command->info('Creating orders...');
        $orders = collect();
        
        // Use all available users since we have fewer now
        $buyersToUse = $users->count() > 0 ? $users : collect([User::factory()->buyer()->create()]);
        
        foreach ($buyersToUse as $buyer) {
            // Each buyer gets 1-3 orders
            $userOrders = Order::factory(rand(1, 3))->create([
                'user_id' => $buyer->id,
            ]);
            
            foreach ($userOrders as $order) {
                // Each order gets 1-3 items (or fewer if not enough products)
                $maxItems = min(3, $products->count());
                $numItems = $maxItems > 0 ? rand(1, $maxItems) : 1;
                $orderProducts = $products->count() > 0 ? $products->random($numItems) : collect();
                $totalAmount = 0;
                
                foreach ($orderProducts as $product) {
                    $quantity = rand(1, 3);
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
                
                // Update order total
                $order->update(['total_amount' => $totalAmount]);
            }
            
            $orders = $orders->merge($userOrders);
        }

        // Create cart items for some users
        $this->command->info('Creating cart items...');
        // Use at most 3 users or all if less than 3
        $usersForCart = $users->count() > 3 ? $users->random(3) : $users;
        
        foreach ($usersForCart as $user) {
            // Each buyer gets 1-3 products in their cart
            $cartProducts = $products->random(rand(1, min(3, $products->count())));
            
            foreach ($cartProducts as $product) {
                CartItem::factory()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                ]);
            }
        }

        // Create notifications for admin users
        $this->command->info('Creating notifications...');
        $adminUsers = User::where('role', UserRole::ADMIN)->get();
        
        // Create order notifications for each admin
        foreach ($adminUsers as $admin) {
            // New order notifications
            foreach ($orders->take(3) as $order) {
                $notificationData = [
                    'title' => 'New Order #' . $order->order_number,
                    'message' => 'A new order has been placed by ' . $order->user->name,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user->name,
                    'total_amount' => $order->total_amount,
                ];
                
                $admin->notifications()->create([
                    'type' => NotificationType::ORDER_CREATED->value,
                    'data' => json_encode($notificationData),
                    'read_at' => null,
                ]);
            }
            
            // Low stock notifications
            foreach ($products->take(2) as $product) {
                $inventory = $product->inventory;
                if ($inventory && $inventory->quantity <= $inventory->low_stock_threshold) {
                    $notificationData = [
                        'title' => 'Low Stock Alert',
                        'message' => "Product \"{$product->name}\" is running low on stock.",
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'current_stock' => $inventory->quantity,
                        'threshold' => $inventory->low_stock_threshold,
                    ];
                    
                    $admin->notifications()->create([
                        'type' => NotificationType::LOW_STOCK->value,
                        'data' => json_encode($notificationData),
                        'read_at' => now()->subDays(rand(1, 7)), // Random read date in the past
                    ]);
                }
            }
            
            // Some read notifications using ORDER_STATUS_CHANGED type
            $admin->notifications()->createMany([
                [
                    'type' => NotificationType::ORDER_STATUS_CHANGED->value,
                    'data' => json_encode([
                        'title' => 'System Update',
                        'message' => 'The system has been updated to the latest version.',
                        'version' => '1.0.0'
                    ]),
                    'read_at' => now()->subDays(2),
                ],
                [
                    'type' => NotificationType::ORDER_STATUS_CHANGED->value,
                    'data' => json_encode([
                        'title' => 'New Feature Available',
                        'message' => 'Check out the new reporting dashboard!',
                        'feature' => 'reporting_dashboard'
                    ]),
                    'read_at' => now()->subDay(),
                ]
            ]);
        }

        // Create ratings for products (only for buyers who have purchased)
        $this->command->info('Creating product ratings...');
        $paidOrders = $orders->where('status', 'paid');
        
        if ($paidOrders->isNotEmpty() && $products->isNotEmpty()) {
            foreach ($paidOrders as $order) {
                // 70% chance the buyer will rate products from their order
                if (rand(1, 100) <= 70) {
                    foreach ($order->orderItems as $orderItem) {
                        Rating::factory()->create([
                            'user_id' => $order->user_id,
                            'product_id' => $orderItem->product_id,
                            'rating' => rand(1, 5),
                            'is_verified_purchase' => true,
                        ]);
                    }
                }
            }
        }

        // Create some unverified ratings (only if we have products and users)
        if ($products->isNotEmpty() && $users->isNotEmpty()) {
            $maxProducts = min(3, $products->count());
            $productsForRatings = $products->random($maxProducts);
            
            foreach ($productsForRatings as $product) {
                $ratingUser = $users->random();
                
                Rating::create([
                    'user_id' => $ratingUser->id,
                    'product_id' => $product->id,
                    'rating' => rand(1, 5),
                    'is_verified_purchase' => false,
                ]);
            }
        }

        // Create notifications for admin users
        $this->command->info('Creating notifications...');
        $adminUsers = User::where('role', UserRole::ADMIN)->get();
        
        // Create order notifications for each admin
        foreach ($adminUsers as $admin) {
            // New order notifications
            foreach ($orders->take(3) as $order) {
                $notificationData = [
                    'title' => 'New Order #' . $order->order_number,
                    'message' => 'A new order has been placed by ' . $order->user->name,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user->name,
                    'total_amount' => $order->total_amount,
                ];
                
                $admin->notifications()->create([
                    'type' => NotificationType::ORDER_CREATED->value,
                    'data' => json_encode($notificationData),
                    'read_at' => null,
                ]);
            }
            
            // Low stock notifications
            foreach ($products->take(2) as $product) {
                $inventory = $product->inventory;
                if ($inventory && $inventory->quantity <= $inventory->low_stock_threshold) {
                    $notificationData = [
                        'title' => 'Low Stock Alert',
                        'message' => "Product \"{$product->name}\" is running low on stock.",
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'current_stock' => $inventory->quantity,
                        'threshold' => $inventory->low_stock_threshold,
                    ];
                    
                    $admin->notifications()->create([
                        'type' => NotificationType::LOW_STOCK->value,
                        'data' => json_encode($notificationData),
                        'read_at' => now()->subDays(rand(1, 7)), // Random read date in the past
                    ]);
                }
            }
            
            // Some read notifications using ORDER_STATUS_CHANGED type
            $admin->notifications()->createMany([
                [
                    'type' => NotificationType::ORDER_STATUS_CHANGED->value,
                    'data' => json_encode([
                        'title' => 'System Update',
                        'message' => 'The system has been updated to the latest version.',
                        'version' => '1.0.0'
                    ]),
                    'read_at' => now()->subDays(2),
                ],
                [
                    'type' => NotificationType::ORDER_STATUS_CHANGED->value,
                    'data' => json_encode([
                        'title' => 'New Feature Available',
                        'message' => 'Check out the new reporting dashboard!',
                        'feature' => 'reporting_dashboard'
                    ]),
                    'read_at' => now()->subDay(),
                ]
            ]);
        }

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info("Created:");
        $this->command->info("- " . User::count() . " users");
        $this->command->info("- " . Category::count() . " categories");
        $this->command->info("- " . Product::count() . " products");
        $this->command->info("- " . Order::count() . " orders");
        $this->command->info("- " . OrderItem::count() . " order items");
        $this->command->info("- " . CartItem::count() . " cart items");
        $this->command->info("- " . Rating::count() . " ratings");
        $this->command->info("- " . Notification::count() . " notifications");
    }
}
