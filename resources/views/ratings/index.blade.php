@extends('layouts.app')

@section('title', 'Reviews for ' . $product->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
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
                         class="w-20 h-20 object-cover rounded-xl">
                @else
                    <div class="w-20 h-20 bg-gray-200 rounded-xl flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
                    <p class="text-xl font-bold text-pink-600">₱{{ number_format($product->price, 2) }}</p>
                    @if($product->ratings->count() > 0)
                        <div class="flex items-center space-x-2 mt-2">
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $product->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm text-gray-600">
                                {{ number_format($product->average_rating, 1) }} ({{ $product->ratings->count() }} {{ Str::plural('review', $product->ratings->count()) }})
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- All Reviews -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">All Reviews ({{ $ratings->total() }})</h2>
                
                @auth
                    <a href="{{ route('ratings.create', $product) }}" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Write a Review
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Login to Review
                    </a>
                @endauth
            </div>

            @if($ratings->count() > 0)
                <div class="space-y-6">
                    @foreach($ratings as $rating)
                        <div class="border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
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

                <!-- Pagination -->
                @if($ratings->hasPages())
                    <div class="mt-8">
                        {{ $ratings->links() }}
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
</div>

<script>
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
@endsection