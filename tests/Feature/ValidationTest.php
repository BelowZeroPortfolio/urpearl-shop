<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test category
        Category::factory()->create([
            'id' => 1,
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);
    }

    /** @test */
    public function it_validates_add_to_cart_request()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $this->actingAs($user)
            ->postJson("/cart/{$product->id}", [
                'quantity' => 0 // Invalid quantity
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_validates_cart_quantity_against_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->inventory()->create([
            'quantity' => 5,
            'low_stock_threshold' => 2
        ]);
        
        $this->actingAs($user)
            ->postJson("/cart/{$product->id}", [
                'quantity' => 10 // More than available stock
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_validates_rating_request_authorization()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        // User hasn't purchased the product
        $this->actingAs($user)
            ->postJson("/products/{$product->slug}/ratings", [
                'rating' => 5,
                'review' => 'Great product!'
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function it_validates_rating_data()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        // Create a purchase history
        $order = $user->orders()->create([
            'total_amount' => 100.00,
            'status' => 'paid'
        ]);
        
        $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100.00
        ]);
        
        $this->actingAs($user)
            ->postJson("/products/{$product->slug}/ratings", [
                'rating' => 6, // Invalid rating (max 5)
                'review' => 'abc' // Too short
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating', 'review']);
    }

    /** @test */
    public function it_validates_product_creation_request()
    {
        $admin = User::factory()->admin()->create();
        
        $this->actingAs($admin)
            ->postJson('/admin/products', [
                'name' => '', // Required field missing
                'price' => -10, // Invalid price
                'sku' => '', // Required field missing
                'category_id' => 999 // Non-existent category
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'sku', 'category_id']);
    }

    /** @test */
    public function it_validates_inventory_update_request()
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        
        $this->actingAs($admin)
            ->postJson("/admin/inventory/{$product->id}", [
                'quantity' => -5, // Invalid quantity
                'low_stock_threshold' => 1000 // Greater than quantity
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity', 'low_stock_threshold']);
    }

    /** @test */
    public function it_validates_order_status_transition()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        
        $order = $user->orders()->create([
            'total_amount' => 100.00,
            'status' => 'shipped' // Already shipped
        ]);
        
        $this->actingAs($admin)
            ->postJson("/admin/orders/{$order->id}/status", [
                'status' => 'pending' // Invalid transition
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function field_validation_api_endpoint_works()
    {
        $this->postJson('/api/validate-field', [
            'field' => 'email',
            'value' => 'invalid-email',
            'rules' => ['required', 'email']
        ])
        ->assertStatus(200)
        ->assertJson([
            'valid' => false
        ]);
        
        $this->postJson('/api/validate-field', [
            'field' => 'email',
            'value' => 'valid@example.com',
            'rules' => ['required', 'email']
        ])
        ->assertStatus(200)
        ->assertJson([
            'valid' => true
        ]);
    }

    /** @test */
    public function it_handles_custom_exceptions_properly()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->inventory()->create([
            'quantity' => 0, // Out of stock
            'low_stock_threshold' => 2
        ]);
        
        $response = $this->actingAs($user)
            ->postJson("/cart/{$product->id}", [
                'quantity' => 1
            ]);
            
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error_type' => 'insufficient_stock'
            ]);
    }

    /** @test */
    public function it_shows_custom_error_pages_for_web_requests()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $product->inventory()->create([
            'quantity' => 0,
            'low_stock_threshold' => 2
        ]);
        
        $response = $this->actingAs($user)
            ->post("/cart/{$product->id}", [
                'quantity' => 1
            ]);
            
        $response->assertStatus(422)
            ->assertViewIs('errors.insufficient-stock');
    }
}