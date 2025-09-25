@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-pink-50 to-orange-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
            <p class="text-lg text-gray-600">Thank you for your purchase, {{ $order->user->name }}!</p>
        </div>

        <!-- Order Summary Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <!-- Order Header -->
            <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 py-4">
                <div class="flex justify-between items-center text-white">
                    <div>
                        <h2 class="text-xl font-semibold">Order #{{ $order->id }}</h2>
                        <p class="text-pink-100">{{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            @if($order->status->value === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($order->status->value === 'paid') bg-green-100 text-green-800
                            @elseif($order->status->value === 'shipped') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800 @endif">
                            {{ ucfirst($order->status->value) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="p-6">
                <!-- Order Items -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Items</h3>
                    <div class="space-y-4">
                        @foreach($order->orderItems as $item)
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                            @if($item->product->image)
                            <img src="{{ Storage::url($item->product->image) }}" 
                                 alt="{{ $item->product->name }}" 
                                 class="w-16 h-16 object-cover rounded-lg">
                            @else
                            <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            @endif
                            
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $item->product->name }}</h4>
                                @if($item->product->size)
                                <p class="text-sm text-gray-500">Size: {{ $item->product->size }}</p>
                                @endif
                                <p class="text-sm text-gray-600">Quantity: {{ $item->quantity }}</p>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-sm text-gray-600">₱{{ number_format($item->price, 2) }} each</p>
                                <p class="font-semibold text-gray-900">₱{{ number_format($item->total_price, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Total -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                        <span class="text-2xl font-bold text-pink-600">₱{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Information -->
        @if($order->shipping_address)
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Shipping Address</h3>
            <div class="bg-blue-50 rounded-xl p-4">
                @if(is_array($order->shipping_address))
                    <div class="text-gray-700">
                        @if(isset($order->shipping_address['name']))
                            <p class="font-semibold">{{ $order->shipping_address['name'] }}</p>
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

        <!-- Next Steps -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">What's Next?</h3>
            <div class="space-y-3">
                @if($order->status->value === 'pending')
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center mt-0.5">
                            <div class="w-2 h-2 bg-yellow-600 rounded-full"></div>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Order Processing</p>
                            <p class="text-sm text-gray-600">We're processing your order and will send you an update once it's confirmed.</p>
                        </div>
                    </div>
                @elseif($order->status->value === 'paid')
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                            <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Payment Confirmed</p>
                            <p class="text-sm text-gray-600">Your payment has been confirmed! We're preparing your order for shipment.</p>
                        </div>
                    </div>
                @elseif($order->status->value === 'shipped')
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                            <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Order Shipped</p>
                            <p class="text-sm text-gray-600">Your order has been shipped! You should receive it soon.</p>
                        </div>
                    </div>
                @endif
                
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center mt-0.5">
                        <svg class="w-3 h-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">Email Confirmation</p>
                        <p class="text-sm text-gray-600">A confirmation email has been sent to {{ $order->user->email }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('orders.index') }}" 
               class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-xl text-base font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                View All Orders
            </a>
            
            <a href="{{ route('products.index') }}" 
               class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-pink-500 to-pink-600 text-white rounded-xl text-base font-medium hover:from-pink-600 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Continue Shopping
            </a>
        </div>
    </div>
</div>
@endsection