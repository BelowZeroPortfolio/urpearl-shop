@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-pink-50 to-orange-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('orders.index') }}" 
               class="inline-flex items-center text-pink-600 hover:text-pink-700 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Orders
            </a>
        </div>

        <!-- Order Header -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 py-6">
                <div class="flex justify-between items-start text-white">
                    <div>
                        <h1 class="text-2xl font-bold mb-2">Order #{{ $order->id }}</h1>
                        <p class="text-pink-100">Placed on {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium
                            @if($order->status->value === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status->value === 'paid') bg-green-100 text-green-800
                            @elseif($order->status->value === 'shipped') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($order->status->value) }}
                        </span>
                        <p class="text-pink-100 text-sm mt-2">Total: ₱{{ number_format($order->total_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Order Progress -->
            <div class="px-6 py-4 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        @foreach($statusHistory as $index => $status)
                        @if($index > 0)
                        <!-- Connector -->
                        <div class="flex-1 h-0.5 {{ $status['completed'] ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                        @endif
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center
                                {{ $status['completed'] ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                                @if($status['completed'])
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                @else
                                    <span class="text-xs font-medium">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ $status['status'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Items</h2>
            <div class="space-y-4">
                @foreach($order->orderItems as $item)
                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    @if($item->product->image)
                    <img src="{{ Storage::url($item->product->image) }}" 
                         alt="{{ $item->product->name }}" 
                         class="w-20 h-20 object-cover rounded-lg">
                    @else
                    <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    @endif
                    
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 text-lg">{{ $item->product->name }}</h3>
                        <p class="text-gray-600">SKU: {{ $item->product->sku }}</p>
                        <p class="text-gray-600">{{ Str::limit($item->product->description, 100) }}</p>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Quantity</p>
                        <p class="font-semibold text-gray-900">{{ $item->quantity }}</p>
                    </div>
                    
                    <div class="text-right">
                        <p class="text-sm text-gray-600">₱{{ number_format($item->price, 2) }} each</p>
                        <p class="font-semibold text-gray-900 text-lg">₱{{ number_format($item->total_price, 2) }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Order Total -->
            <div class="border-t mt-6 pt-6">
                <div class="flex justify-between items-center">
                    <span class="text-xl font-semibold text-gray-900">Total Amount</span>
                    <span class="text-2xl font-bold text-pink-600">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Shipping Information -->
        @if($order->shipping_address)
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Shipping Address</h2>
            <div class="bg-blue-50 rounded-xl p-4">
                @if(is_array($order->shipping_address))
                    <div class="text-gray-700">
                        @if(isset($order->shipping_address['name']))
                            <p class="font-semibold text-lg">{{ $order->shipping_address['name'] }}</p>
                        @endif
                        @if(isset($order->shipping_address['address_line_1']))
                            <p>{{ $order->shipping_address['address_line_1'] }}</p>
                        @endif
                        @if(isset($order->shipping_address['address_line_2']) && $order->shipping_address['address_line_2'])
                            <p>{{ $order->shipping_address['address_line_2'] }}</p>
                        @endif
                        <p>
                            @if(isset($order->shipping_address['city']))
                                {{ $order->shipping_address['city'] }}
                            @endif
                            @if(isset($order->shipping_address['state']))
                                , {{ $order->shipping_address['state'] }}
                            @endif
                            @if(isset($order->shipping_address['postal_code']))
                                {{ $order->shipping_address['postal_code'] }}
                            @endif
                        </p>
                        @if(isset($order->shipping_address['country']))
                            <p>{{ $order->shipping_address['country'] }}</p>
                        @endif
                    </div>
                @else
                    <p class="text-gray-700">{{ $order->shipping_address }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Order Status History -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Status History</h2>
            <div class="space-y-4">
                @foreach($statusHistory as $status)
                <div class="flex items-start space-x-4 p-4 {{ $status['completed'] ? 'bg-green-50' : 'bg-gray-50' }} rounded-xl">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            {{ $status['completed'] ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                            @if($status['completed'])
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold {{ $status['completed'] ? 'text-green-800' : 'text-gray-700' }}">
                                {{ $status['status'] }}
                            </h3>
                            @if($status['date'])
                            <span class="text-sm text-gray-500">
                                {{ $status['date']->format('M j, Y g:i A') }}
                            </span>
                            @endif
                        </div>
                        <p class="text-gray-600 mt-1">{{ $status['description'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4">
            @if($order->isPending() || $order->isPaid())
            <button onclick="confirmCancel()" 
                    class="inline-flex items-center justify-center px-6 py-3 border border-red-300 text-red-700 rounded-xl hover:bg-red-50 transition-colors duration-200 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Cancel Order
            </button>
            @endif
            
            <a href="{{ route('orders.index') }}" 
               class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-pink-500 to-pink-600 text-white rounded-xl font-medium hover:from-pink-600 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                View All Orders
            </a>
        </div>
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
            <form action="{{ route('orders.cancel', $order) }}" method="POST" class="flex-1">
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
function confirmCancel() {
    document.getElementById('cancelModal').classList.remove('hidden');
    document.getElementById('cancelModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancelModal').classList.remove('flex');
}
</script>
@endsection