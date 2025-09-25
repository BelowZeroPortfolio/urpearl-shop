@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                    <p class="text-gray-600 mt-2">Product Details</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.products.edit', $product) }}" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Product
                </a>
                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Product Image and Basic Info -->
        <div class="lg:col-span-2">
            <div class="card p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Image -->
                    <div>
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-64 object-cover rounded-lg border border-gray-200">
                        @else
                            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                                <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <!-- Basic Product Info -->
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Basic Information</h3>
                            <dl class="space-y-2">
                                @if($product->size)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Size</dt>
                                    <dd class="text-sm text-gray-900">{{ $product->size }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                                    <dd class="text-sm text-gray-900">{{ $product->category->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Price</dt>
                                    <dd class="text-lg font-semibold text-pink-600">₱{{ number_format($product->price, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Stock Status</dt>
                                    <dd>
                                        @php
                                            $stockStatus = $product->stock_status;
                                            $statusClasses = [
                                                'in_stock' => 'bg-green-100 text-green-800',
                                                'low_stock' => 'bg-yellow-100 text-yellow-800',
                                                'out_of_stock' => 'bg-red-100 text-red-800'
                                            ];
                                            $statusLabels = [
                                                'in_stock' => 'In Stock',
                                                'low_stock' => 'Low Stock',
                                                'out_of_stock' => 'Out of Stock'
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$stockStatus] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$stockStatus] ?? 'Unknown' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Current Stock</dt>
                                    <dd class="text-sm text-gray-900">{{ $product->inventory->quantity ?? 0 }} units</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Description -->
            <div class="card p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                <div class="prose prose-sm max-w-none">
                    <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                </div>
            </div>

            <!-- Product Reviews -->
            @if($product->ratings->count() > 0)
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Customer Reviews</h3>
                        <div class="flex items-center">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                            <span class="ml-2 text-sm text-gray-600">{{ number_format($product->average_rating, 1) }} ({{ $product->ratings->count() }} reviews)</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($product->ratings->take(5) as $rating)
                            <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900">{{ $rating->user->name }}</span>
                                        <div class="flex items-center ml-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-3 h-3 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $rating->created_at->format('M j, Y') }}</span>
                                </div>
                                @if($rating->review)
                                    <p class="text-sm text-gray-700">{{ $rating->review }}</p>
                                @endif
                            </div>
                        @endforeach

                        @if($product->ratings->count() > 5)
                            <div class="text-center pt-4">
                                <span class="text-sm text-gray-500">And {{ $product->ratings->count() - 5 }} more reviews...</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Inventory Information -->
            @if($product->inventory)
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Inventory Details</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Current Stock</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ $product->inventory->quantity }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Low Stock Threshold</dt>
                            <dd class="text-sm text-gray-900">{{ $product->inventory->low_stock_threshold ?? 'Not set' }}</dd>
                        </div>
                        @if($product->inventory->isLowStock())
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium text-yellow-800">Low Stock Alert</h4>
                                        <p class="text-sm text-yellow-700">This product is running low on stock.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            <!-- Product Statistics -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Reviews</dt>
                        <dd class="text-sm text-gray-900">{{ $product->ratings->count() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Average Rating</dt>
                        <dd class="text-sm text-gray-900">{{ number_format($product->average_rating, 1) }}/5.0</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created</dt>
                        <dd class="text-sm text-gray-900">{{ $product->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="text-sm text-gray-900">{{ $product->updated_at->format('M j, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn-primary w-full text-center">
                        Edit Product
                    </a>
                    @if($product->inventory)
                        <a href="{{ route('admin.inventory.index') }}?product={{ $product->id }}" class="btn-secondary w-full text-center">
                            Manage Inventory
                        </a>
                    @endif
                    <button onclick="window.print()" class="btn-secondary w-full">
                        Print Details
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection