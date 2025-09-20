@extends('layouts.app')

@section('title', 'Review by ' . $rating->user->name . ' - ' . $product->name)

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

        <!-- Review Details -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    @if($rating->user->avatar)
                        <img src="{{ $rating->user->avatar }}" alt="{{ $rating->user->name }}" class="w-16 h-16 rounded-full">
                    @else
                        <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-medium text-pink-600">
                                {{ substr($rating->user->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="flex items-center space-x-2 mb-2">
                                <h1 class="text-2xl font-bold text-gray-900">{{ $rating->user->name }}</h1>
                                @if($rating->is_verified_purchase)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Verified Purchase
                                    </span>
                                @endif
                            </div>
                            
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-6 h-6 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-lg font-semibold text-gray-900">{{ $rating->rating }}/5</span>
                            </div>
                            
                            <time class="text-sm text-gray-500">
                                Reviewed on {{ $rating->created_at->format('F j, Y') }}
                                @if($rating->updated_at->ne($rating->created_at))
                                    (Updated {{ $rating->updated_at->format('F j, Y') }})
                                @endif
                            </time>
                        </div>

                        <!-- Edit/Delete buttons for own reviews -->
                        @auth
                            @if(auth()->id() === $rating->user_id)
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('ratings.edit', [$product, $rating]) }}" 
                                       class="btn-secondary">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Edit Review
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>
                    
                    @if($rating->review)
                        <div class="prose prose-gray max-w-none">
                            <p class="text-gray-700 text-lg leading-relaxed">{{ $rating->review }}</p>
                        </div>
                    @else
                        <p class="text-gray-500 italic">No written review provided.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Other Reviews -->
        @php
            $otherReviews = $product->ratings()->where('id', '!=', $rating->id)->with('user')->latest()->take(3)->get();
        @endphp
        
        @if($otherReviews->count() > 0)
            <div class="mt-12">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Other Reviews for This Product</h3>
                
                <div class="space-y-4">
                    @foreach($otherReviews as $otherRating)
                        <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-100">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($otherRating->user->avatar)
                                        <img src="{{ $otherRating->user->avatar }}" alt="{{ $otherRating->user->name }}" class="w-10 h-10 rounded-full">
                                    @else
                                        <div class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-medium text-pink-600">
                                                {{ substr($otherRating->user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-900">{{ $otherRating->user->name }}</h4>
                                        <time class="text-sm text-gray-500">{{ $otherRating->created_at->format('M j, Y') }}</time>
                                    </div>
                                    
                                    <div class="flex items-center mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $otherRating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                        @endfor
                                    </div>
                                    
                                    @if($otherRating->review)
                                        <p class="text-gray-600 text-sm">{{ Str::limit($otherRating->review, 150) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="text-center mt-6">
                    <a href="{{ route('ratings.index', $product) }}" class="btn-secondary">
                        View All Reviews ({{ $product->ratings->count() }})
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection