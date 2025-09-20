<div class="product-card group hover:shadow-lg transition-shadow duration-200 flex flex-col h-full bg-white rounded-lg overflow-hidden border border-gray-100">
    <a href="{{ route('products.show', $product->slug) }}" class="block flex-1 flex flex-col p-2 group relative">
        <!-- Product Badges -->
        <div class="absolute top-2 left-2 z-10 flex items-start gap-1.5">
            @if($product->is_new_arrival)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-white text-pink-600 shadow-sm transform transition-all duration-200 group-hover:scale-105 group-hover:bg-pink-600 group-hover:text-white">
                    <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.215.33-.396.68-.537 1.01a10.08 10.08 0 00-4.24.98 1 1 0 10.342 1.952 8.046 8.046 0 013.41-.78c.28.002.56.023.837.067.22.035.44.08.66.133a1 1 0 10.47-1.937c-.25-.06-.5-.1-.75-.14zM8.05 6.05a7.007 7.007 0 00-4.66 4.66 1 1 0 01-1.28 1.28 9.02 9.02 0 011.74-4.98 9.034 9.034 0 014.98-1.74 1 1 0 01.22 1.99c-.11.02-.22.03-.33.05zM6 13a1 1 0 100-2 1 1 0 000 2zm13-1a1 1 0 01-1.99 0c0-.11.01-.22.03-.33a1 1 0 011.96.33z" clip-rule="evenodd" />
                    </svg>
                    New
                </span>
            @endif
            @if($product->is_best_seller)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-white text-amber-600 shadow-sm transform transition-all duration-200 group-hover:scale-105 group-hover:bg-amber-500 group-hover:text-white">
                    <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    Bestseller
                </span>
            @endif
        </div>
        
        <!-- Product Image -->
        <!-- Size Badge -->
        @if($product->size)
            <div class="absolute top-2 right-2 z-10">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 bg-opacity-70 text-white text-xs font-medium group-hover:bg-opacity-90 transition-all">
                    {{ $product->size }}
                </span>
            </div>
        @endif
        
        <!-- Product Image -->
        <div class="relative aspect-square rounded overflow-hidden mb-2 bg-gradient-to-br from-gray-50 to-gray-100">
            @if($product->image)
                @php
                    // Check if the image is a full URL or a local path
                    if (filter_var($product->image, FILTER_VALIDATE_URL)) {
                        // It's a full URL, but we'll use a placeholder to avoid hotlinking issues
                        $imageUrl = 'https://placehold.co/600x600/f3f4f6/9ca3af?text=' . urlencode($product->name);
                    } else {
                        // It's a local path, build the full URL
                        $filename = basename($product->image);
                        $imageUrl = asset('storage/products/' . $filename);
                    }
                @endphp
                <div class="relative w-full h-full overflow-hidden bg-gray-100">
                    <img 
                        src="{{ $imageUrl }}" 
                        alt="{{ $product->name }}"
                        class="relative z-10 w-full h-full object-cover group-hover:scale-110 transition-all duration-500"
                        loading="lazy"
                        onerror="this.onerror=null; this.src='https://placehold.co/600x600/f3f4f6/9ca3af?text=Image+Not+Available'"
                    >
                    <!-- Single overlay with both gradient and hover effect -->
                    <div class="absolute inset-0 z-20 bg-gradient-to-t from-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <!-- Quick action button -->
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <div class="bg-white bg-opacity-90 rounded-full p-3 transform scale-75 group-hover:scale-100 transition-all duration-300">
                                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400 group-hover:text-pink-400 transition-colors">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            @endif
            
            <!-- Quick action overlay -->
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                <div class="bg-white bg-opacity-90 rounded-full p-3 transform scale-75 group-hover:scale-100 transition-all duration-300">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="space-y-1 flex-1 flex flex-col">
            <!-- Category -->
            @if($product->category)
                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-medium truncate">{{ $product->category->name }}</p>
            @endif
            
            <!-- Product Name -->
            <h3 class="text-sm font-semibold text-gray-900 group-hover:text-pink-600 transition-colors line-clamp-2 leading-tight">
                {{ $product->name }}
            </h3>

            <!-- Rating -->
            @if($product->ratings_count > 0)
                <div class="flex items-center space-x-1 mt-0.5">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-3 h-3 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endfor
                    </div>
                    <span class="text-[10px] text-gray-400">({{ $product->ratings_count }})</span>
                </div>
            @endif

            <!-- Price and Stock Status -->
            <div class="mt-1">
                <div class="flex items-baseline justify-between">
                    <span class="text-sm font-bold text-pink-600">₱{{ number_format($product->price, 2) }}</span>
                    @if($product->stock_status === 'in_stock')
                        <span class="text-[10px] text-green-600 font-medium">In Stock</span>
                    @elseif($product->stock_status === 'low_stock')
                        <span class="text-[10px] text-yellow-600 font-medium">Low Stock</span>
                    @else
                        <span class="text-[10px] text-red-600 font-medium">Out of Stock</span>
                    @endif
                </div>
            </div>

            <!-- Size Selector (Removed since we show size as a badge) -->
            <div class="mt-1 pt-1 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-medium text-gray-400 tracking-wider">QUANTITY</span>
                    <span id="selected-size-{{ $product->id }}" class="text-xs font-medium text-gray-700">{{ $product->size_name ?? 'Select' }}</span>
                </div>
                <div class="grid grid-cols-6 gap-1">
                    @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $size)
                        <button 
                            type="button"
                            data-product-id="{{ $product->id }}"
                            data-size="{{ $size }}"
                            class="size-option w-full aspect-square flex items-center justify-center text-[10px] font-medium rounded-sm border transition-colors {{ $product->size === $size ? 'bg-pink-600 border-pink-600 text-white' : 'border-gray-200 text-gray-600 hover:border-pink-300' }}"
                        >
                            {{ $size }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </a>

    <!-- Action Buttons -->
    <div class="p-2 bg-gray-50">
        <!-- Add to Cart Button -->
        @if($product->isInStock())
            <button 
                class="w-full bg-pink-600 hover:bg-pink-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors flex items-center justify-center"
                onclick="addToCart({{ $product->id }})"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9m-9 0h9"></path>
                </svg>
                Add to Cart
            </button>
        @else
            <button class="w-full bg-gray-200 text-gray-500 py-2 px-4 rounded-lg text-sm font-medium cursor-not-allowed" disabled>
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                </svg>
                Out of Stock
            </button>
        @endif
        
        <!-- View Details Button -->
        <a 
            href="{{ route('products.show', $product->slug) }}" 
            class="block w-full border border-gray-300 text-gray-700 hover:bg-gray-50 py-2 px-4 rounded-lg text-sm font-medium transition-colors text-center flex items-center justify-center"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            View Details
        </a>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle size selection
        document.querySelectorAll('.size-option').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const size = this.dataset.size;
                const sizeName = this.textContent.trim();
                
                // Update UI
                document.querySelectorAll(`.size-option[data-product-id="${productId}"]`).forEach(btn => {
                    btn.classList.remove('bg-pink-100', 'border-pink-500', 'text-pink-700');
                    btn.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });
                
                this.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                this.classList.add('bg-pink-100', 'border-pink-500', 'text-pink-700');
                
                // Update selected size display
                const sizeDisplay = document.getElementById(`selected-size-${productId}`);
                if (sizeDisplay) {
                    sizeDisplay.textContent = {
                        'XS': 'Extra Small',
                        'S': 'Small',
                        'M': 'Medium',
                        'L': 'Large',
                        'XL': 'Extra Large',
                        'XXL': 'Double Extra Large'
                    }[size] || size;
                }
                
                // Here you can add code to update the cart with the selected size
                console.log(`Product ${productId} size selected: ${size}`);
            });
        });
    });
</script>
@endpush