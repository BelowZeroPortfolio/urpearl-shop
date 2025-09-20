<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_cart()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/cart');

        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
    }

    public function test_guest_cannot_view_cart()
    {
        $response = $this->get('/cart');

        $response->assertRedirect('/login');
    }

    public function test_can_add_product_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        $response = $this->actingAs($user)
            ->postJson("/cart/{$product->id}", [
                'quantity' => 2
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Product added to cart successfully'
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    public function test_cannot_add_product_with_insufficient_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'low_stock_threshold' => 1
        ]);

        $response = $this->actingAs($user)
            ->postJson("/cart/{$product->id}", [
                'quantity' => 5
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Insufficient stock available'
        ]);
    }

    public function test_can_update_cart_item_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($user)
            ->putJson("/cart/{$cartItem->id}", [
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

    public function test_can_remove_cart_item()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/cart/{$cartItem->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id
        ]);
    }

    public function test_can_clear_cart()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);
        
        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/cart');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id
        ]);
    }

    public function test_user_cannot_modify_other_users_cart_items()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        
        $cartItem = CartItem::create([
            'user_id' => $user1->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($user2)
            ->putJson("/cart/{$cartItem->id}", [
                'quantity' => 5
            ]);

        $response->assertStatus(403);
    }
}