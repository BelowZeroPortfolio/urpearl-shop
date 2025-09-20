@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Our Products</h1>
        <p class="text-lg text-gray-600">Discover our exquisite collection of pearls and jewelry</p>
    </div>

    <!-- Search and Filter Section -->
    <x-product-search 
        :categories="$categories"
        :current-search="request('search', '')"
        :current-category="request('category', '')"
        :current-sort="request('sort', 'name')"
    />

    <div class="flex flex-col md:flex-row gap-8 mt-8 relative">
        <!-- Sidebar Filters -->
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="sticky top-16">
                <x-product-filters 
                    :categories="$categories" 
                    :sizes="['XS', 'S', 'M', 'L', 'XL', 'XXL']"
                    :minPrice="$minPrice"
                    :maxPrice="$maxPrice"
                />
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0">
            <!-- Results Info -->
            @if(request()->anyFilled(['search', 'category', 'sort', 'size', 'min_price', 'max_price']))
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm text-gray-500">Filters:</span>
                        
                        @if(request('search'))
                            <span class="inline-flex items-center bg-white px-3 py-1 rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                                Search: "{{ request('search') }}"
                                <a href="{{ route('products.index', array_merge(request()->except('search', 'page'))) }}" class="ml-1.5 text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </span>
                        @endif

                        @if(request('category'))
                            @php
                                $selectedCategory = $categories->find(request('category'));
                            @endphp
                            @if($selectedCategory)
                                <span class="inline-flex items-center bg-white px-3 py-1 rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                                    {{ $selectedCategory->name }}
                                    <a href="{{ route('products.index', array_merge(request()->except('category', 'page'))) }}" class="ml-1.5 text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                </span>
                            @endif
                        @endif

                        @if(request('size'))
                            <span class="inline-flex items-center bg-white px-3 py-1 rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                                Size: {{ request('size') }}
                                <a href="{{ route('products.index', array_merge(request()->except('size', 'page'))) }}" class="ml-1.5 text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </span>
                        @endif

                        @if(request()->has('min_price') || request()->has('max_price'))
                            <span class="inline-flex items-center bg-white px-3 py-1 rounded-full text-sm font-medium text-gray-700 border border-gray-200">
                                Price: ₱{{ request('min_price', '0') }} - ₱{{ request('max_price', '1000+') }}
                                <a href="{{ route('products.index', array_merge(request()->except(['min_price', 'max_price', 'page']))) }}" class="ml-1.5 text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            </span>
                        @endif

                        @if(request()->hasAny(['search', 'category', 'size', 'min_price', 'max_price']))
                            <a href="{{ route('products.index') }}" class="ml-auto text-sm text-pink-600 hover:text-pink-800 font-medium">
                                Clear all
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        @include('products.partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-8">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <!-- No Products Found -->
                <div class="text-center py-16 bg-white rounded-xl shadow-soft">
                    <div class="w-20 h-20 mx-auto mb-6 bg-pink-50 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No products found</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">We couldn't find any products matching your filters. Try adjusting your search criteria.</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        Clear all filters
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection