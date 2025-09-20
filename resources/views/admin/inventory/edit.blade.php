@extends('layouts.admin')

@section('title', 'Edit Inventory - ' . $product->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Inventory</h1>
            <p class="text-gray-600">Update stock levels and thresholds for {{ $product->name }}</p>
        </div>
        <a href="{{ route('admin.inventory.index') }}" 
           class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
            Back to Inventory
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Product Information -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Product Information</h3>
                
                <!-- Product Image -->
                <div class="mb-4">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-48 object-cover rounded-lg">
                    @else
                        <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Product Details -->
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Product Name</label>
                        <p class="text-gray-900 font-medium">{{ $product->name }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">SKU</label>
                        <p class="text-gray-900">{{ $product->sku }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Category</label>
                        <p class="text-gray-900">{{ $product->category->name ?? 'Uncategorized' }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Price</label>
                        <p class="text-gray-900 font-semibold">₱{{ number_format($product->price, 2) }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Current Status</label>
                        @php
                            $status = $product->stock_status;
                            $statusClasses = [
                                'in_stock' => 'bg-green-100 text-green-800',
                                'low_stock' => 'bg-yellow-100 text-yellow-800',
                                'out_of_stock' => 'bg-red-100 text-red-800',
                            ];
                            $statusLabels = [
                                'in_stock' => 'In Stock',
                                'low_stock' => 'Low Stock',
                                'out_of_stock' => 'Out of Stock',
                            ];
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$status] ?? 'Unknown' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Current Inventory Stats -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Inventory</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $product->inventory->quantity ?? 0 }}</div>
                        <div class="text-sm text-blue-600">Current Stock</div>
                    </div>
                    
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ $product->inventory->low_stock_threshold ?? 0 }}</div>
                        <div class="text-sm text-yellow-600">Low Stock Alert</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Update Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Update Inventory</h3>

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</h4>
                                <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.inventory.update', $product) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Stock Quantity -->
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            Stock Quantity <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="quantity" 
                                   name="quantity" 
                                   value="{{ old('quantity', $product->inventory->quantity ?? 0) }}"
                                   min="0"
                                   class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent {{ $errors->has('quantity') ? 'border-red-300' : 'border-gray-300' }}"
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 text-sm">units</span>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">Current quantity in stock</p>
                        @error('quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Low Stock Threshold -->
                    <div>
                        <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 mb-2">
                            Low Stock Threshold <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="low_stock_threshold" 
                                   name="low_stock_threshold" 
                                   value="{{ old('low_stock_threshold', $product->inventory->low_stock_threshold ?? 10) }}"
                                   min="0"
                                   class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent {{ $errors->has('low_stock_threshold') ? 'border-red-300' : 'border-gray-300' }}"
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <span class="text-gray-500 text-sm">units</span>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">Alert when stock falls to or below this level</p>
                        @error('low_stock_threshold')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stock Status Preview -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Stock Status Preview</h4>
                        <div id="statusPreview" class="flex items-center space-x-2">
                            <span id="statusBadge" class="inline-flex px-3 py-1 text-sm font-semibold rounded-full">
                                <!-- Will be updated by JavaScript -->
                            </span>
                            <span id="statusText" class="text-sm text-gray-600">
                                <!-- Will be updated by JavaScript -->
                            </span>
                        </div>
                    </div>

                    <!-- Quick Adjustment Buttons -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Quick Adjustments</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" onclick="adjustQuantity(-10)" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm">
                                -10
                            </button>
                            <button type="button" onclick="adjustQuantity(-1)" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm">
                                -1
                            </button>
                            <button type="button" onclick="adjustQuantity(1)" class="px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm">
                                +1
                            </button>
                            <button type="button" onclick="adjustQuantity(10)" class="px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm">
                                +10
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                        <button type="submit" 
                                class="flex-1 px-6 py-3 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors font-medium">
                            Update Inventory
                        </button>
                        <a href="{{ route('admin.inventory.index') }}" 
                           class="flex-1 px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Inventory History (Future Enhancement) -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p>Inventory history tracking coming soon</p>
                    <p class="text-sm">Track all stock movements and changes</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Show success toast when redirected back from successful update
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof showToast === 'function') {
            showToast('{{ session('success') }}', 'success');
        } else {
            // Fallback if showToast is not available
            alert('{{ session('success') }}');
        }
    });
@endif

function adjustQuantity(amount) {
    const quantityInput = document.getElementById('quantity');
    const currentValue = parseInt(quantityInput.value) || 0;
    const newValue = Math.max(0, currentValue + amount);
    quantityInput.value = newValue;
    updateStatusPreview();
}

function updateStatusPreview() {
    const quantity = parseInt(document.getElementById('quantity').value) || 0;
    const threshold = parseInt(document.getElementById('low_stock_threshold').value) || 0;
    
    const statusBadge = document.getElementById('statusBadge');
    const statusText = document.getElementById('statusText');
    
    let status, statusClass, statusLabel, statusDescription;
    
    if (quantity <= 0) {
        status = 'out_of_stock';
        statusClass = 'bg-red-100 text-red-800';
        statusLabel = 'Out of Stock';
        statusDescription = 'Product is not available for purchase';
    } else if (quantity <= threshold) {
        status = 'low_stock';
        statusClass = 'bg-yellow-100 text-yellow-800';
        statusLabel = 'Low Stock';
        statusDescription = 'Stock is running low - consider restocking';
    } else {
        status = 'in_stock';
        statusClass = 'bg-green-100 text-green-800';
        statusLabel = 'In Stock';
        statusDescription = 'Product is available for purchase';
    }
    
    statusBadge.className = `inline-flex px-3 py-1 text-sm font-semibold rounded-full ${statusClass}`;
    statusBadge.textContent = statusLabel;
    statusText.textContent = statusDescription;
}

// Update preview when inputs change
document.getElementById('quantity').addEventListener('input', updateStatusPreview);
document.getElementById('low_stock_threshold').addEventListener('input', updateStatusPreview);

// Initialize preview on page load
document.addEventListener('DOMContentLoaded', updateStatusPreview);
</script>
@endpush
@endsection