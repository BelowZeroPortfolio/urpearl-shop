@extends('layouts.admin')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.orders.index') }}" 
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
                    @if($order->stripe_payment_id)
                    <p class="text-pink-100 text-sm mt-1">Stripe Payment: {{ $order->stripe_payment_id }}</p>
                    @endif
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
                    <!-- Pending -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ in_array($order->status->value, ['pending', 'paid', 'shipped']) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                            @if(in_array($order->status->value, ['pending', 'paid', 'shipped']))
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <span class="text-xs font-medium">1</span>
                            @endif
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">Order Placed</span>
                    </div>

                    <!-- Connector -->
                    <div class="flex-1 h-0.5 {{ in_array($order->status->value, ['paid', 'shipped']) ? 'bg-green-500' : 'bg-gray-300' }}"></div>

                    <!-- Paid -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ in_array($order->status->value, ['paid', 'shipped']) ? 'bg-green-500 text-white' : ($order->status->value === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-300 text-gray-600') }}">
                            @if(in_array($order->status->value, ['paid', 'shipped']))
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <span class="text-xs font-medium">2</span>
                            @endif
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">Payment Confirmed</span>
                    </div>

                    <!-- Connector -->
                    <div class="flex-1 h-0.5 {{ $order->status->value === 'shipped' ? 'bg-green-500' : 'bg-gray-300' }}"></div>

                    <!-- Shipped -->
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ $order->status->value === 'shipped' ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600' }}">
                            @if($order->status->value === 'shipped')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <span class="text-xs font-medium">3</span>
                            @endif
                        </div>
                        <span class="ml-2 text-sm font-medium text-gray-700">Shipped</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Order Items -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
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
            <div class="bg-white rounded-2xl shadow-lg p-6">
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
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Customer Information -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Customer Information</h2>
                <div class="flex items-center space-x-4 mb-4">
                    @if($order->user->avatar)
                    <img class="h-12 w-12 rounded-full" src="{{ $order->user->avatar }}" alt="{{ $order->user->name }}">
                    @else
                    <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-lg font-medium text-gray-600">{{ substr($order->user->name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-900">{{ $order->user->name }}</p>
                        <p class="text-gray-600">{{ $order->user->email }}</p>
                        <p class="text-sm text-gray-500">{{ ucfirst($order->user->role->value) }}</p>
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    <p>Customer since: {{ $order->user->created_at->format('F j, Y') }}</p>
                    <p>Total orders: {{ $order->user->orders->count() }}</p>
                </div>
            </div>

            <!-- Order Status Management -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Update Order Status</h2>
                
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" 
                                name="status" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            <option value="pending" {{ $order->status->value === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->status->value === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="shipped" {{ $order->status->value === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="cancelled" {{ $order->status->value === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="3" 
                                  placeholder="Add any notes about this status change..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"></textarea>
                    </div>

                    <button type="submit" 
                            class="w-full px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors duration-200 font-medium">
                        Update Status
                    </button>
                </form>

                <!-- Status Transition Rules -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Status Transition Rules</h3>
                    <ul class="text-xs text-gray-600 space-y-1">
                        <li>• Pending → Paid or Cancelled</li>
                        <li>• Paid → Shipped or Cancelled</li>
                        <li>• Shipped → No changes allowed</li>
                        <li>• Cancelled → No changes allowed</li>
                    </ul>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Timeline</h2>
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Order Placed</p>
                            <p class="text-xs text-gray-500">{{ $order->created_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                    
                    @if($order->updated_at != $order->created_at)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Last Updated</p>
                            <p class="text-xs text-gray-500">{{ $order->updated_at->format('M j, Y g:i A') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="success-message">
    {{ session('success') }}
</div>
<script>
    setTimeout(() => {
        document.getElementById('success-message').remove();
    }, 5000);
</script>
@endif

@if(session('error'))
<div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="error-message">
    {{ session('error') }}
</div>
<script>
    setTimeout(() => {
        document.getElementById('error-message').remove();
    }, 5000);
</script>
@endif
@endsection