@props(['product'])

<div class="border-t border-gray-200 pt-12" id="reviews-section">
    <!-- Reviews Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Customer Reviews</h2>
            @if($product->ratings->count() > 0)
                <div class="flex items-center space-x-4 mt-2">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-lg font-medium text-gray-900">{{ number_format($product->average_rating, 1) }}</span>
                    <span class="text-gray-600">Based on {{ $product->ratings->count() }} {{ Str::plural('review', $product->ratings->count()) }}</span>
                </div>
            @else
                <p class="text-gray-600 mt-2">No reviews yet. Be the first to review this product!</p>
            @endif
        </div>
        
        <!-- Write Review Button -->
        @auth
            <div id="review-button-container">
                <button id="check-review-eligibility" 
                        class="btn-primary"
                        data-product-id="{{ $product->id }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Write a Review
                </button>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                Login to Review
            </a>
        @endauth
    </div>

    <!-- Rating Distribution (if there are ratings) -->
    @if($product->ratings->count() > 0)
        <div class="bg-gray-50 rounded-2xl p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Average Rating Display -->
                <div class="text-center">
                    <div class="text-4xl font-bold text-gray-900 mb-2">{{ number_format($product->average_rating, 1) }}</div>
                    <div class="flex items-center justify-center mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-6 h-6 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600">{{ $product->ratings->count() }} {{ Str::plural('review', $product->ratings->count()) }}</p>
                </div>

                <!-- Rating Breakdown -->
                <div class="space-y-2">
                    @php
                        $ratingCounts = $product->ratings->groupBy('rating')->map->count();
                        $totalRatings = $product->ratings->count();
                    @endphp
                    @for($i = 5; $i >= 1; $i--)
                        @php
                            $count = $ratingCounts->get($i, 0);
                            $percentage = $totalRatings > 0 ? ($count / $totalRatings) * 100 : 0;
                        @endphp
                        <div class="flex items-center space-x-3">
                            <span class="text-sm font-medium text-gray-700 w-8">{{ $i }} ★</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600 w-8">{{ $count }}</span>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    @endif

    <!-- Reviews List -->
    <div id="reviews-container">
        @if($product->ratings->count() > 0)
            <div class="space-y-6" id="reviews-list">
                @foreach($product->ratings->take(5) as $rating)
                    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    @if($rating->user->avatar)
                                        <img src="{{ $rating->user->avatar }}" alt="{{ $rating->user->name }}" class="w-12 h-12 rounded-full">
                                    @else
                                        <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg font-medium text-pink-600">
                                                {{ substr($rating->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <h4 class="font-semibold text-gray-900">{{ $rating->user->name }}</h4>
                                        @if($rating->is_verified_purchase)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Verified Purchase
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 mb-3">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                        <time class="text-sm text-gray-500">{{ $rating->created_at->format('M j, Y') }}</time>
                                    </div>
                                    
                                    @if($rating->review)
                                        <p class="text-gray-700 leading-relaxed">{{ $rating->review }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Edit/Delete buttons for own reviews -->
                            @auth
                                @if(auth()->id() === $rating->user_id)
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('ratings.edit', [$product, $rating]) }}" 
                                           class="text-gray-400 hover:text-pink-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </a>
                                        <button onclick="deleteReview({{ $rating->id }})" 
                                                class="text-gray-400 hover:text-red-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Load More Reviews -->
            @if($product->ratings->count() > 5)
                <div class="text-center mt-8">
                    <button id="load-more-reviews" 
                            class="btn-secondary"
                            data-product-id="{{ $product->id }}"
                            data-page="2">
                        Load More Reviews
                    </button>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No reviews yet</h3>
                <p class="text-gray-600">Be the first to share your thoughts about this product.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check review eligibility
    const checkEligibilityBtn = document.getElementById('check-review-eligibility');
    if (checkEligibilityBtn) {
        checkEligibilityBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            checkReviewEligibility(productId);
        });
    }

    // Load more reviews
    const loadMoreBtn = document.getElementById('load-more-reviews');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const page = parseInt(this.dataset.page);
            loadMoreReviews(productId, page);
        });
    }
});

