@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create New Product</h1>
                <p class="text-gray-600 mt-2">Add a new product to your catalog</p>
            </div>
        </div>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Product Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Product Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 @error('name') border-red-500 @enderror"
                       placeholder="Enter product name">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Product Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 @error('description') border-red-500 @enderror"
                          placeholder="Enter product description">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Price -->
                <div class="w-full md:w-1/2">
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                        Price (₱) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">₱</span>
                        <input type="number" 
                               id="price" 
                               name="price" 
                               value="{{ old('price') }}"
                               step="0.01"
                               min="0"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 @error('price') border-red-500 @enderror"
                               placeholder="0.00" required>
                    </div>
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Size -->
                <div>
                    <label for="size" class="block text-sm font-medium text-gray-700 mb-2">
                        Size <span class="text-red-500">*</span>
                    </label>
                    <select id="size" 
                            name="size" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 @error('size') border-red-500 @enderror">
                        <option value="" disabled selected>Select size</option>
                        <option value="XS" {{ old('size') == 'XS' ? 'selected' : '' }}>XS - Extra Small</option>
                        <option value="S" {{ old('size') == 'S' ? 'selected' : '' }}>S - Small</option>
                        <option value="M" {{ old('size') == 'M' ? 'selected' : '' }}>M - Medium (Default)</option>
                        <option value="L" {{ old('size') == 'L' ? 'selected' : '' }}>L - Large</option>
                        <option value="XL" {{ old('size') == 'XL' ? 'selected' : '' }}>XL - Extra Large</option>
                        <option value="XXL" {{ old('size') == 'XXL' ? 'selected' : '' }}>XXL - Double Extra Large</option>
                    </select>
                    @error('size')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Category -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2">
                    @php
                        $isNewCategory = old('category_id') === 'new';
                    @endphp
                    <select id="category_id" 
                            name="category_id" 
                            onchange="toggleNewCategoryField(this)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 @error('category_id') border-red-500 @enderror">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                        <option value="new" {{ $isNewCategory ? 'selected' : '' }}>Other (Specify below)</option>
                    </select>
                    
                    <!-- New Category Input (initially hidden) -->
                    <div id="new-category-container" class="{{ $isNewCategory ? '' : 'hidden' }} mt-2">
                        <label for="new_category" class="block text-sm font-medium text-gray-700 mb-1">
                            New Category Name <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-2">
                            <input type="text" 
                                   id="new_category" 
                                   name="new_category" 
                                   value="{{ old('new_category') }}"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-pink-500 focus:border-pink-500 @error('new_category') border-red-500 @enderror"
                                   placeholder="Enter new category name">
                        </div>
                        @error('new_category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Product Tags -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Product Tags
                </label>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="hidden" name="is_new_arrival" value="0">
                        <input type="checkbox" 
                               id="is_new_arrival" 
                               name="is_new_arrival" 
                               value="1"
                               class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded"
                               {{ old('is_new_arrival') ? 'checked' : '' }}>
                        <label for="is_new_arrival" class="ml-2 block text-sm text-gray-700">
                            Mark as New Arrival
                            <span class="text-xs text-gray-500">(Will show a "New Arrival" badge on the product)</span>
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="hidden" name="is_best_seller" value="0">
                        <input type="checkbox" 
                               id="is_best_seller" 
                               name="is_best_seller" 
                               value="1"
                               class="h-4 w-4 text-amber-500 focus:ring-amber-500 border-gray-300 rounded"
                               {{ old('is_best_seller') ? 'checked' : '' }}>
                        <label for="is_best_seller" class="ml-2 block text-sm text-gray-700">
                            Mark as Best Seller
                            <span class="text-xs text-gray-500">(Will show a "Best Seller" badge on the product)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Product Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Product Image <span class="text-red-500">*</span>
                </label>
                <div class="relative group">
                    <!-- Drop Zone -->
                    <div id="drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 hover:border-pink-400 transition-colors duration-300 cursor-pointer bg-gray-50 hover:bg-gray-100">
                        <input type="file" 
                               id="image" 
                               name="image" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                               accept="image/*" 
                               onchange="previewImage(this)">
                        <div class="drop-zone-content space-y-3 text-center">
                            <div class="mx-auto w-12 h-12 rounded-full bg-pink-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium text-pink-600">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    PNG, JPG, GIF (Max. 5MB)
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Image Preview -->
                    <div id="image-preview" class="mt-4 hidden relative group">
                        <div class="relative">
                            <img id="preview-img" class="h-64 w-full object-cover rounded-lg border border-gray-200 shadow-sm" alt="Preview">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <button type="button" onclick="removeImage()" class="bg-white rounded-full p-2 text-red-600 shadow-lg hover:bg-red-50 transition-colors">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                @error('image')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.products.index') }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Product
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Function to toggle the new category input field
function toggleNewCategoryField(selectElement) {
    const newCategoryContainer = document.getElementById('new-category-container');
    const newCategoryInput = document.getElementById('new_category');
    
    if (selectElement.value === 'new') {
        newCategoryContainer.classList.remove('hidden');
        newCategoryInput.required = true;
        // If this is a new category, clear any existing category selection
        Array.from(selectElement.options).forEach(option => {
            if (option.value !== 'new' && option.value !== '') {
                option.selected = false;
            }
        });
    } else {
        newCategoryContainer.classList.add('hidden');
        newCategoryInput.required = false;
        // Clear the new category input when selecting an existing category
        if (newCategoryInput) {
            newCategoryInput.value = '';
        }
    }
}

// DOM Elements
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('image');
const preview = document.getElementById('image-preview');
const previewImg = document.getElementById('preview-img');

// Prevent default drag behaviors
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

// Highlight drop zone when item is dragged over it
['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

// Handle dropped files
dropZone.addEventListener('drop', handleDrop, false);

// Handle click on drop zone - only if the click is on the drop zone itself, not the file input
dropZone.addEventListener('click', (e) => {
    if (e.target === dropZone || e.target.closest('.drop-zone-content')) {
        fileInput.click();
    }
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

function highlight() {
    dropZone.classList.add('border-pink-500', 'bg-pink-50');
    dropZone.classList.remove('border-gray-300', 'bg-gray-50');
}

function unhighlight() {
    dropZone.classList.remove('border-pink-500', 'bg-pink-50');
    dropZone.classList.add('border-gray-300', 'bg-gray-50');
}

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length) {
        fileInput.files = files;
        previewImage(fileInput);
    }
}

function previewImage(input) {
    const file = input.files[0];
    
    if (file) {
        // Check file size (20MB max)
        const maxSize = 20 * 1024 * 1024; // 20MB in bytes
        if (file.size > maxSize) {
            alert('File size should not exceed 20MB');
            input.value = ''; // Clear the file input
            return;
        }
        
        // Check file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Only JPG, PNG, GIF, and WebP files are allowed');
            input.value = ''; // Clear the file input
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
            
            // Scroll to preview
            preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
}

function removeImage() {
    fileInput.value = '';
    preview.classList.add('hidden');
}

// Handle form submission to ensure an image is selected
const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Please select an image for the product');
            dropZone.scrollIntoView({ behavior: 'smooth', block: 'center' });
            dropZone.classList.add('border-red-500');
            setTimeout(() => dropZone.classList.remove('border-red-500'), 2000);
        }
    });
}

// Initialize the form on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize category field state
    const categorySelect = document.getElementById('category_id');
    if (categorySelect) {
        toggleNewCategoryField(categorySelect);
    }
    const skuField = document.getElementById('sku');
    if (!skuField.value) {
        const nameField = document.getElementById('name');
        const sku = nameField.value
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '')
            .substring(0, 10);
        skuField.value = sku;
    }
});

// Auto-generate SKU from product name
document.getElementById('name').addEventListener('input', function() {
    const skuField = document.getElementById('sku');
    if (!skuField.value) {
        const sku = this.value
            .toUpperCase()
            .replace(/[^A-Z0-9]/g, '')
            .substring(0, 10);
        skuField.value = sku;
    }
});
</script>
@endsection