<?php

namespace Tests\Feature\Admin;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $buyer;
    protected NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->buyer = User::factory()->create(['role' => UserRole::BUYER]);
        $this->notificationService = app(NotificationService::class);
    }

    public function test_admin_can_view_notifications_index()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.notifications.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.notifications.index');
    }

    public function test_buyer_cannot_access_notifications()
    {
        $this->actingAs($this->buyer);

        $response = $this->get(route('admin.notifications.index'));

        $response->assertStatus(403);
    }

    public function test_can_get_notifications_via_api()
    {
        $this->actingAs($this->admin);

        // Create a test notification
        Notification::create([
            'user_id' => $this->admin->id,
            'type' => NotificationType::LOW_STOCK,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'payload' => ['test' => 'data'],
        ]);

        $response = $this->get(route('admin.notifications.api'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notifications' => [
                '*' => [
                    'id',
                    'title',
                    'message',
                    'type',
                    'is_read',
                    'created_at',
                    'payload',
                ]
            ],
            'unread_count'
        ]);
    }

    public function test_can_mark_notification_as_read()
    {
        $this->actingAs($this->admin);

        $notification = Notification::create([
            'user_id' => $this->admin->id,
            'type' => NotificationType::LOW_STOCK,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'payload' => ['test' => 'data'],
        ]);

        $this->assertNull($notification->read_at);

        $response = $this->post(route('admin.notifications.read', $notification));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_can_mark_all_notifications_as_read()
    {
        $this->actingAs($this->admin);

        // Create multiple unread notifications
        Notification::create([
            'user_id' => $this->admin->id,
            'type' => NotificationType::LOW_STOCK,
            'title' => 'Test Notification 1',
            'message' => 'This is test notification 1',
            'payload' => ['test' => 'data1'],
        ]);

        Notification::create([
            'user_id' => $this->admin->id,
            'type' => NotificationType::ORDER_CREATED,
            'title' => 'Test Notification 2',
            'message' => 'This is test notification 2',
            'payload' => ['test' => 'data2'],
        ]);

        $this->assertEquals(2, $this->admin->notifications()->unread()->count());

        $response = $this->post(route('admin.notifications.mark-all-read'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals(0, $this->admin->notifications()->unread()->count());
    }

    public function test_can_delete_notification()
    {
        $this->actingAs($this->admin);

        $notification = Notification::create([
            'user_id' => $this->admin->id,
            'type' => NotificationType::LOW_STOCK,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'payload' => ['test' => 'data'],
        ]);

        $response = $this->delete(route('admin.notifications.destroy', $notification));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_cannot_access_other_users_notifications()
    {
        $otherAdmin = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $notification = Notification::create([
            'user_id' => $otherAdmin->id,
            'type' => NotificationType::LOW_STOCK,
            'title' => 'Other Admin Notification',
            'message' => 'This belongs to another admin',
            'payload' => ['test' => 'data'],
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.notifications.read', $notification));
        $response->assertStatus(403);

        $response = $this->delete(route('admin.notifications.destroy', $notification));
        $response->assertStatus(403);
    }

    public function test_notification_service_creates_low_stock_notification()
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $product->inventory()->create([
            'quantity' => 5,
            'low_stock_threshold' => 10,
        ]);

        $this->notificationService->createLowStockNotification($product);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'type' => NotificationType::LOW_STOCK->value,
            'title' => 'Low Stock Alert',
        ]);
    }

    public function test_notification_service_gets_unread_count()
    {
        // Create some notifications
        Notification::create([
            'user_id' => $this->admin->id,
            'type' => NotificationType::LOW_STOCK,
            'title' => 'Unread Notification 1',
            'message' => 'This is unread',
            'payload' => [],
        ]);

        $readNotification = Notification::create([
            'user_id' => $this->admin->id,
            'type' => NotificationType::ORDER_CREATED,
            'title' => 'Read Notification',
            'message' => 'This is read',
            'payload' => [],
        ]);
        $readNotification->markAsRead();

        $unreadCount = $this->notificationService->getUnreadCount($this->admin);

        $this->assertEquals(1, $unreadCount);
    }
}