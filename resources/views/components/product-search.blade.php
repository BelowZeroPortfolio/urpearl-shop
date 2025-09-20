@props(['categories' => collect(), 'currentSearch' => '', 'currentCategory' => '', 'currentSort' => 'name'])

<div class="bg-white rounded-2xl shadow-soft p-6">
    <form method="GET" action="{{ route('products.index') }}" class="search-form space-y-4 md:space-y-0 md:flex md:items-center md:space-x-4">
        <!-- Search Input -->
        <div class="flex-1">
            <div class="relative">
                <input 
                    type="text" 
                    name="search" 
                    id="search-input"
                    value="{{ $currentSearch }}"
                    placeholder="Search products..." 
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent text-sm"
                    autocomplete="off"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                @if($currentSearch)
                    <button type="button" id="clear-search" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        <!-- Category Filter -->
        <div class="md:w-40">
            <select 
                name="category" 
                class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                onchange="this.form.submit()"
            >
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $currentCategory == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Sort Options -->
        <div class="md:w-40">
            <select 
                name="sort" 
                class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                onchange="this.form.submit()"
            >
                <option value="name" {{ $currentSort == 'name' ? 'selected' : '' }}>Name A-Z</option>
                <option value="price" {{ $currentSort == 'price' ? 'selected' : '' }}>Price Low-High</option>
                <option value="rating" {{ $currentSort == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                <option value="newest" {{ $currentSort == 'newest' ? 'selected' : '' }}>Newest First</option>
            </select>
        </div>

        <!-- Hidden submit button for form submission -->
        <button type="submit" class="hidden">Search</button>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.querySelector('.search-form');
        const searchInput = document.getElementById('search-input');
        const clearSearch = document.getElementById('clear-search');
        let debounceTimer;

        // Debounce function
        function debounce(func, delay) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(func, delay);
        }

        // Handle search input with debounce
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                debounce(() => {
                    searchForm.submit();
                }, 500);
            });
        }

        // Clear search
        if (clearSearch) {
            clearSearch.addEventListener('click', function() {
                searchInput.value = '';
                searchForm.submit();
            });
        }
    });
</script>
@endpush