<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_notification_creation()
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $inventoryService = app(InventoryService::class);
        
        $product = Product::factory()->create(['name' => 'Debug Product']);
        
        // Set initial stock
        $result = $inventoryService->updateStock($product, 15);
        $this->assertTrue($result);
        
        // Set threshold
        $result = $inventoryService->updateLowStockThreshold($product, 10);
        $this->assertTrue($result);
        
        // Check initial state
        $product->refresh();
        $this->assertEquals(15, $product->inventory->quantity);
        $this->assertEquals(10, $product->inventory->low_stock_threshold);
        $this->assertFalse($product->inventory->isLowStock());
        
        // Clear any existing notifications
        $admin->notifications()->delete();
        $this->assertEquals(0, $admin->notifications()->count());
        
        // Decrement stock to trigger low stock
        $result = $inventoryService->decrementStock($product, 10);
        $this->assertTrue($result);
        
        // Check final state
        $product->refresh();
        $this->assertEquals(5, $product->inventory->quantity);
        $this->assertTrue($product->inventory->isLowStock());
        
        // Check notifications
        $notificationCount = $admin->notifications()->count();
        $this->assertEquals(1, $notificationCount, "Expected 1 notification, got {$notificationCount}");
    }
}