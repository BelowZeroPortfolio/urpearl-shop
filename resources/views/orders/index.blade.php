@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-pink-50 to-orange-50 py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Orders</h1>
            <p class="text-gray-600">Track and manage your orders</p>
        </div>

        <!-- Order Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                <p class="text-sm text-gray-600">Total Orders</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 text-center">
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending_orders'] }}</p>
                <p class="text-sm text-gray-600">Pending</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ $stats['paid_orders'] }}</p>
                <p class="text-sm text-gray-600">Paid</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $stats['shipped_orders'] }}</p>
                <p class="text-sm text-gray-600">Shipped</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-4 text-center">
                <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled_orders'] }}</p>
                <p class="text-sm text-gray-600">Cancelled</p>
            </div>
        </div>

        <!-- Filters -->
        @if($orders->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Order ID</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Enter order ID"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Filter by Status</label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" 
                            class="px-6 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors duration-200 font-medium">
                        Apply Filters
                    </button>
                    <a href="{{ route('orders.index') }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200 font-medium">
                        Clear
                    </a>
                </div>
            </form>
        </div>
        @endif

        @if($orders->count() > 0)
            <!-- Orders List -->
            <div class="space-y-6">
                @foreach($orders as $order)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <!-- Order Header -->
                    <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 py-4">
                        <div class="flex justify-between items-center text-white">
                            <div>
                                <h3 class="text-lg font-semibold">Order #{{ $order->id }}</h3>
                                <p class="text-pink-100 text-sm">{{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($order->status->value === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status->value === 'paid') bg-green-100 text-green-800
                                    @elseif($order->status->value === 'shipped') bg-blue-100 text-blue-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($order->status->value) }}
                                </span>
                                <p class="text-pink-100 text-sm mt-1">₱{{ number_format($order->total_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Content -->
                    <div class="p-6">
                        <!-- Order Items Preview -->
                        <div class="mb-4">
                            <div class="flex items-center space-x-4">
                                @foreach($order->orderItems->take(3) as $item)
                                <div class="flex items-center space-x-2">
                                    @if($item->product->image)
                                    <img src="{{ Storage::url($item->product->image) }}" 
                                         alt="{{ $item->product->name }}" 
                                         class="w-12 h-12 object-cover rounded-lg">
                                    @else
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $item->product->name }}</p>
                                        <p class="text-xs text-gray-600">Qty: {{ $item->quantity }}</p>
                                    </div>
                                </div>
                                @endforeach
                                
                                @if($order->orderItems->count() > 3)
                                <div class="text-sm text-gray-600">
                                    +{{ $order->orderItems->count() - 3 }} more items
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="flex justify-between items-center text-sm text-gray-600 mb-4">
                            <span>{{ $order->orderItems->count() }} {{ Str::plural('item', $order->orderItems->count()) }}</span>
                            <span>Total: ₱{{ number_format($order->total_amount, 2) }}</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('orders.show', $order) }}" 
                               class="inline-flex items-center px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors duration-200 text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Details
                            </a>
                            
                            @if($order->isPending() || $order->isPaid())
                            <button onclick="confirmCancel({{ $order->id }})" 
                                    class="inline-flex items-center px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors duration-200 text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel Order
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Orders Yet</h3>
                <p class="text-gray-600 mb-6">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-pink-500 to-pink-600 text-white rounded-xl font-medium hover:from-pink-600 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Start Shopping
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Cancel Order Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cancel Order</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to cancel this order? This action cannot be undone and inventory will be restored.</p>
        <div class="flex gap-3">
            <button onclick="closeModal()" 
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                Keep Order
            </button>
            <form id="cancelForm" method="POST" class="flex-1">
                @csrf
                @method('PATCH')
                <button type="submit" 
                        class="w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                    Cancel Order
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmCancel(orderId) {
    document.getElementById('cancelForm').action = `/orders/${orderId}/cancel`;
    document.getElementById('cancelModal').classList.remove('hidden');
    document.getElementById('cancelModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancelModal').classList.remove('flex');
}
</script>
@endsection