<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Mail\LowStockAlert;
use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Create a low stock notification for admin users.
     */
    public function createLowStockNotification(Product $product): void
    {
        $adminUsers = User::where('role', UserRole::ADMIN)->get();

        foreach ($adminUsers as $admin) {
            // Avoid creating duplicate low stock notifications for the same admin and product
            $existing = Notification::where('user_id', $admin->id)
                ->where('type', NotificationType::LOW_STOCK)
                ->whereJsonContains('payload->product_id', $product->id)
                ->whereNull('read_at')
                ->first();

            if ($existing) {
                continue;
            }

            Notification::create([
                'user_id' => $admin->id,
                'type' => NotificationType::LOW_STOCK,
                'title' => 'Low Stock Alert',
                'message' => "Product '{$product->name}' is running low on stock. Current quantity: {$product->inventory->quantity}",
                'payload' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'current_quantity' => $product->inventory->quantity,
                    'threshold' => $product->inventory->low_stock_threshold,
                ],
            ]);

            // Send email notification
            $this->sendLowStockEmail($admin, $product);
        }
    }

    /**
     * Create an order notification for admin users.
     */
    public function createOrderNotification(string $type, array $data): void
    {
        $adminUsers = User::where('role', UserRole::ADMIN)->get();

        foreach ($adminUsers as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => $type === 'created' ? NotificationType::ORDER_CREATED : NotificationType::ORDER_STATUS_CHANGED,
                'title' => $type === 'created' ? 'New Order Received' : 'Order Status Updated',
                'message' => $data['message'],
                'payload' => $data,
            ]);
        }
    }

    /**
     * Get unread notifications count for a user.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }

    /**
     * Get recent notifications for a user.
     */
    public function getRecentNotifications(User $user, int $limit = 10)
    {
        return $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): bool
    {
        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): void
    {
        $user->notifications()->unread()->update(['read_at' => now()]);
    }

    /**
     * Send low stock email notification.
     */
    private function sendLowStockEmail(User $admin, Product $product): void
    {
        try {
            Mail::to($admin->email)->send(new LowStockAlert($product, $admin));
        } catch (\Exception $e) {
            // Log the error but don't fail the notification creation
            \Log::error('Failed to send low stock email notification', [
                'admin_id' => $admin->id,
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}