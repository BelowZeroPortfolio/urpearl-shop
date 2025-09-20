<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->inventoryService = app(InventoryService::class);
    }

    public function test_low_stock_notification_created_when_inventory_decremented()
    {
        Mail::fake();

        $product = Product::factory()->create(['name' => 'Test Pearl Necklace']);
        
        // Create inventory with stock above threshold
        $product->inventory()->create([
            'quantity' => 15,
            'low_stock_threshold' => 10,
        ]);

        // Verify no notifications exist initially
        $this->assertEquals(0, $this->admin->notifications()->count());

        // Decrement stock to trigger low stock notification
        $result = $this->inventoryService->decrementStock($product, 10); // This should leave 5, which is below threshold of 10
        $this->assertTrue($result);

        // Verify notification was created
        $this->assertEquals(1, $this->admin->notifications()->count());
        
        $notification = $this->admin->notifications()->first();
        $this->assertEquals(NotificationType::LOW_STOCK, $notification->type);
        $this->assertEquals('Low Stock Alert', $notification->title);
        $this->assertStringContainsString('Test Pearl Necklace', $notification->message);
        $this->assertEquals($product->id, $notification->payload['product_id']);

        // Verify email was sent
        Mail::assertSent(\App\Mail\LowStockAlert::class, function ($mail) use ($product) {
            return $mail->product->id === $product->id;
        });
    }

    public function test_duplicate_low_stock_notifications_not_created()
    {
        Mail::fake();

        $product = Product::factory()->create(['name' => 'Test Pearl Earrings']);
        
        // Set stock below threshold
        $this->inventoryService->updateStock($product, 5);
        $this->inventoryService->updateLowStockThreshold($product, 10);

        // First notification should be created
        $this->assertEquals(1, $this->admin->notifications()->count());

        // Decrement stock further - should not create another notification
        $this->inventoryService->decrementStock($product, 2);

        // Should still only have one notification
        $this->assertEquals(1, $this->admin->notifications()->count());
    }

    public function test_low_stock_notifications_cleared_when_stock_replenished()
    {
        $product = Product::factory()->create(['name' => 'Test Pearl Bracelet']);
        
        // Create inventory with stock below threshold to trigger notification
        $product->inventory()->create([
            'quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        // Manually trigger notification creation since we bypassed the service
        $this->inventoryService->checkLowStockAndNotify($product->inventory);

        // Verify notification was created
        $this->assertEquals(1, $this->admin->notifications()->unread()->count());

        // Replenish stock above threshold
        $result = $this->inventoryService->incrementStock($product, 10); // Now at 15, above threshold of 10
        $this->assertTrue($result);

        // Verify notification was cleared
        $this->assertEquals(0, $this->admin->notifications()->unread()->count());
    }

    public function test_notification_bell_shows_correct_count()
    {
        $this->actingAs($this->admin);

        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);
        
        // Create low stock for both products
        $this->inventoryService->updateStock($product1, 3);
        $this->inventoryService->updateLowStockThreshold($product1, 10);
        
        $this->inventoryService->updateStock($product2, 2);
        $this->inventoryService->updateLowStockThreshold($product2, 10);

        // Check API returns correct count
        $response = $this->get(route('admin.notifications.api'));
        
        $response->assertStatus(200);
        $response->assertJson(['unread_count' => 2]);
        
        $data = $response->json();
        $this->assertCount(2, $data['notifications']);
    }
}