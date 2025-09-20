{{-- Sample form demonstrating validation components --}}
<form data-validate data-validate-on-blur="true" data-validate-on-input="false" class="space-y-6">
    @csrf
    
    {{-- Display validation errors --}}
    <x-validation-errors />
    
    {{-- Success/Error alerts --}}
    @if(session('success'))
        <x-alert type="success" dismissible>
            {{ session('success') }}
        </x-alert>
    @endif
    
    @if(session('error'))
        <x-alert type="error" dismissible>
            {{ session('error') }}
        </x-alert>
    @endif
    
    {{-- Form inputs with validation --}}
    <x-form-input 
        name="name" 
        label="Product Name" 
        required 
        data-rules="required|min:3|max:255"
        help="Enter a descriptive product name"
    />
    
    <x-form-input 
        name="price" 
        type="number" 
        label="Price" 
        required 
        data-rules="required|numeric|min:0.01"
        help="Price in Philippine Pesos (₱)"
        step="0.01"
    />
    
    <x-form-input 
        name="description" 
        type="textarea" 
        label="Description" 
        required 
        data-rules="required|min:10|max:1000"
        help="Provide a detailed product description"
        rows="4"
    />
    
    <x-form-input 
        name="category_id" 
        type="select" 
        label="Category" 
        required 
        data-rules="required|exists:categories,id"
    >
        <option value="">Select a category</option>
        <option value="1">Pearls</option>
        <option value="2">Jewelry</option>
        <option value="3">Accessories</option>
    </x-form-input>
    
    {{-- Submit button --}}
    <div class="flex justify-end">
        <button type="submit" class="btn-primary">
            Save Product
        </button>
    </div>
</form>