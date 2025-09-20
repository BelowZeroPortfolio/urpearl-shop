@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
        <p class="text-gray-600 mt-2">Review your items before checkout</p>
    </div>

    @if($stockErrors)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Stock Issues</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($stockErrors as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($cartItems->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Cart Items ({{ $cartItems->count() }})</h2>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        @foreach($cartItems as $cartItem)
                            <div class="p-6 cart-item" data-cart-item-id="{{ $cartItem->id }}">
                                <div class="flex items-center space-x-4">
                                    <!-- Product Image -->
                                    <div class="flex-shrink-0">
                                        <img src="{{ $cartItem->product->image ? asset('storage/' . $cartItem->product->image) : 'https://via.placeholder.com/80x80?text=No+Image' }}" 
                                             alt="{{ $cartItem->product->name }}" 
                                             class="w-20 h-20 rounded-xl object-cover">
                                    </div>
                                    
                                    <!-- Product Details -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <a href="{{ route('products.show', $cartItem->product) }}" class="hover:text-pink-500 transition-colors">
                                                {{ $cartItem->product->name }}
                                            </a>
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $cartItem->product->sku }}</p>
                                        <p class="text-lg font-bold text-pink-600 mt-2">₱{{ number_format($cartItem->product->price, 2) }}</p>
                                        
                                        @if($cartItem->product->inventory && $cartItem->product->inventory->quantity < $cartItem->quantity)
                                            <p class="text-sm text-red-600 mt-1">
                                                Only {{ $cartItem->product->inventory->quantity }} available
                                            </p>
                                        @endif
                                    </div>
                                    
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center border border-gray-300 rounded-lg">
                                            <button type="button" 
                                                    class="quantity-btn px-3 py-2 text-gray-600 hover:text-pink-500 transition-colors"
                                                    data-action="decrease"
                                                    data-cart-item-id="{{ $cartItem->id }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                </svg>
                                            </button>
                                            <input type="number" 
                                                   value="{{ $cartItem->quantity }}" 
                                                   min="1" 
                                                   max="{{ $cartItem->product->inventory ? $cartItem->product->inventory->quantity : 100 }}"
                                                   class="quantity-input w-16 text-center border-0 focus:ring-0 focus:outline-none"
                                                   data-cart-item-id="{{ $cartItem->id }}">
                                            <button type="button" 
                                                    class="quantity-btn px-3 py-2 text-gray-600 hover:text-pink-500 transition-colors"
                                                    data-action="increase"
                                                    data-cart-item-id="{{ $cartItem->id }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Remove Button -->
                                        <button type="button" 
                                                class="remove-btn text-red-500 hover:text-red-700 transition-colors p-2"
                                                data-cart-item-id="{{ $cartItem->id }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Item Total -->
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900 item-total">
                                            ₱{{ number_format($cartItem->quantity * $cartItem->product->price, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Clear Cart Button -->
                <div class="mt-6">
                    <button type="button" 
                            id="clear-cart-btn"
                            class="text-red-600 hover:text-red-800 transition-colors font-medium">
                        Clear Cart
                    </button>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span id="cart-subtotal">₱{{ number_format($cartTotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-bold text-gray-900">
                                <span>Total</span>
                                <span id="cart-total">₱{{ number_format($cartTotal, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        @if(count($stockErrors) > 0)
                            <button disabled class="w-full bg-gray-300 text-gray-500 py-3 px-4 rounded-xl font-semibold cursor-not-allowed">
                                Resolve Stock Issues
                            </button>
                        @else
                            <a href="{{ route('checkout.index') }}" class="w-full bg-pink-500 hover:bg-pink-600 text-white py-3 px-4 rounded-xl font-semibold transition-colors text-center block">
                                Proceed to Checkout
                            </a>
                        @endif
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('products.index') }}" class="w-full border border-gray-300 text-gray-700 hover:bg-gray-50 py-3 px-4 rounded-xl font-semibold transition-colors text-center block">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="text-center py-16">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
            <p class="text-gray-600 mb-8">Start shopping to add items to your cart</p>
            <a href="{{ route('products.index') }}" class="btn-primary">
                Browse Products
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    document.querySelectorAll('.quantity-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.dataset.action;
            const cartItemId = this.dataset.cartItemId;
            const input = document.querySelector(`input[data-cart-item-id="${cartItemId}"]`);
            let quantity = parseInt(input.value);
            
            if (action === 'increase') {
                quantity++;
            } else if (action === 'decrease' && quantity > 1) {
                quantity--;
            }
            
            input.value = quantity;
            updateCartItem(cartItemId, quantity);
        });
    });
    
    // Quantity input changes
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const cartItemId = this.dataset.cartItemId;
            const quantity = parseInt(this.value);
            
            if (quantity > 0) {
                updateCartItem(cartItemId, quantity);
            }
        });
    });
    
    // Remove buttons
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function() {
            const cartItemId = this.dataset.cartItemId;
            removeCartItem(cartItemId);
        });
    });
    
    // Clear cart button
    document.getElementById('clear-cart-btn')?.addEventListener('click', function() {
        if (confirm('Are you sure you want to clear your cart?')) {
            clearCart();
        }
    });
    
    function updateCartItem(cartItemId, quantity) {
        fetch(`/cart/${cartItemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: quantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (quantity === 0) {
                    // Remove the item from DOM
                    document.querySelector(`[data-cart-item-id="${cartItemId}"]`).remove();
                } else {
                    // Update item total
                    const cartItem = document.querySelector(`[data-cart-item-id="${cartItemId}"]`);
                    const price = parseFloat(cartItem.querySelector('.text-pink-600').textContent.replace('₱', '').replace(',', ''));
                    const itemTotal = cartItem.querySelector('.item-total');
                    itemTotal.textContent = `₱${(price * quantity).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                }
                
                // Update cart totals
                updateCartTotals(data.cart_total, data.cart_count);
                
                // Show success message
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while updating the cart', 'error');
        });
    }
    
    function removeCartItem(cartItemId) {
        fetch(`/cart/${cartItemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the item from DOM
                document.querySelector(`[data-cart-item-id="${cartItemId}"]`).remove();
                
                // Update cart totals
                updateCartTotals(data.cart_total, data.cart_count);
                
                // Check if cart is empty
                if (data.cart_count === 0) {
                    location.reload();
                }
                
                showMessage(data.message, 'success');
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while removing the item', 'error');
        });
    }
    
    function clearCart() {
        fetch('/cart/clear', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while clearing the cart', 'error');
        });
    }
    
    function updateCartTotals(total, count) {
        document.getElementById('cart-subtotal').textContent = `₱${parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        document.getElementById('cart-total').textContent = `₱${parseFloat(total).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        document.getElementById('cart-count').textContent = count;
    }
    
    function showMessage(message, type) {
        // Create a simple toast notification
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
});
</script>
@endpush
@endsection