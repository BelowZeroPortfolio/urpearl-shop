@extends('layouts.admin')

@section('title', 'Inventory Management')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Inventory Management</h1>
                <p class="text-gray-600">Monitor and manage product stock levels</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 mt-4 sm:mt-0">
                <a href="{{ route('admin.inventory.export') }}"
                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-center">
                    Export CSV
                </a>
                <button onclick="openBulkUpdateModal()"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    Bulk Update
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Products</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_products'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-xl">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">In Stock</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['in_stock_count'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-xl">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Low Stock</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['low_stock_count'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-xl">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['out_of_stock_count'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <form method="GET" action="{{ route('admin.inventory.index') }}"
                class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Product name or SKU..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="category" name="category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="stock_status" class="block text-sm font-medium text-gray-700 mb-2">Stock Status</label>
                    <select id="stock_status" name="stock_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock
                        </option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock
                        </option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out
                            of Stock</option>
                    </select>
                </div>

                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sort" name="sort"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        <option value="">Name (A-Z)</option>
                        <option value="quantity_asc" {{ request('sort') == 'quantity_asc' ? 'selected' : '' }}>Stock (Low
                            to High)</option>
                        <option value="quantity_desc" {{ request('sort') == 'quantity_desc' ? 'selected' : '' }}>Stock
                            (High to Low)</option>
                    </select>
                </div>

                <div class="md:col-span-4 flex gap-3">
                    <button type="submit"
                        class="px-6 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors">
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.inventory.index') }}"
                        class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Current Stock</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Threshold</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                alt="{{ $product->name }}"
                                                class="w-12 h-12 rounded-lg object-cover mr-4">
                                        @else
                                            <div
                                                class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center mr-4">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">SKU: {{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $product->inventory->quantity ?? 0 }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $product->inventory->low_stock_threshold ?? 0 }}
                                </td>
                                <td class="px-6 py-4">
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
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $statusLabels[$status] ?? 'Unknown' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.inventory.edit', $product) }}"
                                        class="text-pink-600 hover:text-pink-800 transition-colors">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No products found</p>
                                        <p class="text-sm">Try adjusting your filters or add some products first.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($products->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Update Modal -->
    <div id="bulkUpdateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Bulk Update Inventory</h3>
                    <p class="text-sm text-gray-600 mt-1">Update multiple products at once</p>
                </div>
                <div class="p-6">
                    <form id="bulkUpdateForm">
                        <div id="bulkUpdateItems" class="space-y-4">
                            <!-- Dynamic content will be added here -->
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeBulkUpdateModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors">
                                Update All
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Adjustment Modal -->
    <div id="stockAdjustmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Adjust Stock</h3>
                    <p class="text-sm text-gray-600 mt-1">Quick stock adjustment</p>
                </div>
                <div class="p-6">
                    <form id="stockAdjustmentForm">
                        <input type="hidden" id="adjustProductId" name="product_id">
                        <input type="hidden" id="adjustAction" name="action">

                        <div class="mb-4">
                            <label for="adjustQuantity"
                                class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                            <input type="number" id="adjustQuantity" name="quantity" min="1" value="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeStockAdjustmentModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors">
                                Adjust Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Bulk Update Modal Functions
            function openBulkUpdateModal() {
                document.getElementById('bulkUpdateModal').classList.remove('hidden');
                loadBulkUpdateItems();
            }

            function closeBulkUpdateModal() {
                document.getElementById('bulkUpdateModal').classList.add('hidden');
            }

            function loadBulkUpdateItems() {
                const container = document.getElementById('bulkUpdateItems');
                container.innerHTML = '<p class="text-center text-gray-500">Loading products...</p>';

                // For now, we'll add a simple form to add products manually
                // In a real implementation, you might want to load all products or selected ones
                container.innerHTML = `
        <div class="text-center">
            <p class="text-gray-600 mb-4">Select products to update in bulk:</p>
            <button type="button" onclick="addBulkUpdateItem()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                Add Product
            </button>
        </div>
        <div id="bulkUpdateList" class="mt-4 space-y-3"></div>
    `;
            }

            function addBulkUpdateItem() {
                const list = document.getElementById('bulkUpdateList');
                const index = list.children.length;

                const item = document.createElement('div');
                item.className = 'border border-gray-200 rounded-lg p-4';
                item.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product ID</label>
                <input type="number" name="updates[${index}][product_id]" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input type="number" name="updates[${index}][quantity]" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Threshold</label>
                <input type="number" name="updates[${index}][low_stock_threshold]" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
            </div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="mt-2 text-red-600 hover:text-red-800 text-sm">Remove</button>
    `;

                list.appendChild(item);
            }

            // Stock Adjustment Modal Functions
            function adjustStock(productId, action) {
                document.getElementById('adjustProductId').value = productId;
                document.getElementById('adjustAction').value = action;
                document.getElementById('stockAdjustmentModal').classList.remove('hidden');
            }

            function closeStockAdjustmentModal() {
                document.getElementById('stockAdjustmentModal').classList.add('hidden');
            }

            // Form Submissions
            document.getElementById('bulkUpdateForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const updates = [];

                // Convert FormData to the expected format
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }

                // Extract updates array
                const updatesData = {};
                Object.keys(data).forEach(key => {
                    if (key.startsWith('updates[')) {
                        const match = key.match(/updates\[(\d+)\]\[(\w+)\]/);
                        if (match) {
                            const index = match[1];
                            const field = match[2];
                            if (!updatesData[index]) updatesData[index] = {};
                            updatesData[index][field] = data[key];
                        }
                    }
                });

                const updatesArray = Object.values(updatesData);

                fetch(@json(route('admin.inventory.bulk-update')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token())
                        },
                        body: JSON.stringify({
                            updates: updatesArray
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating inventory.');
                    });
            });

            document.getElementById('stockAdjustmentForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const productId = formData.get('product_id');
                const action = formData.get('action');
                const quantity = formData.get('quantity');

                fetch(`/admin/inventory/${productId}/adjust`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token())
                        },
                        body: JSON.stringify({
                            action: action,
                            quantity: parseInt(quantity)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while adjusting stock.');
                    });
            });

        </script>
    @endpush
@endsection
