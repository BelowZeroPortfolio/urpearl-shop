@props([
    'categories', 
    'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'], 
    'minPrice' => null, 
    'maxPrice' => null,
    'priceRange' => ['min' => null, 'max' => null],
    'newArrivalCount' => 0,
    'bestSellerCount' => 0,
    'isNewArrival' => false,
    'isBestSeller' => false
])

@php
    // Get current query parameters
    $queryParams = request()->query();
    
    // Remove page parameter to reset pagination when filters change
    unset($queryParams['page']);
    
    // Create base URL for clearing filters
    $baseUrl = route('products.index');
    
    // Function to build URL with updated parameters
    $buildUrl = function($updates = []) use ($queryParams, $baseUrl) {
        $params = array_merge($queryParams, $updates);
        return $baseUrl . '?' . http_build_query($params);
    };
@endphp

<div class="sticky top-6 bg-white rounded-lg shadow-sm p-4 mb-6 text-sm">
    <form id="filter-form" method="GET" action="{{ route('products.index') }}">
        <!-- Hidden fields to maintain state -->
        @if(request('new_arrival'))
            <input type="hidden" name="new_arrival" value="1">
        @endif
        @if(request('best_seller'))
            <input type="hidden" name="best_seller" value="1">
        @endif
        <!-- Hidden field to track if price filter was applied -->
        <input type="hidden" name="price_filter_applied" id="price_filter_applied" value="{{ request()->has('min_price') || request()->has('max_price') ? '1' : '0' }}">

        <!-- Categories -->
        <div class="mb-4">
            <h3 class="text-xs font-medium text-gray-600 uppercase tracking-wider mb-2">Categories</h3>
            <div class="space-y-1.5">
                @foreach($categories as $category)
                    <div class="flex items-center py-0.5">
                        <input 
                            id="cat-{{ $category->id }}" 
                            type="radio" 
                            name="category" 
                            value="{{ $category->id }}"
                            class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded"
                            {{ request('category') == $category->id ? 'checked' : '' }}
                            onchange="this.form.submit()"
                        >
                        <label for="cat-{{ $category->id }}" class="ml-2 text-gray-600">
                            {{ $category->name }}
                        </label>
                    </div>
                @endforeach
                <!-- Clear category filter -->
                @if(request('category'))
                    <div class="mt-1">
                        <a href="{{ $buildUrl(['category' => null]) }}" 
                           class="text-xs text-pink-600 hover:text-pink-800 inline-block mt-1">
                            Clear category
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sizes -->
        <div class="mb-4">
            <h3 class="text-xs font-medium text-gray-600 uppercase tracking-wider mb-2">Sizes</h3>
            <div class="grid grid-cols-2 gap-2">
                @foreach($sizes as $size)
                    <div class="flex items-center py-0.5">
                        <input 
                            id="size-{{ $size }}" 
                            type="radio" 
                            name="size" 
                            value="{{ $size }}"
                            class="peer hidden"
                            {{ request('size') == $size ? 'checked' : '' }}
                            onchange="this.form.submit()"
                        >
                        <label for="size-{{ $size }}" 
                               class="w-full text-center py-2 px-3 text-sm rounded-md border border-gray-200 cursor-pointer 
                                      peer-checked:bg-pink-100 peer-checked:border-pink-400 peer-checked:text-pink-700">
                            {{ $size }}
                        </label>
                    </div>
                @endforeach
            </div>
            @if(request('size'))
                <div class="mt-2 text-right">
                    <a href="{{ $buildUrl(['size' => null]) }}" 
                           class="text-xs text-pink-600 hover:text-pink-800 inline-block mt-1">
                            Clear size
                        </a>
                </div>
            @endif
        </div>

        <!-- Special Filters -->
        <div class="mb-4 pt-4 border-t border-gray-100">
            <h3 class="text-xs font-medium text-gray-600 uppercase tracking-wider mb-2">Special</h3>
            <div class="space-y-2">
                <!-- New Arrivals -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="new_arrival" 
                            type="checkbox" 
                            name="new_arrival" 
                            value="1"
                            class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded"
                            {{ request('new_arrival') ? 'checked' : '' }}
                            onchange="this.form.submit()"
                        >
                        <label for="new_arrival" class="ml-2 text-gray-600">
                            New Arrivals
                        </label>
                    </div>
                    @if($newArrivalCount > 0)
                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                            {{ $newArrivalCount }}
                        </span>
                    @endif
                </div>
                
                <!-- Best Sellers -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="best_seller" 
                            type="checkbox" 
                            name="best_seller" 
                            value="1"
                            class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded"
                            {{ request('best_seller') ? 'checked' : '' }}
                            onchange="this.form.submit()"
                        >
                        <label for="best_seller" class="ml-2 text-gray-600">
                            Best Sellers
                        </label>
                    </div>
                    @if($bestSellerCount > 0)
                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">
                            {{ $bestSellerCount }}
                        </span>
                    @endif
                </div>
                
                <!-- Clear special filters -->
                @if($isNewArrival || $isBestSeller)
                    <div class="pt-1">
                        <a href="{{ $buildUrl(['new_arrival' => null, 'best_seller' => null]) }}" 
                               class="text-xs text-pink-600 hover:text-pink-800">
                                 Clear special filters
                             </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Price Range -->
        <div class="mb-4 pt-4 border-t border-gray-100">
            <h3 class="text-xs font-medium text-gray-600 uppercase tracking-wider mb-2">Price Range</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between space-x-4">
                    <div class="w-1/2">
                        <label for="min_price" class="block text-xs text-gray-500 mb-1">Min</label>
                        <input type="number" id="min_price" 
                               value="{{ request('min_price', $priceRange['min'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                               placeholder="Min" min="0">
                    </div>
                    <div class="w-1/2">
                        <label for="max_price" class="block text-xs text-gray-500 mb-1">Max</label>
                        <input type="number" id="max_price" 
                               value="{{ request('max_price', $priceRange['max'] ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                               placeholder="Max" min="0">
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button type="button" id="apply_price" 
                            class="flex-1 bg-pink-600 text-white py-2 px-4 rounded-md hover:bg-pink-700 transition-colors text-sm font-medium">
                        Apply Price
                    </button>
                    @if(request()->has('min_price') || request()->has('max_price'))
                        <a href="{{ $buildUrl(['min_price' => null, 'max_price' => null, 'price_filter_applied' => null]) }}" 
                           class="flex-1 bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 transition-colors text-sm font-medium text-center">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Clear All Filters -->
        @if(request()->hasAny(['category', 'size', 'min_price', 'max_price']))
            <div class="pt-4 border-t border-gray-100">
                <a href="{{ route('products.index') }}" class="w-full block text-center text-sm text-pink-600 hover:text-pink-800 font-medium">
                    Clear All Filters
                </a>
            </div>
        @endif
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filter-form');
        const priceFilterApplied = document.getElementById('price_filter_applied');
        const applyPriceBtn = document.getElementById('apply_price');
        const minPriceInput = document.getElementById('min_price');
        const maxPriceInput = document.getElementById('max_price');
        
        // Create hidden inputs for price range
        function updateHiddenPriceInputs() {
            // Remove existing hidden inputs
            const existingMin = form.querySelector('input[name="min_price"]');
            const existingMax = form.querySelector('input[name="max_price"]');
            if (existingMin) existingMin.remove();
            if (existingMax) existingMax.remove();
            
            // Only add the inputs if we're applying the price filter
            if (priceFilterApplied.value === '1') {
                const minInput = document.createElement('input');
                minInput.type = 'hidden';
                minInput.name = 'min_price';
                minInput.value = minPriceInput.value;
                form.appendChild(minInput);
                
                const maxInput = document.createElement('input');
                maxInput.type = 'hidden';
                maxInput.name = 'max_price';
                maxInput.value = maxPriceInput.value;
                form.appendChild(maxInput);
            }
        }
        
        // Function to update URL and submit form
        function submitForm() {
            const formData = new FormData(form);
            const params = new URLSearchParams();
            
            // Handle checkboxes - only include checked ones
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    params.set(checkbox.name, '1'); // Use set() instead of append() to avoid duplicates
                } else {
                    params.delete(checkbox.name); // Remove if unchecked
                }
            });
            
            // Handle other form inputs
            ['category', 'size', 'search', 'sort'].forEach(field => {
                if (formData.get(field)) {
                    params.set(field, formData.get(field));
                } else {
                    params.delete(field);
                }
            });
            
            // Handle price filter if applied
            if (priceFilterApplied.value === '1' && (minPriceInput.value || maxPriceInput.value)) {
                params.set('price_filter_applied', '1');
                if (minPriceInput.value) params.set('min_price', minPriceInput.value);
                if (maxPriceInput.value) params.set('max_price', maxPriceInput.value);
            } else {
                params.delete('price_filter_applied');
                params.delete('min_price');
                params.delete('max_price');
            }
            
            // Update URL and redirect
            const queryString = params.toString() ? `?${params.toString()}` : '';
            window.location.href = `${window.location.pathname}${queryString}`;
        }
        
        // Handle form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm();
        });
        
        // Handle checkbox and radio changes
        const filterInputs = form.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (input.type === 'checkbox') {
                    // Toggle the checked state
                    input.checked = !input.checked;
                    // Update the hidden field if it exists
                    const hiddenInput = form.querySelector(`input[type="hidden"][name="${input.name}"]`);
                    if (hiddenInput) {
                        hiddenInput.value = input.checked ? '1' : '0';
                    }
                }
                // Don't include price filter when other filters change
                priceFilterApplied.value = '0';
                submitForm();
            });
        });
        
        // Handle apply price button click
        if (applyPriceBtn) {
            applyPriceBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Only apply price filter if at least one price is set
                if (minPriceInput.value || maxPriceInput.value) {
                    priceFilterApplied.value = '1';
                    submitForm();
                }
            });
        }
        
        // Handle price range inputs
        if (minPriceInput && maxPriceInput) {
            // Update max price min value when min price changes
            minPriceInput.addEventListener('input', debounce(function() {
                if (this.value) {
                    maxPriceInput.min = this.value;
                    if (parseFloat(maxPriceInput.value) < parseFloat(this.value)) {
                        maxPriceInput.value = this.value;
                    }
                } else {
                    maxPriceInput.removeAttribute('min');
                }
            }, 300));
            
            // Update min price max value when max price changes
            maxPriceInput.addEventListener('input', debounce(function() {
                if (this.value) {
                    minPriceInput.max = this.value;
                    if (parseFloat(minPriceInput.value) > parseFloat(this.value)) {
                        minPriceInput.value = this.value;
                    }
                } else {
                    minPriceInput.removeAttribute('max');
                }
            }, 300));
        }
    });
</script>
@endpush
