<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\Notification;
use App\Models\Category;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Enums\NotificationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $inventoryService;
    protected NotificationService $notificationService;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->notificationService = Mockery::mock(NotificationService::class);
        $this->inventoryService = new InventoryService($this->notificationService);
        
        $category = Category::factory()->create();
        $this->product = Product::factory()->create(['category_id' => $category->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_stock_creates_inventory_when_none_exists()
    {
        $result = $this->inventoryService->updateStock($this->product, 15);

        $this->assertTrue($result);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $this->product->id,
            'quantity' => 15,
            'low_stock_threshold' => 10 // Default threshold
        ]);
    }

    public function test_update_stock_updates_existing_inventory()
    {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 5,
            'low_stock_threshold' => 3
        ]);

        $result = $this->inventoryService->updateStock($this->product, 20);

        $this->assertTrue($result);
        $this->assertEquals(20, $inventory->fresh()->quantity);
        $this->assertEquals(3, $inventory->fresh()->low_stock_threshold); // Unchanged
    }

    public function test_increment_stock_increases_quantity()
    {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 10
        ]);

        $result = $this->inventoryService->incrementStock($this->product, 5);

        $this->assertTrue($result);
        $this->assertEquals(15, $inventory->fresh()->quantity);
    }

    public function test_increment_stock_creates_inventory_when_none_exists()
    {
        $result = $this->inventoryService->incrementStock($this->product, 8);

        $this->assertTrue($result);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $this->product->id,
            'quantity' => 8
        ]);
    }

    public function test_decrement_stock_reduces_quantity()
    {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 10
        ]);

        $result = $this->inventoryService->decrementStock($this->product, 3);

        $this->assertTrue($result);
        $this->assertEquals(7, $inventory->fresh()->quantity);
    }

    public function test_decrement_stock_fails_with_insufficient_stock()
    {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 5
        ]);

        $result = $this->inventoryService->decrementStock($this->product, 10);

        $this->assertFalse($result);
        $this->assertEquals(5, $inventory->fresh()->quantity); // Unchanged
    }

    public function test_decrement_stock_fails_when_no_inventory_exists()
    {
        $result = $this->inventoryService->decrementStock($this->product, 1);

        $this->assertFalse($result);
        $this->assertDatabaseMissing('inventories', [
            'product_id' => $this->product->id
        ]);
    }

    public function test_update_low_stock_threshold_updates_existing_inventory()
    {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        $result = $this->inventoryService->updateLowStockThreshold($this->product, 8);

        $this->assertTrue($result);
        $this->assertEquals(8, $inventory->fresh()->low_stock_threshold);
    }

    public function test_update_low_stock_threshold_creates_inventory_when_none_exists()
    {
        $result = $this->inventoryService->updateLowStockThreshold($this->product, 7);

        $this->assertTrue($result);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $this->product->id,
            'quantity' => 0,
            'low_stock_threshold' => 7
        ]);
    }

    public function test_get_low_stock_products_returns_correct_products()
    {
        $category = Category::factory()->create();
        
        // Create products with different stock levels
        $lowStockProduct = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create([
            'product_id' => $lowStockProduct->id,
            'quantity' => 3,
            'low_stock_threshold' => 5
        ]);

        $normalStockProduct = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create([
            'product_id' => $normalStockProduct->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        $lowStockProducts = $this->inventoryService->getLowStockProducts();

        $this->assertCount(1, $lowStockProducts);
        $this->assertEquals($lowStockProduct->id, $lowStockProducts->first()->id);
    }

    public function test_get_inventory_stats_returns_correct_statistics()
    {
        $category = Category::factory()->create();
        
        // Create products with different stock levels
        $inStockProduct = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create([
            'product_id' => $inStockProduct->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        $lowStockProduct = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create([
            'product_id' => $lowStockProduct->id,
            'quantity' => 3,
            'low_stock_threshold' => 5
        ]);

        $outOfStockProduct = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create([
            'product_id' => $outOfStockProduct->id,
            'quantity' => 0,
            'low_stock_threshold' => 5
        ]);

        $stats = $this->inventoryService->getInventoryStats();

        $this->assertEquals(4, $stats['total_products']); // Including the setUp product
        $this->assertEquals(1, $stats['low_stock_count']);
        $this->assertEquals(1, $stats['out_of_stock_count']);
        $this->assertEquals(3, $stats['in_stock_count']);
    }

    public function test_check_low_stock_and_notify_creates_notification()
    {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'low_stock_threshold' => 5
        ]);

        $this->notificationService
            ->shouldReceive('createLowStockNotification')
            ->once()
            ->with($this->product);

        $this->inventoryService->checkLowStockAndNotify($inventory);
    }

    public function test_check_low_stock_and_notify_does_not_create_notification_for_sufficient_stock()
    {
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        $this->notificationService
            ->shouldNotReceive('createLowStockNotification');

        $this->inventoryService->checkLowStockAndNotify($inventory);
    }

    public function test_bulk_update_inventory_processes_multiple_products()
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $updates = [
            [
                'product_id' => $product1->id,
                'quantity' => 15,
                'low_stock_threshold' => 8
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 20,
                'low_stock_threshold' => 10
            ]
        ];

        $results = $this->inventoryService->bulkUpdateInventory($updates);

        $this->assertCount(2, $results);
        $this->assertTrue($results[0]['success']);
        $this->assertTrue($results[1]['success']);

        // Verify inventory was created/updated
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product1->id,
            'quantity' => 15,
            'low_stock_threshold' => 8
        ]);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product2->id,
            'quantity' => 20,
            'low_stock_threshold' => 10
        ]);
    }

    public function test_bulk_update_inventory_handles_invalid_product_id()
    {
        $updates = [
            [
                'product_id' => 99999, // Non-existent product
                'quantity' => 15
            ]
        ];

        $results = $this->inventoryService->bulkUpdateInventory($updates);

        $this->assertCount(1, $results);
        $this->assertFalse($results[0]['success']);
        $this->assertEquals('Product not found', $results[0]['message']);
    }

    public function test_database_transactions_rollback_on_failure()
    {
        // Mock a database failure
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        DB::shouldReceive('commit')->never();

        // Force an exception by trying to update with invalid data
        $inventory = Inventory::factory()->create([
            'product_id' => $this->product->id,
            'quantity' => 10
        ]);

        // Mock the inventory to throw an exception
        $inventory = Mockery::mock($inventory);
        $inventory->shouldReceive('save')->andThrow(new \Exception('Database error'));

        $this->product->setRelation('inventory', $inventory);

        $result = $this->inventoryService->updateStock($this->product, 15);

        $this->assertFalse($result);
    }
}