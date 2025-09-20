<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;
    protected CartService $cartService;

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
        
        $this->cartService = app(CartService::class);
    }

    /** @test */
    public function authenticated_user_can_view_cart_page()
    {
        $response = $this->actingAs($this->user)->get(route('cart.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
    }

    /** @test */
    public function guest_cannot_view_cart_page()
    {
        $response = $this->get(route('cart.index'));
        
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_can_add_product_to_cart()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('cart.add', $this->product), [
                'quantity' => 2
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cart_count' => 2
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
    }

    /** @test */
    public function user_cannot_add_more_than_available_stock()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('cart.add', $this->product), [
                'quantity' => 15 // More than available stock (10)
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Insufficient stock available'
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    /** @test */
    public function user_can_update_cart_item_quantity()
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->putJson(route('cart.update', $cartItem), [
                'quantity' => 5
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5
        ]);
    }

    /** @test */
    public function user_can_remove_item_from_cart()
    {
        $cartItem = CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('cart.remove', $cartItem));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    /** @test */
    public function user_can_clear_entire_cart()
    {
        // Add multiple items to cart
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $product2 = Product::factory()->create(['category_id' => $this->product->category_id]);
        Inventory::factory()->create(['product_id' => $product2->id, 'quantity' => 5]);
        
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson(route('cart.clear'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'cart_count' => 0
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function cart_service_calculates_correct_total()
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $total = $this->cartService->getCartTotal($this->user);
        
        $this->assertEquals(200.00, $total); // 2 * 100.00
    }

    /** @test */
    public function cart_service_validates_stock_correctly()
    {
        // Create cart item with quantity exceeding stock
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 15 // More than available stock (10)
        ]);

        $errors = $this->cartService->validateCartStock($this->user);
        
        $this->assertCount(1, $errors);
        $this->assertStringContains('Only 10 units', $errors[0]);
    }

    /** @test */
    public function user_cannot_access_other_users_cart_items()
    {
        $otherUser = User::factory()->create();
        $cartItem = CartItem::create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->putJson(route('cart.update', $cartItem), [
                'quantity' => 5
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cart_data_endpoint_returns_correct_information()
    {
        CartItem::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('cart.data'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'cart_items',
            'cart_count',
            'cart_total',
            'stock_errors'
        ]);
        
        $response->assertJson([
            'cart_count' => 3,
            'cart_total' => 300.00
        ]);
    }
}