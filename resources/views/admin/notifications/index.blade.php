@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-600 mt-2">Manage your system notifications and alerts</p>
        </div>
        
        @if($unreadCount > 0)
        <div class="flex items-center space-x-4">
            <span class="bg-pink-100 text-pink-800 px-3 py-1 rounded-full text-sm font-medium">
                {{ $unreadCount }} unread
            </span>
            <button 
                onclick="markAllAsRead()" 
                class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
            >
                Mark All as Read
            </button>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        @if($notifications->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($notifications as $notification)
                <div class="notification-item p-6 {{ $notification->isUnread() ? 'bg-pink-50 border-l-4 border-l-pink-500' : '' }}" 
                     data-notification-id="{{ $notification->id }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <div class="notification-icon">
                                    @switch($notification->type->value)
                                        @case('low_stock')
                                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            @break
                                        @case('order_created')
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            @break
                                        @default
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                    @endswitch
                                </div>
                                
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $notification->title }}</h3>
                                    <p class="text-gray-600 mt-1">{{ $notification->message }}</p>
                                </div>
                                
                                @if($notification->isUnread())
                                <div class="w-3 h-3 bg-pink-500 rounded-full"></div>
                                @endif
                            </div>
                            
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                
                                <div class="flex items-center space-x-2">
                                    @if($notification->isUnread())
                                    <button 
                                        onclick="markAsRead({{ $notification->id }})"
                                        class="text-pink-600 hover:text-pink-700 text-sm font-medium"
                                    >
                                        Mark as Read
                                    </button>
                                    @endif
                                    
                                    @if($notification->type->value === 'low_stock' && isset($notification->payload['product_id']))
                                    <a 
                                        href="{{ route('admin.inventory.index') }}" 
                                        class="bg-pink-600 hover:bg-pink-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors"
                                    >
                                        Manage Inventory
                                    </a>
                                    @endif
                                    
                                    <button 
                                        onclick="deleteNotification({{ $notification->id }})"
                                        class="text-red-600 hover:text-red-700 text-sm font-medium"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-12"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                <p class="text-gray-500">You're all caught up! No new notifications at this time.</p>
            </div>
        @endif
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/admin/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark notification as read');
    });
}

function markAllAsRead() {
    fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark all notifications as read');
    });
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(`/admin/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-notification-id="${notificationId}"]`).remove();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete notification');
        });
    }
}
</script>
@endsection