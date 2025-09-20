<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_item_has_correct_fillable_attributes()
    {
        $fillable = ['user_id', 'product_id', 'quantity'];

        $cartItem = new CartItem();
        $this->assertEquals($fillable, $cartItem->getFillable());
    }

    public function test_cart_item_casts_quantity_to_integer()
    {
        $cartItem = CartItem::factory()->create(['quantity' => '5']);
        
        $this->assertIsInt($cartItem->quantity);
        $this->assertEquals(5, $cartItem->quantity);
    }

    public function test_cart_item_belongs_to_user()
    {
        $user = User::factory()->create();
        $cartItem = CartItem::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cartItem->user);
        $this->assertEquals($user->id, $cartItem->user->id);
    }

    public function test_cart_item_belongs_to_product()
    {
        $product = Product::factory()->create();
        $cartItem = CartItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $cartItem->product);
        $this->assertEquals($product->id, $cartItem->product->id);
    }

    public function test_total_price_attribute_calculates_correctly()
    {
        $product = Product::factory()->create(['price' => 25.50]);
        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        $expectedTotal = 3 * 25.50; // 76.50
        $this->assertEquals($expectedTotal, $cartItem->total_price);
    }

    public function test_total_price_attribute_handles_single_quantity()
    {
        $product = Product::factory()->create(['price' => 100.00]);
        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $this->assertEquals(100.00, $cartItem->total_price);
    }

    public function test_total_price_attribute_handles_zero_quantity()
    {
        $product = Product::factory()->create(['price' => 50.00]);
        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'quantity' => 0
        ]);

        $this->assertEquals(0.00, $cartItem->total_price);
    }
}