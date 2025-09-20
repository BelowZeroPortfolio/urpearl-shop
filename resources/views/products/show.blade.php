@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-pink-600 transition-colors">
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('products.index') }}" class="ml-1 text-gray-500 hover:text-pink-600 transition-colors md:ml-2">
                        Products
                    </a>
                </div>
            </li>
            @if($product->category)
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <a href="{{ route('products.index', ['category' => $product->category->id]) }}" class="ml-1 text-gray-500 hover:text-pink-600 transition-colors md:ml-2">
                        {{ $product->category->name }}
                    </a>
                </div>
            </li>
            @endif
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-gray-900 font-medium md:ml-2">{{ $product->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Product Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
        <!-- Product Image -->
        <div class="space-y-4">
            <div class="aspect-square rounded-2xl overflow-hidden bg-gray-100">
                @if($product->image)
                    <img 
                        src="{{ asset('storage/' . $product->image) }}" 
                        alt="{{ $product->name }}"
                        class="w-full h-full object-cover"
                    >
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        <!-- Product Information -->
        <div class="space-y-6">
            <!-- Product Title and Category -->
            <div>
                @if($product->category)
                    <p class="text-sm text-pink-600 font-medium mb-2">{{ $product->category->name }}</p>
                @endif
                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>
                
                <!-- Rating -->
                @if($product->ratings->count() > 0)
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm text-gray-600">
                            {{ number_format($product->average_rating, 1) }} ({{ $product->ratings->count() }} {{ Str::plural('review', $product->ratings->count()) }})
                        </span>
                    </div>
                @endif
            </div>

            <!-- Price and Stock -->
            <div class="border-t border-b border-gray-200 py-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-3xl font-bold text-pink-600">₱{{ number_format($product->price, 2) }}</span>
                    
                    @if($product->stock_status === 'in_stock')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            In Stock
                        </span>
                    @elseif($product->stock_status === 'low_stock')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Low Stock
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            Out of Stock
                        </span>
                    @endif
                </div>

                <!-- SKU -->
                <p class="text-sm text-gray-500">SKU: {{ $product->sku }}</p>
            </div>

            <!-- Add to Cart Section -->
            <div class="space-y-4">
                @if($product->isInStock())
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center border border-gray-200 rounded-xl">
                            <button type="button" class="p-3 hover:bg-gray-50 rounded-l-xl" onclick="decrementQuantity()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <input 
                                type="number" 
                                id="quantity" 
                                value="1" 
                                min="1" 
                                max="{{ $product->inventory->quantity ?? 1 }}"
                                class="w-16 text-center border-0 focus:ring-0"
                            >
                            <button type="button" class="p-3 hover:bg-gray-50 rounded-r-xl" onclick="incrementQuantity()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <button 
                            class="flex-1 btn-primary"
                            onclick="addToCartWithQuantity({{ $product->id }})"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0h9"></path>
                            </svg>
                            Add to Cart
                        </button>
                    </div>
                @else
                    <button class="w-full btn-secondary opacity-50 cursor-not-allowed" disabled>
                        Out of Stock
                    </button>
                @endif

                @if($product->inventory && $product->inventory->quantity <= 5 && $product->inventory->quantity > 0)
                    <p class="text-sm text-yellow-600">
                        Only {{ $product->inventory->quantity }} left in stock!
                    </p>
                @endif
            </div>

            <!-- Product Description -->
            @if($product->description)
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                    <div class="prose prose-sm max-w-none text-gray-600">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Reviews Section -->
    <x-product-ratings :product="$product" />

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="border-t border-gray-200 pt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Products</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    @include('products.partials.product-card', ['product' => $relatedProduct])
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
function incrementQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    const min = parseInt(input.getAttribute('min'));
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}

function addToCartWithQuantity(productId) {
    const quantity = document.getElementById('quantity').value;
    addToCart(productId, quantity);
    
    // Reset quantity to 1 after successful add
    setTimeout(() => {
        document.getElementById('quantity').value = 1;
    }, 1000);
}
</script>
@endsection