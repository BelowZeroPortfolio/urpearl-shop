<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_has_correct_fillable_attributes()
    {
        $fillable = [
            'user_id', 'total_amount', 'status', 'stripe_payment_id', 'shipping_address'
        ];

        $order = new Order();
        $this->assertEquals($fillable, $order->getFillable());
    }

    public function test_order_casts_total_amount_to_decimal()
    {
        $order = Order::factory()->create(['total_amount' => 123.456]);
        
        $this->assertEquals('123.46', $order->total_amount);
    }

    public function test_order_casts_status_to_enum()
    {
        $order = Order::factory()->create(['status' => OrderStatus::PAID]);
        
        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertEquals(OrderStatus::PAID, $order->status);
    }

    public function test_order_casts_shipping_address_to_array()
    {
        $shippingAddress = [
            'name' => 'John Doe',
            'address_line_1' => '123 Main St',
            'city' => 'Manila',
            'country' => 'Philippines'
        ];

        $order = Order::factory()->create(['shipping_address' => $shippingAddress]);
        
        $this->assertIsArray($order->shipping_address);
        $this->assertEquals($shippingAddress, $order->shipping_address);
    }

    public function test_order_belongs_to_user()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_has_many_order_items()
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->orderItems);
        $this->assertInstanceOf(OrderItem::class, $order->orderItems->first());
    }

    public function test_is_pending_method_returns_correct_boolean()
    {
        $pendingOrder = Order::factory()->pending()->create();
        $paidOrder = Order::factory()->paid()->create();

        $this->assertTrue($pendingOrder->isPending());
        $this->assertFalse($paidOrder->isPending());
    }

    public function test_is_paid_method_returns_correct_boolean()
    {
        $pendingOrder = Order::factory()->pending()->create();
        $paidOrder = Order::factory()->paid()->create();

        $this->assertFalse($pendingOrder->isPaid());
        $this->assertTrue($paidOrder->isPaid());
    }

    public function test_is_shipped_method_returns_correct_boolean()
    {
        $pendingOrder = Order::factory()->pending()->create();
        $shippedOrder = Order::factory()->shipped()->create();

        $this->assertFalse($pendingOrder->isShipped());
        $this->assertTrue($shippedOrder->isShipped());
    }

    public function test_is_cancelled_method_returns_correct_boolean()
    {
        $pendingOrder = Order::factory()->pending()->create();
        $cancelledOrder = Order::factory()->cancelled()->create();

        $this->assertFalse($pendingOrder->isCancelled());
        $this->assertTrue($cancelledOrder->isCancelled());
    }

    public function test_total_quantity_attribute_calculates_correctly()
    {
        $order = Order::factory()->create();
        
        // Create order items with quantities: 2, 3, 1 = total 6
        OrderItem::factory()->create(['order_id' => $order->id, 'quantity' => 2]);
        OrderItem::factory()->create(['order_id' => $order->id, 'quantity' => 3]);
        OrderItem::factory()->create(['order_id' => $order->id, 'quantity' => 1]);

        $this->assertEquals(6, $order->total_quantity);
    }

    public function test_total_quantity_returns_zero_when_no_items()
    {
        $order = Order::factory()->create();
        
        $this->assertEquals(0, $order->total_quantity);
    }

    public function test_order_factory_creates_with_valid_status()
    {
        $order = Order::factory()->create();
        
        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertContains($order->status, OrderStatus::cases());
    }

    public function test_order_factory_creates_with_stripe_payment_id()
    {
        $order = Order::factory()->create();
        
        $this->assertStringStartsWith('pi_', $order->stripe_payment_id);
        $this->assertEquals(27, strlen($order->stripe_payment_id)); // pi_ + 24 characters
    }

    public function test_order_factory_creates_with_shipping_address()
    {
        $order = Order::factory()->create();
        
        $this->assertIsArray($order->shipping_address);
        $this->assertArrayHasKey('name', $order->shipping_address);
        $this->assertArrayHasKey('address_line_1', $order->shipping_address);
        $this->assertArrayHasKey('city', $order->shipping_address);
        $this->assertArrayHasKey('country', $order->shipping_address);
    }
}