function checkReviewEligibility(productId) {
    fetch(`/products/${productId}/can-review`)
        .then(response => response.json())
        .then(data => {
            if (data.can_review) {
                window.location.href = `/products/${productId}/ratings/create`;
            } else {
                let message = '';
                switch (data.reason) {
                    case 'not_authenticated':
                        message = 'Please log in to write a review.';
                        break;
                    case 'not_purchased':
                        message = 'You can only review products you have purchased.';
                        break;
                    case 'already_reviewed':
                        message = 'You have already reviewed this product. You can edit your existing review.';
                        break;
                    default:
                        message = 'Unable to write a review at this time.';
                }
                showMessage(message, 'error');
            }
        })
        .catch(error => {
            console.error('Error checking review eligibility:', error);
            showMessage('An error occurred. Please try again.', 'error');
        });
}

function loadMoreReviews(productId, page) {
    const loadMoreBtn = document.getElementById('load-more-reviews');
    const originalText = loadMoreBtn.textContent;
    loadMoreBtn.textContent = 'Loading...';
    loadMoreBtn.disabled = true;

    fetch(`/products/${productId}/ratings/data?page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.ratings.length > 0) {
                const reviewsList = document.getElementById('reviews-list');
                
                data.ratings.forEach(rating => {
                    const reviewElement = createReviewElement(rating);
                    reviewsList.appendChild(reviewElement);
                });

                // Update page number
                loadMoreBtn.dataset.page = page + 1;
                
                // Hide button if no more reviews
                if (data.pagination.current_page >= data.pagination.last_page) {
                    loadMoreBtn.style.display = 'none';
                }
            } else {
                loadMoreBtn.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading more reviews:', error);
            showMessage('Failed to load more reviews.', 'error');
        })
        .finally(() => {
            loadMoreBtn.textContent = originalText;
            loadMoreBtn.disabled = false;
        });
}

function createReviewElement(rating) {
    const reviewDiv = document.createElement('div');
    reviewDiv.className = 'bg-white rounded-2xl shadow-sm p-6 border border-gray-100';
    
    const starsHtml = Array.from({length: 5}, (_, i) => {
        const filled = i < rating.rating ? 'text-yellow-400' : 'text-gray-300';
        return `<svg class="w-4 h-4 ${filled}" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
        </svg>`;
    }).join('');

    const avatarHtml = rating.user_avatar 
        ? `<img src="${rating.user_avatar}" alt="${rating.user_name}" class="w-12 h-12 rounded-full">`
        : `<div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
             <span class="text-lg font-medium text-pink-600">${rating.user_name.charAt(0)}</span>
           </div>`;

    const editButtonsHtml = rating.can_edit 
        ? `<div class="flex items-center space-x-2">
             <a href="/products/{{ $product->id }}/ratings/${rating.id}/edit" class="text-gray-400 hover:text-pink-600 transition-colors">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
               </svg>
             </a>
             <button onclick="deleteReview(${rating.id})" class="text-gray-400 hover:text-red-600 transition-colors">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
               </svg>
             </button>
           </div>`
        : '';

    reviewDiv.innerHTML = `
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    ${avatarHtml}
                </div>
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <h4 class="font-semibold text-gray-900">${rating.user_name}</h4>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Verified Purchase
                        </span>
                    </div>
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="flex items-center">${starsHtml}</div>
                        <time class="text-sm text-gray-500">${rating.created_at}</time>
                    </div>
                    ${rating.review ? `<p class="text-gray-700 leading-relaxed">${rating.review}</p>` : ''}
                </div>
            </div>
            ${editButtonsHtml}
        </div>
    `;

    return reviewDiv;
}

function deleteReview(ratingId) {
    if (!confirm('Are you sure you want to delete your review? This action cannot be undone.')) {
        return;
    }

    fetch(`/products/{{ $product->id }}/ratings/${ratingId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            // Reload the page to refresh the reviews
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting review:', error);
        showMessage('Failed to delete review. Please try again.', 'error');
    });
}
</script>
@endpush