<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $category = Category::factory()->create();
        $this->product = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);
    }

    public function test_authenticated_user_can_view_cart_page()
    {
        $response = $this->actingAs($this->user)->get('/cart');

        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
        $response->assertSee('Shopping Cart');
    }

    public function test_unauthenticated_user_redirected_from_cart()
    {
        $response = $this->get('/cart');

        $response->assertRedirect('/login');
    }

    public function test_user_can_add_product_to_cart()
    {
        $response = $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Product added to cart successfully!');

        // Assert cart item was created
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
    }

    public function test_adding_same_product_updates_quantity()
    {
        // Add product first time
        $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2
            ]);

        // Add same product again
        $response = $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 3
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert quantity was updated, not duplicated
        $this->assertEquals(1, CartItem::where('user_id', $this->user->id)->count());
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5
        ]);
    }

    public function test_cannot_add_more_than_available_stock()
    {
        $response = $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 15 // More than available stock (10)
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Insufficient stock available');

        // Assert no cart item was created
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);
    }

    public function test_cannot_add_out_of_stock_product()
    {
        // Set inventory to 0
        $this->product->inventory->update(['quantity' => 0]);

        $response = $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 1
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Insufficient stock available');
    }

    public function test_user_can_update_cart_item_quantity()
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/cart/{$cartItem->id}", [
                'quantity' => 4
            ]);

        $response->assertRedirect('/cart');
        $response->assertSessionHas('success', 'Cart updated successfully!');

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 4
        ]);
    }

    public function test_cannot_update_cart_item_beyond_stock()
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/cart/{$cartItem->id}", [
                'quantity' => 15 // More than available stock
            ]);

        $response->assertRedirect('/cart');
        $response->assertSessionHas('error', 'Insufficient stock available');

        // Quantity should remain unchanged
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 2
        ]);
    }

    public function test_user_can_remove_item_from_cart()
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->delete("/cart/{$cartItem->id}");

        $response->assertRedirect('/cart');
        $response->assertSessionHas('success', 'Item removed from cart!');

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    public function test_user_cannot_modify_other_users_cart_items()
    {
        $otherUser = User::factory()->create();
        $otherCartItem = CartItem::factory()->create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        // Try to update other user's cart item
        $response = $this->actingAs($this->user)
            ->patch("/cart/{$otherCartItem->id}", [
                'quantity' => 5
            ]);

        $response->assertStatus(403);

        // Try to delete other user's cart item
        $response = $this->actingAs($this->user)
            ->delete("/cart/{$otherCartItem->id}");

        $response->assertStatus(403);
    }

    public function test_cart_displays_correct_items_and_totals()
    {
        $product1 = Product::factory()->create(['price' => 100.00]);
        $product2 = Product::factory()->create(['price' => 50.00]);
        
        Inventory::factory()->create(['product_id' => $product1->id, 'quantity' => 10]);
        Inventory::factory()->create(['product_id' => $product2->id, 'quantity' => 10]);

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($this->user)->get('/cart');

        $response->assertStatus(200);
        $response->assertSee($product1->name);
        $response->assertSee($product2->name);
        $response->assertSee('₱200.00'); // Product 1 total (100 * 2)
        $response->assertSee('₱50.00');  // Product 2 total (50 * 1)
        $response->assertSee('₱250.00'); // Grand total
    }

    public function test_empty_cart_displays_appropriate_message()
    {
        $response = $this->actingAs($this->user)->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Your cart is empty');
        $response->assertSee('Continue Shopping');
    }

    public function test_cart_item_validation_requires_valid_product()
    {
        $response = $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => 99999, // Non-existent product
                'quantity' => 1
            ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_cart_item_validation_requires_positive_quantity()
    {
        $response = $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 0
            ]);

        $response->assertSessionHasErrors('quantity');

        $response = $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => -1
            ]);

        $response->assertSessionHasErrors('quantity');
    }

    public function test_cart_persists_across_sessions()
    {
        // Add item to cart
        $this->actingAs($this->user)
            ->post('/cart/add', [
                'product_id' => $this->product->id,
                'quantity' => 2
            ]);

        // Simulate logout and login
        $this->post('/logout');
        $this->actingAs($this->user);

        // Check cart still contains item
        $response = $this->get('/cart');
        $response->assertSee($this->product->name);
        $response->assertSee('2'); // Quantity
    }

    public function test_ajax_cart_updates_work()
    {
        $cartItem = CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->patchJson("/cart/{$cartItem->id}", [
                'quantity' => 3
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'total' => $this->product->price * 3
        ]);
    }

    public function test_cart_count_updates_correctly()
    {
        // Add multiple items
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $product2 = Product::factory()->create();
        Inventory::factory()->create(['product_id' => $product2->id, 'quantity' => 10]);
        
        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 3
        ]);

        $response = $this->actingAs($this->user)->get('/');

        // Should show total quantity (2 + 3 = 5) in cart badge
        $response->assertSee('5'); // Cart count
    }
}