<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Rating;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class UserJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_complete_buyer_journey_from_registration_to_purchase()
    {
        // Step 1: Create test data
        $category = Category::factory()->create(['name' => 'Pearl Necklaces']);
        $product = Product::factory()->create([
            'name' => 'Elegant Pearl Necklace',
            'price' => 299.99,
            'category_id' => $category->id
        ]);
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        // Step 2: User visits homepage
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('UrPearl SHOP');
        $response->assertSee($product->name);

        // Step 3: User clicks on product to view details
        $response = $this->get("/products/{$product->id}");
        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->description);
        $response->assertSee('₱299.99');
        $response->assertSee('Add to Cart');

        // Step 4: User tries to add to cart but needs to login first
        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
        $response->assertRedirect('/login');

        // Step 5: User goes to login page
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Continue with Google');

        // Step 6: User authenticates with Google OAuth
        $this->mockGoogleOAuth([
            'id' => '123456789',
            'name' => 'John Buyer',
            'email' => 'john@example.com',
            'avatar' => 'https://example.com/avatar.jpg'
        ]);

        $response = $this->get('/auth/google/callback');
        $response->assertRedirect('/');

        // Verify user was created and authenticated
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::BUYER, $user->role);
        $this->assertAuthenticatedAs($user);

        // Step 7: User adds product to cart
        $response = $this->actingAs($user)->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product added to cart successfully!');

        // Step 8: User views cart
        $response = $this->actingAs($user)->get('/cart');
        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('2'); // Quantity
        $response->assertSee('₱599.98'); // Total (299.99 * 2)

        // Step 9: User proceeds to checkout
        $response = $this->actingAs($user)->get('/checkout');
        $response->assertStatus(200);
        $response->assertSee('Checkout');
        $response->assertSee($product->name);
        $response->assertSee('₱599.98');

        // Step 10: User completes checkout (mock successful payment)
        $this->mockStripePayment('pi_success', 'succeeded');

        $response = $this->actingAs($user)->post('/checkout/process', [
            'payment_method_id' => 'pm_card_visa',
            'shipping_name' => 'John Buyer',
            'shipping_address_line_1' => '123 Main Street',
            'shipping_city' => 'Manila',
            'shipping_state' => 'NCR',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Philippines'
        ]);

        $response->assertRedirect('/checkout/success');

        // Verify order was created
        $order = Order::where('user_id', $user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(599.98, $order->total_amount);
        $this->assertEquals(OrderStatus::PAID, $order->status);

        // Verify inventory was decremented
        $this->assertEquals(8, $product->inventory->fresh()->quantity);

        // Verify cart was cleared
        $this->assertEquals(0, CartItem::where('user_id', $user->id)->count());

        // Step 11: User views order confirmation
        $response = $this->actingAs($user)
            ->withSession(['last_order_id' => $order->id])
            ->get('/checkout/success');
        $response->assertStatus(200);
        $response->assertSee('Order Confirmation');
        $response->assertSee($order->id);

        // Step 12: User views order history
        $response = $this->actingAs($user)->get('/orders');
        $response->assertStatus(200);
        $response->assertSee($order->id);
        $response->assertSee('Paid');

        // Step 13: User leaves a product review
        $response = $this->actingAs($user)->post('/ratings', [
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Excellent quality pearl necklace!'
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Review submitted successfully!');

        // Verify rating was created
        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Excellent quality pearl necklace!',
            'is_verified_purchase' => true
        ]);

        // Step 14: User views product again to see their review
        $response = $this->actingAs($user)->get("/products/{$product->id}");
        $response->assertSee('Excellent quality pearl necklace!');
        $response->assertSee('5'); // Star rating
        $response->assertSee('Verified Purchase');
    }

    public function test_admin_journey_managing_products_and_orders()
    {
        // Step 1: Create admin user
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@urpearl.com'
        ]);

        // Step 2: Admin logs in and accesses dashboard
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Total Products');
        $response->assertSee('Total Orders');

        // Step 3: Admin creates a new category
        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Pearl Earrings'
        ]);
        $response->assertRedirect();

        $category = Category::where('name', 'Pearl Earrings')->first();
        $this->assertNotNull($category);

        // Step 4: Admin creates a new product
        $response = $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Luxury Pearl Earrings',
            'description' => 'Beautiful handcrafted pearl earrings',
            'price' => 199.99,
            'sku' => 'PE-001',
            'category_id' => $category->id,
            'quantity' => 15,
            'low_stock_threshold' => 5
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success', 'Product created successfully!');

        $product = Product::where('sku', 'PE-001')->first();
        $this->assertNotNull($product);

        // Step 5: Admin views product list
        $response = $this->actingAs($admin)->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSee('Luxury Pearl Earrings');
        $response->assertSee('PE-001');

        // Step 6: Create a buyer and simulate purchase
        $buyer = User::factory()->buyer()->create();
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'total_amount' => 199.99,
            'status' => OrderStatus::PENDING
        ]);

        // Step 7: Admin views orders
        $response = $this->actingAs($admin)->get('/admin/orders');
        $response->assertStatus(200);
        $response->assertSee($order->id);
        $response->assertSee('Pending');

        // Step 8: Admin updates order status
        $response = $this->actingAs($admin)->patch("/admin/orders/{$order->id}", [
            'status' => OrderStatus::SHIPPED->value
        ]);
        $response->assertRedirect('/admin/orders');
        $response->assertSessionHas('success', 'Order status updated successfully!');

        // Verify order status was updated
        $this->assertEquals(OrderStatus::SHIPPED, $order->fresh()->status);

        // Step 9: Admin checks inventory levels
        $response = $this->actingAs($admin)->get('/admin/inventory');
        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee('15'); // Current stock

        // Step 10: Admin updates inventory
        $response = $this->actingAs($admin)->patch("/admin/inventory/{$product->inventory->id}", [
            'quantity' => 3, // Below threshold to trigger low stock
            'low_stock_threshold' => 5
        ]);
        $response->assertRedirect('/admin/inventory');

        // Step 11: Admin checks notifications for low stock alert
        $response = $this->actingAs($admin)->get('/admin/notifications');
        $response->assertStatus(200);
        // Should see low stock notification
    }

    public function test_responsive_design_elements_are_present()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Test homepage responsive elements
        $response = $this->get('/');
        $response->assertStatus(200);
        
        // Check for responsive navigation classes
        $response->assertSee('md:hidden'); // Mobile menu toggle
        $response->assertSee('hidden md:block'); // Desktop navigation
        
        // Check for responsive grid classes
        $response->assertSee('grid-cols-1 md:grid-cols-2 lg:grid-cols-3');

        // Test product page responsive elements
        $response = $this->get("/products/{$product->id}");
        $response->assertStatus(200);
        
        // Check for responsive image and content layout
        $response->assertSee('lg:grid-cols-2'); // Desktop two-column layout
        $response->assertSee('w-full'); // Full width on mobile
    }

    public function test_search_and_filtering_functionality()
    {
        $category1 = Category::factory()->create(['name' => 'Necklaces']);
        $category2 = Category::factory()->create(['name' => 'Earrings']);
        
        $product1 = Product::factory()->create([
            'name' => 'Pearl Necklace',
            'category_id' => $category1->id,
            'price' => 100.00
        ]);
        $product2 = Product::factory()->create([
            'name' => 'Diamond Earrings',
            'category_id' => $category2->id,
            'price' => 200.00
        ]);

        // Test search functionality
        $response = $this->get('/products?search=Pearl');
        $response->assertStatus(200);
        $response->assertSee($product1->name);
        $response->assertDontSee($product2->name);

        // Test category filtering
        $response = $this->get("/products?category={$category2->id}");
        $response->assertStatus(200);
        $response->assertSee($product2->name);
        $response->assertDontSee($product1->name);

        // Test price range filtering
        $response = $this->get('/products?min_price=150&max_price=250');
        $response->assertStatus(200);
        $response->assertSee($product2->name);
        $response->assertDontSee($product1->name);
    }

    public function test_error_handling_and_validation_messages()
    {
        $user = User::factory()->create();

        // Test form validation errors display correctly
        $response = $this->actingAs($user)->post('/cart/add', [
            'product_id' => 99999, // Non-existent product
            'quantity' => 0 // Invalid quantity
        ]);

        $response->assertSessionHasErrors(['product_id', 'quantity']);

        // Test 404 error page
        $response = $this->get('/products/99999');
        $response->assertStatus(404);

        // Test unauthorized access
        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Mock Google OAuth for testing
     */
    private function mockGoogleOAuth(array $userData): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($userData['id']);
        $socialiteUser->shouldReceive('getName')->andReturn($userData['name']);
        $socialiteUser->shouldReceive('getEmail')->andReturn($userData['email']);
        $socialiteUser->shouldReceive('getAvatar')->andReturn($userData['avatar']);

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    /**
     * Mock Stripe payment for testing
     */
    private function mockStripePayment(string $paymentIntentId, string $status): void
    {
        // This would typically mock Stripe SDK calls
        // For now, we'll assume the payment service handles this
    }
}