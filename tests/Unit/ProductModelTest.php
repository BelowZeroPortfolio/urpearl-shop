<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_correct_fillable_attributes()
    {
        $fillable = [
            'name', 'slug', 'description', 'price', 'sku', 'category_id', 'image'
        ];

        $product = new Product();
        $this->assertEquals($fillable, $product->getFillable());
    }

    public function test_product_casts_price_to_decimal()
    {
        $product = Product::factory()->create(['price' => 123.456]);
        
        $this->assertEquals('123.46', $product->price);
    }

    public function test_product_auto_generates_slug_on_creation()
    {
        $product = Product::factory()->create(['name' => 'Beautiful Pearl Necklace']);
        
        $this->assertStringContainsString('beautiful-pearl-necklace', $product->slug);
    }

    public function test_product_updates_slug_when_name_changes()
    {
        $product = Product::factory()->create(['name' => 'Original Name']);
        $originalSlug = $product->slug;
        
        $product->update(['name' => 'New Product Name']);
        
        $this->assertNotEquals($originalSlug, $product->fresh()->slug);
        $this->assertStringContainsString('new-product-name', $product->fresh()->slug);
    }

    public function test_product_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_has_one_inventory()
    {
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Inventory::class, $product->inventory);
        $this->assertEquals($inventory->id, $product->inventory->id);
    }

    public function test_product_has_many_cart_items()
    {
        $product = Product::factory()->create();
        CartItem::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertCount(3, $product->cartItems);
        $this->assertInstanceOf(CartItem::class, $product->cartItems->first());
    }

    public function test_product_has_many_order_items()
    {
        $product = Product::factory()->create();
        OrderItem::factory()->count(2)->create(['product_id' => $product->id]);

        $this->assertCount(2, $product->orderItems);
        $this->assertInstanceOf(OrderItem::class, $product->orderItems->first());
    }

    public function test_product_has_many_ratings()
    {
        $product = Product::factory()->create();
        Rating::factory()->count(4)->create(['product_id' => $product->id]);

        $this->assertCount(4, $product->ratings);
        $this->assertInstanceOf(Rating::class, $product->ratings->first());
    }

    public function test_average_rating_attribute_calculates_correctly()
    {
        $product = Product::factory()->create();
        
        // Create ratings: 5, 4, 3 = average 4.0
        Rating::factory()->create(['product_id' => $product->id, 'rating' => 5]);
        Rating::factory()->create(['product_id' => $product->id, 'rating' => 4]);
        Rating::factory()->create(['product_id' => $product->id, 'rating' => 3]);

        $this->assertEquals(4.0, $product->average_rating);
    }

    public function test_average_rating_returns_zero_when_no_ratings()
    {
        $product = Product::factory()->create();
        
        $this->assertEquals(0.0, $product->average_rating);
    }

    public function test_stock_status_returns_out_of_stock_when_no_inventory()
    {
        $product = Product::factory()->create();
        
        $this->assertEquals('out_of_stock', $product->stock_status);
    }

    public function test_stock_status_returns_out_of_stock_when_quantity_zero()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 0,
            'low_stock_threshold' => 5
        ]);
        
        $this->assertEquals('out_of_stock', $product->stock_status);
    }

    public function test_stock_status_returns_low_stock_when_below_threshold()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'low_stock_threshold' => 5
        ]);
        
        $this->assertEquals('low_stock', $product->stock_status);
    }

    public function test_stock_status_returns_in_stock_when_above_threshold()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);
        
        $this->assertEquals('in_stock', $product->stock_status);
    }

    public function test_is_in_stock_returns_true_when_quantity_available()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5
        ]);
        
        $this->assertTrue($product->isInStock());
    }

    public function test_is_in_stock_returns_false_when_no_inventory()
    {
        $product = Product::factory()->create();
        
        $this->assertFalse($product->isInStock());
    }

    public function test_is_in_stock_returns_false_when_quantity_zero()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 0
        ]);
        
        $this->assertFalse($product->isInStock());
    }
}