@extends('layouts.app')

@section('title', 'Edit Review - ' . $product->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Back to Product -->
        <div class="mb-6">
            <a href="{{ route('products.show', $product->slug) }}" 
               class="inline-flex items-center text-pink-600 hover:text-pink-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Product
            </a>
        </div>

        <!-- Product Info -->
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-8">
            <div class="flex items-center space-x-4">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" 
                         alt="{{ $product->name }}" 
                         class="w-16 h-16 object-cover rounded-xl">
                @else
                    <div class="w-16 h-16 bg-gray-200 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $product->name }}</h2>
                    <p class="text-lg font-bold text-pink-600">₱{{ number_format($product->price, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Edit Review Form -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Your Review</h1>

            <form id="rating-form" action="{{ route('ratings.update', [$product, $rating]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Rating Stars -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Your Rating *</label>
                    <div class="flex items-center space-x-1" id="star-rating">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" 
                                    class="star-btn text-3xl {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400 transition-colors focus:outline-none" 
                                    data-rating="{{ $i }}">
                                ★
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating-input" value="{{ $rating->rating }}">
                    @error('rating')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Review Text -->
                <div class="mb-6">
                    <label for="review" class="block text-sm font-medium text-gray-700 mb-3">
                        Your Review (Optional)
                    </label>
                    <textarea name="review" 
                              id="review" 
                              rows="5" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-transparent resize-none"
                              placeholder="Share your experience with this product..."
                              maxlength="1000">{{ old('review', $rating->review) }}</textarea>
                    <div class="mt-2 flex justify-between items-center">
                        @error('review')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @else
                            <p class="text-sm text-gray-500">Maximum 1000 characters</p>
                        @enderror
                        <span id="char-count" class="text-sm text-gray-400">{{ strlen($rating->review ?? '') }}/1000</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-between">
                    <div class="flex space-x-4">
                        <a href="{{ route('products.show', $product->slug) }}" 
                           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="button" 
                                id="delete-btn"
                                class="px-6 py-3 border border-red-300 text-red-600 rounded-xl hover:bg-red-50 transition-colors">
                            Delete Review
                        </button>
                    </div>
                    <button type="submit" 
                            id="submit-btn"
                            class="px-8 py-3 bg-pink-600 text-white rounded-xl hover:bg-pink-700 transition-colors">
                        Update Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Review</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete your review? This action cannot be undone.</p>
        <div class="flex justify-end space-x-4">
            <button id="cancel-delete" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
            <button id="confirm-delete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Delete
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating-input');
    const submitBtn = document.getElementById('submit-btn');
    const reviewTextarea = document.getElementById('review');
    const charCount = document.getElementById('char-count');
    const deleteBtn = document.getElementById('delete-btn');
    const deleteModal = document.getElementById('delete-modal');
    const cancelDelete = document.getElementById('cancel-delete');
    const confirmDelete = document.getElementById('confirm-delete');
    
    let selectedRating = parseInt(ratingInput.value);

    // Star rating functionality
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            selectedRating = index + 1;
            ratingInput.value = selectedRating;
            updateStars();
        });

        star.addEventListener('mouseenter', function() {
            highlightStars(index + 1);
        });
    });

    document.getElementById('star-rating').addEventListener('mouseleave', function() {
        updateStars();
    });

    function highlightStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }

    function updateStars() {
        highlightStars(selectedRating);
    }

    // Character count for review
    reviewTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length}/1000`;
        
        if (length > 1000) {
            charCount.classList.add('text-red-600');
            charCount.classList.remove('text-gray-400');
        } else {
            charCount.classList.remove('text-red-600');
            charCount.classList.add('text-gray-400');
        }
    });

    // Form submission
    document.getElementById('rating-form').addEventListener('submit', function(e) {
        if (selectedRating === 0) {
            e.preventDefault();
            alert('Please select a rating before submitting your review.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';
    });

    // Delete functionality
    deleteBtn.addEventListener('click', function() {
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
    });

    cancelDelete.addEventListener('click', function() {
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
    });

    confirmDelete.addEventListener('click', function() {
        // Create a form to delete the rating
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("ratings.destroy", [$product, $rating]) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    });

    // Close modal when clicking outside
    deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        }
    });
});
</script>
@endpush
@endsection