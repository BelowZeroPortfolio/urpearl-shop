<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rating;
use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RatingController extends Controller
{
    /**
     * Display ratings for a specific product.
     */
    public function index(Product $product)
    {
        $ratings = $product->ratings()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('ratings.index', compact('product', 'ratings'));
    }

    /**
     * Show the form for creating a new rating.
     */
    public function create(Product $product)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to leave a review.');
        }

        $user = Auth::user();

        // Check if user has purchased this product
        if (!$this->hasUserPurchasedProduct($user->id, $product->id)) {
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'You can only review products you have purchased.');
        }

        // Check if user has already rated this product
        $existingRating = Rating::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingRating) {
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'You have already reviewed this product.');
        }

        return view('ratings.create', compact('product'));
    }

    /**
     * Store a newly created rating in storage.
     */
    public function store(StoreRatingRequest $request, Product $product)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to leave a review.'
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'You must be logged in to leave a review.');
        }

        $user = Auth::user();

        // Check if user has purchased this product
        if (!$this->hasUserPurchasedProduct($user->id, $product->id)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only review products you have purchased.'
                ], 403);
            }
            
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'You can only review products you have purchased.');
        }

        // Check if user has already rated this product
        $existingRating = Rating::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingRating) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this product.'
                ], 409);
            }
            
            return redirect()->route('products.show', $product->slug)
                ->with('error', 'You have already reviewed this product.');
        }

        // Get validated data
        $validated = $request->validated();

        try {
            // Create the rating
            $rating = Rating::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
                'is_verified_purchase' => true, // Always true since we verify purchase before allowing rating
            ]);

            // Load the user relationship for the response
            $rating->load('user');

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Review submitted successfully!',
                    'rating' => [
                        'id' => $rating->id,
                        'rating' => $rating->rating,
                        'review' => $rating->review,
                        'user_name' => $rating->user->name,
                        'created_at' => $rating->created_at->format('M d, Y'),
                    ],
                    'product_average_rating' => $product->fresh()->average_rating,
                ]);
            }

            return redirect()->route('products.show', $product->slug)
                ->with('success', 'Review submitted successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit review. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit review. Please try again.');
        }
    }

    /**
     * Display the specified rating.
     */
    public function show(Product $product, Rating $rating)
    {
        // Ensure the rating belongs to the product
        if ($rating->product_id !== $product->id) {
            abort(404);
        }

        $rating->load('user');

        return view('ratings.show', compact('product', 'rating'));
    }

    /**
     * Show the form for editing the specified rating.
     */
    public function edit(Product $product, Rating $rating)
    {
        // Check if user is authenticated and owns the rating
        if (!Auth::check() || Auth::id() !== $rating->user_id) {
            abort(403, 'You can only edit your own reviews.');
        }

        // Ensure the rating belongs to the product
        if ($rating->product_id !== $product->id) {
            abort(404);
        }

        return view('ratings.edit', compact('product', 'rating'));
    }

    /**
     * Update the specified rating in storage.
     */
    public function update(UpdateRatingRequest $request, Product $product, Rating $rating)
    {
        // Check if user is authenticated and owns the rating
        if (!Auth::check() || Auth::id() !== $rating->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own reviews.'
            ], 403);
        }

        // Ensure the rating belongs to the product
        if ($rating->product_id !== $product->id) {
            abort(404);
        }

        // Get validated data
        $validated = $request->validated();

        try {
            // Update the rating
            $rating->update([
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Review updated successfully!',
                    'rating' => [
                        'id' => $rating->id,
                        'rating' => $rating->rating,
                        'review' => $rating->review,
                        'user_name' => $rating->user->name,
                        'updated_at' => $rating->updated_at->format('M d, Y'),
                    ],
                    'product_average_rating' => $product->fresh()->average_rating,
                ]);
            }

            return redirect()->route('products.show', $product->slug)
                ->with('success', 'Review updated successfully!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update review. Please try again.'
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update review. Please try again.');
        }
    }

    /**
     * Remove the specified rating from storage.
     */
    public function destroy(Product $product, Rating $rating)
    {
        // Check if user is authenticated and owns the rating
        if (!Auth::check() || Auth::id() !== $rating->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete your own reviews.'
            ], 403);
        }

        // Ensure the rating belongs to the product
        if ($rating->product_id !== $product->id) {
            abort(404);
        }

        try {
            $rating->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully!',
                'product_average_rating' => $product->fresh()->average_rating,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete review. Please try again.'
            ], 500);
        }
    }

    /**
     * Check if a user has purchased a specific product.
     */
    private function hasUserPurchasedProduct(int $userId, int $productId): bool
    {
        return OrderItem::whereHas('order', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->whereIn('status', ['paid', 'shipped']); // Only paid or shipped orders count
        })->where('product_id', $productId)->exists();
    }

    /**
     * Get ratings data for AJAX requests.
     */
    public function getRatings(Product $product, Request $request)
    {
        $ratings = $product->ratings()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $ratingsData = $ratings->map(function ($rating) {
            return [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'review' => $rating->review,
                'user_name' => $rating->user->name,
                'user_avatar' => $rating->user->avatar,
                'created_at' => $rating->created_at->format('M d, Y'),
                'can_edit' => Auth::check() && Auth::id() === $rating->user_id,
            ];
        });

        return response()->json([
            'success' => true,
            'ratings' => $ratingsData,
            'pagination' => [
                'current_page' => $ratings->currentPage(),
                'last_page' => $ratings->lastPage(),
                'per_page' => $ratings->perPage(),
                'total' => $ratings->total(),
            ],
            'average_rating' => $product->average_rating,
            'total_ratings' => $product->ratings()->count(),
        ]);
    }

    /**
     * Check if current user can review a product.
     */
    public function canReview(Product $product)
    {
        if (!Auth::check()) {
            return response()->json([
                'can_review' => false,
                'reason' => 'not_authenticated'
            ]);
        }

        $user = Auth::user();

        // Check if user has purchased this product
        if (!$this->hasUserPurchasedProduct($user->id, $product->id)) {
            return response()->json([
                'can_review' => false,
                'reason' => 'not_purchased'
            ]);
        }

        // Check if user has already rated this product
        $existingRating = Rating::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingRating) {
            return response()->json([
                'can_review' => false,
                'reason' => 'already_reviewed',
                'existing_rating' => [
                    'id' => $existingRating->id,
                    'rating' => $existingRating->rating,
                    'review' => $existingRating->review,
                ]
            ]);
        }

        return response()->json([
            'can_review' => true
        ]);
    }
}