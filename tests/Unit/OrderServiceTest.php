<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OrderService;
use App\Services\InventoryService;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected InventoryService $inventoryService;
    protected User $user;
    protected Product $product;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->inventoryService = new InventoryService();
        $this->orderService = new OrderService($this->inventoryService);
        
        // Create test data
        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        
        $this->product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test product description',
            'price' => 100.00,
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
        ]);
        
        // Create inventory
        Inventory::create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5,
        ]);
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => UserRole::BUYER,
        ]);
    }

    public function test_can_create_order_from_cart()
    {
        Mail::fake();
        
        // Add item to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
        
        $shippingAddress = [
            'name' => 'Test User',
            'address_line_1' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'postal_code' => '12345',
            'country' => 'Test Country',
        ];
        
        $order = $this->orderService->createOrderFromCart($this->user, $shippingAddress, 'stripe_payment_id');
        
        // Assert order was created
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($this->user->id, $order->user_id);
        $this->assertEquals(200.00, $order->total_amount); // 2 * 100.00
        $this->assertEquals(OrderStatus::PAID, $order->status);
        $this->assertEquals('stripe_payment_id', $order->stripe_payment_id);
        $this->assertEquals($shippingAddress, $order->shipping_address);
        
        // Assert order items were created
        $this->assertEquals(1, $order->orderItems->count());
        $orderItem = $order->orderItems->first();
        $this->assertEquals($this->product->id, $orderItem->product_id);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals(100.00, $orderItem->price);
        
        // Assert inventory was decremented
        $this->product->inventory->refresh();
        $this->assertEquals(8, $this->product->inventory->quantity); // 10 - 2
        
        // Assert cart was cleared
        $this->assertEquals(0, $this->user->cartItems()->count());
        
        // Assert email was sent
        Mail::assertSent(OrderConfirmation::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });
    }

    public function test_can_create_order_with_specific_items()
    {
        Mail::fake();
        
        $items = [
            [
                'product_id' => $this->product->id,
                'quantity' => 3,
            ]
        ];
        
        $shippingAddress = [
            'name' => 'Test User',
            'address_line_1' => '123 Test St',
            'city' => 'Test City',
        ];
        
        $order = $this->orderService->createOrder($this->user, $items, $shippingAddress);
        
        // Assert order was created
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($this->user->id, $order->user_id);
        $this->assertEquals(300.00, $order->total_amount); // 3 * 100.00
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertNull($order->stripe_payment_id);
        
        // Assert inventory was decremented
        $this->product->inventory->refresh();
        $this->assertEquals(7, $this->product->inventory->quantity); // 10 - 3
        
        // Assert email was sent
        Mail::assertSent(OrderConfirmation::class);
    }

    public function test_throws_exception_when_cart_is_empty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cart is empty');
        
        $this->orderService->createOrderFromCart($this->user, []);
    }

    public function test_throws_exception_when_insufficient_stock()
    {
        // Add item to cart with quantity greater than available stock
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 15, // More than available (10)
        ]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');
        
        $this->orderService->createOrderFromCart($this->user, []);
    }

    public function test_can_update_order_status()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 100.00,
            'status' => OrderStatus::PENDING,
            'shipping_address' => [],
        ]);
        
        $result = $this->orderService->updateOrderStatus($order, OrderStatus::PAID);
        
        $this->assertTrue($result);
        $order->refresh();
        $this->assertEquals(OrderStatus::PAID, $order->status);
    }

    public function test_can_cancel_order_and_restore_inventory()
    {
        // Create order with order items
        $order = Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 200.00,
            'status' => OrderStatus::PENDING,
            'shipping_address' => [],
        ]);
        
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 100.00,
        ]);
        
        // Decrement inventory first (simulate order creation)
        $this->inventoryService->decrementStock($this->product, 2);
        $this->assertEquals(8, $this->product->inventory->fresh()->quantity);
        
        // Cancel order
        $result = $this->orderService->cancelOrder($order);
        
        $this->assertTrue($result);
        $order->refresh();
        $this->assertEquals(OrderStatus::CANCELLED, $order->status);
        
        // Assert inventory was restored
        $this->product->inventory->refresh();
        $this->assertEquals(10, $this->product->inventory->quantity); // Back to original
    }

    public function test_cannot_cancel_shipped_order()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 100.00,
            'status' => OrderStatus::SHIPPED,
            'shipping_address' => [],
        ]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only pending or paid orders can be cancelled');
        
        $this->orderService->cancelOrder($order);
    }

    public function test_get_order_stats()
    {
        // Create test orders
        Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 100.00,
            'status' => OrderStatus::PENDING,
            'shipping_address' => [],
        ]);
        
        Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 200.00,
            'status' => OrderStatus::PAID,
            'shipping_address' => [],
        ]);
        
        Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 150.00,
            'status' => OrderStatus::SHIPPED,
            'shipping_address' => [],
        ]);
        
        $stats = $this->orderService->getOrderStats();
        
        $this->assertEquals(3, $stats['total_orders']);
        $this->assertEquals(1, $stats['pending_orders']);
        $this->assertEquals(1, $stats['paid_orders']);
        $this->assertEquals(1, $stats['shipped_orders']);
        $this->assertEquals(0, $stats['cancelled_orders']);
        $this->assertEquals(350.00, $stats['total_revenue']); // 200 + 150 (paid + shipped)
    }

    public function test_validates_product_exists_when_creating_order()
    {
        $items = [
            [
                'product_id' => 999, // Non-existent product
                'quantity' => 1,
            ]
        ];
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Product with ID 999 not found');
        
        $this->orderService->createOrder($this->user, $items, []);
    }

    public function test_validates_required_item_fields()
    {
        $items = [
            [
                'product_id' => $this->product->id,
                // Missing quantity
            ]
        ];
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Each item must have product_id and quantity');
        
        $this->orderService->createOrder($this->user, $items, []);
    }
}