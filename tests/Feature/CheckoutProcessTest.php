<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Tests\TestCase;

class CheckoutProcessTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100.00
        ]);
        Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_authenticated_user_can_access_checkout_page()
    {
        // Add item to cart first
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)->get('/checkout');

        $response->assertStatus(200);
        $response->assertViewIs('checkout.index');
        $response->assertSee('Checkout');
        $response->assertSee($this->product->name);
        $response->assertSee('₱200.00'); // Total amount
    }

    public function test_unauthenticated_user_redirected_from_checkout()
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/login');
    }

    public function test_checkout_redirects_when_cart_is_empty()
    {
        $response = $this->actingAs($this->user)->get('/checkout');

        $response->assertRedirect('/cart');
        $response->assertSessionHas('error', 'Your cart is empty');
    }

    public function test_checkout_form_validation_requires_shipping_address()
    {
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($this->user)
            ->post('/checkout/process', [
                'payment_method_id' => 'pm_card_visa',
                // Missing shipping address fields
            ]);

        $response->assertSessionHasErrors([
            'shipping_name',
            'shipping_address_line_1',
            'shipping_city',
            'shipping_postal_code'
        ]);
    }

    public function test_successful_checkout_creates_order_and_decrements_inventory()
    {
        $this->mockStripePaymentIntent('pi_test_success', 'succeeded');

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $initialStock = $this->product->inventory->quantity;

        $response = $this->actingAs($this->user)
            ->post('/checkout/process', [
                'payment_method_id' => 'pm_card_visa',
                'shipping_name' => 'John Doe',
                'shipping_address_line_1' => '123 Main St',
                'shipping_city' => 'Manila',
                'shipping_state' => 'NCR',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Philippines'
            ]);

        // Assert order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => 200.00,
            'status' => OrderStatus::PAID->value,
            'stripe_payment_id' => 'pi_test_success'
        ]);

        // Assert order items were created
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 100.00
        ]);

        // Assert inventory was decremented
        $this->assertEquals($initialStock - 2, $this->product->inventory->fresh()->quantity);

        // Assert cart was cleared
        $this->assertEquals(0, CartItem::where('user_id', $this->user->id)->count());

        // Assert redirect to success page
        $response->assertRedirect('/checkout/success');
        $response->assertSessionHas('success', 'Order placed successfully!');
    }

    public function test_failed_payment_does_not_create_order()
    {
        $this->mockStripePaymentIntent('pi_test_failed', 'payment_failed');

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $initialStock = $this->product->inventory->quantity;

        $response = $this->actingAs($this->user)
            ->post('/checkout/process', [
                'payment_method_id' => 'pm_card_declined',
                'shipping_name' => 'John Doe',
                'shipping_address_line_1' => '123 Main St',
                'shipping_city' => 'Manila',
                'shipping_state' => 'NCR',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Philippines'
            ]);

        // Assert no order was created
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id
        ]);

        // Assert inventory was not decremented
        $this->assertEquals($initialStock, $this->product->inventory->fresh()->quantity);

        // Assert cart was not cleared
        $this->assertEquals(1, CartItem::where('user_id', $this->user->id)->count());

        // Assert redirect back with error
        $response->assertRedirect('/checkout');
        $response->assertSessionHas('error', 'Payment failed. Please try again.');
    }

    public function test_checkout_handles_insufficient_stock_during_process()
    {
        // Set low stock
        $this->product->inventory->update(['quantity' => 1]);

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2 // More than available
        ]);

        $response = $this->actingAs($this->user)
            ->post('/checkout/process', [
                'payment_method_id' => 'pm_card_visa',
                'shipping_name' => 'John Doe',
                'shipping_address_line_1' => '123 Main St',
                'shipping_city' => 'Manila',
                'shipping_state' => 'NCR',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Philippines'
            ]);

        $response->assertRedirect('/cart');
        $response->assertSessionHas('error', 'Some items in your cart are no longer available in the requested quantity.');
    }

    public function test_checkout_success_page_displays_order_details()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 150.00,
            'status' => OrderStatus::PAID
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 100.00
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['last_order_id' => $order->id])
            ->get('/checkout/success');

        $response->assertStatus(200);
        $response->assertViewIs('checkout.success');
        $response->assertSee('Order Confirmation');
        $response->assertSee($order->id);
        $response->assertSee('₱150.00');
        $response->assertSee($this->product->name);
    }

    public function test_checkout_success_page_redirects_without_recent_order()
    {
        $response = $this->actingAs($this->user)->get('/checkout/success');

        $response->assertRedirect('/');
    }

    public function test_multiple_products_checkout_calculates_correctly()
    {
        $product2 = Product::factory()->create(['price' => 75.00]);
        Inventory::factory()->create([
            'product_id' => $product2->id,
            'quantity' => 10
        ]);

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2 // 200.00
        ]);
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 1 // 75.00
        ]);

        $this->mockStripePaymentIntent('pi_test_success', 'succeeded');

        $response = $this->actingAs($this->user)
            ->post('/checkout/process', [
                'payment_method_id' => 'pm_card_visa',
                'shipping_name' => 'John Doe',
                'shipping_address_line_1' => '123 Main St',
                'shipping_city' => 'Manila',
                'shipping_state' => 'NCR',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Philippines'
            ]);

        // Assert order total is correct
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => 275.00 // 200 + 75
        ]);

        // Assert both order items were created
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals(2, $order->orderItems->count());
    }

    public function test_checkout_stores_shipping_address_correctly()
    {
        $this->mockStripePaymentIntent('pi_test_success', 'succeeded');

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        $shippingData = [
            'shipping_name' => 'Jane Smith',
            'shipping_address_line_1' => '456 Oak Avenue',
            'shipping_address_line_2' => 'Apt 2B',
            'shipping_city' => 'Quezon City',
            'shipping_state' => 'NCR',
            'shipping_postal_code' => '1100',
            'shipping_country' => 'Philippines'
        ];

        $response = $this->actingAs($this->user)
            ->post('/checkout/process', array_merge([
                'payment_method_id' => 'pm_card_visa'
            ], $shippingData));

        $order = Order::where('user_id', $this->user->id)->first();
        
        $this->assertEquals([
            'name' => 'Jane Smith',
            'address_line_1' => '456 Oak Avenue',
            'address_line_2' => 'Apt 2B',
            'city' => 'Quezon City',
            'state' => 'NCR',
            'postal_code' => '1100',
            'country' => 'Philippines'
        ], $order->shipping_address);
    }

    public function test_checkout_triggers_low_stock_notifications()
    {
        // Set inventory to trigger low stock after purchase
        $this->product->inventory->update([
            'quantity' => 6,
            'low_stock_threshold' => 5
        ]);

        $this->mockStripePaymentIntent('pi_test_success', 'succeeded');

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2 // Will leave 4, which is below threshold of 5
        ]);

        $response = $this->actingAs($this->user)
            ->post('/checkout/process', [
                'payment_method_id' => 'pm_card_visa',
                'shipping_name' => 'John Doe',
                'shipping_address_line_1' => '123 Main St',
                'shipping_city' => 'Manila',
                'shipping_state' => 'NCR',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Philippines'
            ]);

        // Assert low stock notification was created
        $this->assertDatabaseHas('notifications', [
            'type' => 'low_stock',
            'data' => json_encode([
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'current_stock' => 4,
                'threshold' => 5
            ])
        ]);
    }

    /**
     * Mock Stripe PaymentIntent for testing
     */
    private function mockStripePaymentIntent(string $paymentIntentId, string $status): void
    {
        $paymentIntent = Mockery::mock(PaymentIntent::class);
        $paymentIntent->id = $paymentIntentId;
        $paymentIntent->status = $status;
        $paymentIntent->amount = 20000; // $200.00 in cents
        $paymentIntent->currency = 'php';

        // Mock Stripe API calls
        Stripe::shouldReceive('setApiKey')->once();
        PaymentIntent::shouldReceive('create')->andReturn($paymentIntent);
        PaymentIntent::shouldReceive('retrieve')->with($paymentIntentId)->andReturn($paymentIntent);
    }
}