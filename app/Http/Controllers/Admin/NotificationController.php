<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Display the admin notification dashboard.
     */
    public function index(): View
    {
        $notifications = $this->notificationService->getRecentNotifications(
            auth()->user(),
            20
        );

        $unreadCount = $this->notificationService->getUnreadCount(auth()->user());

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get notifications for the notification bell dropdown.
     */
    public function getNotifications(): JsonResponse
    {
        $notifications = $this->notificationService->getRecentNotifications(
            auth()->user(),
            10
        );

        $unreadCount = $this->notificationService->getUnreadCount(auth()->user());

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type->value,
                    'is_read' => $notification->isRead(),
                    'created_at' => $notification->created_at->diffForHumans(),
                    'payload' => $notification->payload,
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $this->notificationService->markAllAsRead(auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }
}