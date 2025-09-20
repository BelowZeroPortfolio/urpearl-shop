<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rating;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a category
        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);
        
        // Create a product
        $this->product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test product description',
            'price' => 99.99,
            'sku' => 'TEST-001',
            'category_id' => $this->category->id
        ]);
        
        // Create a buyer user
        $this->buyer = User::create([
            'name' => 'Test Buyer',
            'email' => 'buyer@test.com',
            'role' => UserRole::BUYER,
            'provider' => 'google',
            'provider_id' => '123456789'
        ]);
        
        // Create an admin user
        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'role' => UserRole::ADMIN,
            'provider' => 'google',
            'provider_id' => '987654321'
        ]);
    }

    private function createPaidOrderWithProduct($userId, $productId)
    {
        $order = Order::create([
            'user_id' => $userId,
            'total_amount' => 99.99,
            'status' => OrderStatus::PAID,
            'stripe_payment_id' => 'pi_test123',
            'shipping_address' => [
                'name' => 'Test User',
                'address' => '123 Test St',
                'city' => 'Test City',
                'postal_code' => '12345'
            ]
        ]);
        
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productId,
            'quantity' => 1,
            'price' => 99.99,
            'total' => 99.99
        ]);
        
        return $order;
    }

    public function test_guest_cannot_access_rating_creation_page()
    {
        $response = $this->get(route('ratings.create', $this->product));
        
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_without_purchase_cannot_create_rating()
    {
        $response = $this->actingAs($this->buyer)
            ->get(route('ratings.create', $this->product));
        
        $response->assertRedirect(route('products.show', $this->product->slug))
            ->assertSessionHas('error', 'You can only review products you have purchased.');
    }

    public function test_user_with_purchase_can_access_rating_creation_page()
    {
        $this->createPaidOrderWithProduct($this->buyer->id, $this->product->id);
        
        $response = $this->actingAs($this->buyer)
            ->get(route('ratings.create', $this->product));
        
        $response->assertStatus(200)
            ->assertViewIs('ratings.create')
            ->assertViewHas('product', $this->product);
    }

    public function test_user_can_create_rating_with_valid_data()
    {
        $this->createPaidOrderWithProduct($this->buyer->id, $this->product->id);
        
        $ratingData = [
            'rating' => 5,
            'review' => 'Excellent product! Highly recommended.'
        ];
        
        $response = $this->actingAs($this->buyer)
            ->post(route('ratings.store', $this->product), $ratingData);
        
        $response->assertRedirect(route('products.show', $this->product->slug))
            ->assertSessionHas('success', 'Review submitted successfully!');
        
        $this->assertDatabaseHas('ratings', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 5,
            'review' => 'Excellent product! Highly recommended.',
            'is_verified_purchase' => true
        ]);
    }

    public function test_user_cannot_create_duplicate_rating()
    {
        $this->createPaidOrderWithProduct($this->buyer->id, $this->product->id);
        
        // Create first rating
        Rating::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'review' => 'Good product',
            'is_verified_purchase' => true
        ]);
        
        $ratingData = [
            'rating' => 5,
            'review' => 'Trying to create duplicate rating'
        ];
        
        $response = $this->actingAs($this->buyer)
            ->post(route('ratings.store', $this->product), $ratingData);
        
        $response->assertRedirect(route('products.show', $this->product->slug))
            ->assertSessionHas('error', 'You have already reviewed this product.');
    }

    public function test_rating_validation_rules()
    {
        $this->createPaidOrderWithProduct($this->buyer->id, $this->product->id);
        
        // Test invalid rating (too high)
        $response = $this->actingAs($this->buyer)
            ->post(route('ratings.store', $this->product), [
                'rating' => 6,
                'review' => 'Test review'
            ]);
        
        $response->assertSessionHasErrors(['rating']);
        
        // Test invalid rating (too low)
        $response = $this->actingAs($this->buyer)
            ->post(route('ratings.store', $this->product), [
                'rating' => 0,
                'review' => 'Test review'
            ]);
        
        $response->assertSessionHasErrors(['rating']);
        
        // Test review too long
        $response = $this->actingAs($this->buyer)
            ->post(route('ratings.store', $this->product), [
                'rating' => 5,
                'review' => str_repeat('a', 1001)
            ]);
        
        $response->assertSessionHasErrors(['review']);
    }

    public function test_user_can_edit_own_rating()
    {
        $this->createPaidOrderWithProduct($this->buyer->id, $this->product->id);
        
        $rating = Rating::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'review' => 'Original review',
            'is_verified_purchase' => true
        ]);
        
        $response = $this->actingAs($this->buyer)
            ->get(route('ratings.edit', [$this->product, $rating]));
        
        $response->assertStatus(200)
            ->assertViewIs('ratings.edit')
            ->assertViewHas('rating', $rating);
    }

    public function test_user_cannot_edit_others_rating()
    {
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@test.com',
            'role' => UserRole::BUYER,
            'provider' => 'google',
            'provider_id' => '111111111'
        ]);
        
        $rating = Rating::create([
            'user_id' => $otherUser->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'review' => 'Other user review',
            'is_verified_purchase' => true
        ]);
        
        $response = $this->actingAs($this->buyer)
            ->get(route('ratings.edit', [$this->product, $rating]));
        
        $response->assertStatus(403);
    }

    public function test_user_can_update_own_rating()
    {
        $rating = Rating::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'review' => 'Original review',
            'is_verified_purchase' => true
        ]);
        
        $updateData = [
            'rating' => 5,
            'review' => 'Updated review - much better!'
        ];
        
        $response = $this->actingAs($this->buyer)
            ->put(route('ratings.update', [$this->product, $rating]), $updateData);
        
        $response->assertRedirect(route('products.show', $this->product->slug))
            ->assertSessionHas('success', 'Review updated successfully!');
        
        $this->assertDatabaseHas('ratings', [
            'id' => $rating->id,
            'rating' => 5,
            'review' => 'Updated review - much better!'
        ]);
    }

    public function test_user_can_delete_own_rating()
    {
        $rating = Rating::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'review' => 'Review to be deleted',
            'is_verified_purchase' => true
        ]);
        
        $response = $this->actingAs($this->buyer)
            ->delete(route('ratings.destroy', [$this->product, $rating]));
        
        $response->assertJson([
            'success' => true,
            'message' => 'Review deleted successfully!'
        ]);
        
        $this->assertDatabaseMissing('ratings', [
            'id' => $rating->id
        ]);
    }

    public function test_can_review_eligibility_check()
    {
        // Test without purchase
        $response = $this->actingAs($this->buyer)
            ->get(route('ratings.can-review', $this->product));
        
        $response->assertJson([
            'can_review' => false,
            'reason' => 'not_purchased'
        ]);
        
        $this->createPaidOrderWithProduct($this->buyer->id, $this->product->id);
        
        // Test with purchase
        $response = $this->actingAs($this->buyer)
            ->get(route('ratings.can-review', $this->product));
        
        $response->assertJson([
            'can_review' => true
        ]);
        
        // Create a rating
        Rating::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'review' => 'Test review',
            'is_verified_purchase' => true
        ]);
        
        // Test with existing rating
        $response = $this->actingAs($this->buyer)
            ->get(route('ratings.can-review', $this->product));
        
        $response->assertJson([
            'can_review' => false,
            'reason' => 'already_reviewed'
        ]);
    }

    public function test_product_average_rating_calculation()
    {
        // Create multiple ratings
        $ratings = [
            ['user_id' => $this->buyer->id, 'rating' => 5],
            ['user_id' => $this->admin->id, 'rating' => 4],
        ];
        
        foreach ($ratings as $ratingData) {
            Rating::create([
                'user_id' => $ratingData['user_id'],
                'product_id' => $this->product->id,
                'rating' => $ratingData['rating'],
                'review' => 'Test review',
                'is_verified_purchase' => true
            ]);
        }
        
        // Refresh the product to get updated average
        $this->product->refresh();
        
        $this->assertEquals(4.5, $this->product->average_rating);
    }
}