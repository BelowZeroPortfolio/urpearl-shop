<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\OrderService;
use App\Services\InventoryService;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Notification;
use App\Enums\UserRole;
use App\Enums\NotificationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class OrderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_triggers_low_stock_notification()
    {
        Mail::fake();
        
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN,
        ]);
        
        // Create buyer user
        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'role' => UserRole::BUYER,
        ]);
        
        // Create category and product
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test product description',
            'price' => 100.00,
            'sku' => 'TEST-001',
            'category_id' => $category->id,
        ]);
        
        // Create inventory with low threshold
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 6, // Just above threshold
            'low_stock_threshold' => 5,
        ]);
        
        // Add item to cart that will trigger low stock
        CartItem::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'quantity' => 2, // This will bring stock to 4, below threshold
        ]);
        
        // Create order services
        $inventoryService = new InventoryService();
        $orderService = new OrderService($inventoryService);
        
        // Create order from cart
        $order = $orderService->createOrderFromCart($buyer, [
            'name' => 'Test Buyer',
            'address_line_1' => '123 Test St',
            'city' => 'Test City',
        ]);
        
        // Assert order was created
        $this->assertNotNull($order);
        $this->assertEquals(200.00, $order->total_amount);
        
        // Assert inventory was decremented
        $product->inventory->refresh();
        $this->assertEquals(4, $product->inventory->quantity);
        
        // Assert low stock notification was created for admin
        $notification = Notification::where('user_id', $admin->id)
            ->where('type', NotificationType::LOW_STOCK)
            ->first();
            
        $this->assertNotNull($notification);
        $this->assertStringContains('low on stock', $notification->message);
        $this->assertEquals($product->id, $notification->payload['product_id']);
    }

    public function test_order_cancellation_restores_inventory_and_clears_notifications()
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN,
        ]);
        
        // Create buyer user
        $buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'role' => UserRole::BUYER,
        ]);
        
        // Create category and product
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        
        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test product description',
            'price' => 100.00,
            'sku' => 'TEST-001',
            'category_id' => $category->id,
        ]);
        
        // Create inventory
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5,
        ]);
        
        // Create order services
        $inventoryService = new InventoryService();
        $orderService = new OrderService($inventoryService);
        
        // Create order directly
        $order = $orderService->createOrder($buyer, [
            [
                'product_id' => $product->id,
                'quantity' => 8, // This will bring stock to 2, below threshold
            ]
        ], [
            'name' => 'Test Buyer',
            'address_line_1' => '123 Test St',
        ]);
        
        // Verify inventory was decremented and notification created
        $product->inventory->refresh();
        $this->assertEquals(2, $product->inventory->quantity);
        
        $notification = Notification::where('user_id', $admin->id)
            ->where('type', NotificationType::LOW_STOCK)
            ->first();
        $this->assertNotNull($notification);
        
        // Cancel the order
        $orderService->cancelOrder($order);
        
        // Verify inventory was restored
        $product->inventory->refresh();
        $this->assertEquals(10, $product->inventory->quantity);
        
        // Verify low stock notification was cleared
        $notification = Notification::where('user_id', $admin->id)
            ->where('type', NotificationType::LOW_STOCK)
            ->whereNull('read_at')
            ->first();
        $this->assertNull($notification);
    }
}