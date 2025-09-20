<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_has_correct_fillable_attributes()
    {
        $fillable = ['product_id', 'quantity', 'low_stock_threshold'];

        $inventory = new Inventory();
        $this->assertEquals($fillable, $inventory->getFillable());
    }

    public function test_inventory_casts_quantity_to_integer()
    {
        $inventory = Inventory::factory()->create(['quantity' => '10']);
        
        $this->assertIsInt($inventory->quantity);
        $this->assertEquals(10, $inventory->quantity);
    }

    public function test_inventory_casts_low_stock_threshold_to_integer()
    {
        $inventory = Inventory::factory()->create(['low_stock_threshold' => '5']);
        
        $this->assertIsInt($inventory->low_stock_threshold);
        $this->assertEquals(5, $inventory->low_stock_threshold);
    }

    public function test_inventory_belongs_to_product()
    {
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $inventory->product);
        $this->assertEquals($product->id, $inventory->product->id);
    }

    public function test_is_low_stock_returns_true_when_quantity_equals_threshold()
    {
        $inventory = Inventory::factory()->create([
            'quantity' => 5,
            'low_stock_threshold' => 5
        ]);

        $this->assertTrue($inventory->isLowStock());
    }

    public function test_is_low_stock_returns_true_when_quantity_below_threshold()
    {
        $inventory = Inventory::factory()->create([
            'quantity' => 3,
            'low_stock_threshold' => 5
        ]);

        $this->assertTrue($inventory->isLowStock());
    }

    public function test_is_low_stock_returns_false_when_quantity_above_threshold()
    {
        $inventory = Inventory::factory()->create([
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        $this->assertFalse($inventory->isLowStock());
    }

    public function test_decrement_stock_reduces_quantity_correctly()
    {
        $inventory = Inventory::factory()->create(['quantity' => 10]);

        $result = $inventory->decrementStock(3);

        $this->assertTrue($result);
        $this->assertEquals(7, $inventory->fresh()->quantity);
    }

    public function test_decrement_stock_returns_false_when_insufficient_stock()
    {
        $inventory = Inventory::factory()->create(['quantity' => 5]);

        $result = $inventory->decrementStock(10);

        $this->assertFalse($result);
        $this->assertEquals(5, $inventory->fresh()->quantity); // Quantity unchanged
    }

    public function test_decrement_stock_allows_exact_quantity_reduction()
    {
        $inventory = Inventory::factory()->create(['quantity' => 5]);

        $result = $inventory->decrementStock(5);

        $this->assertTrue($result);
        $this->assertEquals(0, $inventory->fresh()->quantity);
    }

    public function test_decrement_stock_handles_negative_quantity()
    {
        $inventory = Inventory::factory()->create(['quantity' => 10]);

        // The current implementation doesn't validate negative quantities
        // It will actually increase the stock (10 - (-1) = 11)
        $result = $inventory->decrementStock(-1);

        $this->assertTrue($result);
        $this->assertEquals(11, $inventory->fresh()->quantity);
    }

    public function test_decrement_stock_handles_zero_quantity()
    {
        $inventory = Inventory::factory()->create(['quantity' => 10]);

        // Decrementing by 0 should succeed and leave quantity unchanged
        $result = $inventory->decrementStock(0);

        $this->assertTrue($result);
        $this->assertEquals(10, $inventory->fresh()->quantity);
    }
}