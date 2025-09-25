<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    /**
     * Display a listing of products with optional filtering and search.
     */
    public function index(Request $request): View
    {
        // Base query for products
        $query = Product::with(['category', 'inventory'])
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('id', '>', 0);
            
        // Get price stats for the current filters (without price filters)
        $priceStats = Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->when($request->filled('category'), function($q) use ($request) {
                $q->where('category_id', $request->input('category'));
            })
            ->when($request->filled('size'), function($q) use ($request) {
                $q->where('size', $request->input('size'));
            })
            ->when($request->has('new_arrival'), function($q) {
                $q->where('is_new_arrival', true);
            })
            ->when($request->has('best_seller'), function($q) {
                $q->where('is_best_seller', true);
            })
            ->first();
        
        // Get min and max price from request or use default values
        $minPrice = $request->filled('min_price') ? (float)$request->get('min_price') : ($priceStats->min_price ?? 0);
        $maxPrice = $request->filled('max_price') ? (float)$request->get('max_price') : ($priceStats->max_price ?? 1000);
        
        // Ensure min is not greater than max and both are positive
        if ($minPrice > $maxPrice) {
            $temp = $minPrice;
            $minPrice = $maxPrice;
            $maxPrice = $temp;
        }
        
        // Apply price range filter only if price filter was explicitly applied
        if ($request->boolean('price_filter_applied')) {
            if ($request->filled('min_price') || $request->filled('max_price')) {
                $query->whereBetween('price', [
                    $request->filled('min_price') ? $minPrice : 0,
                    $request->filled('max_price') ? $maxPrice : PHP_FLOAT_MAX
                ]);
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = trim($request->get('search'));
            if (!empty($searchTerm)) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%")
                        ->orWhere('size', 'like', "%{$searchTerm}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                            $categoryQuery->where('name', 'like', "%{$searchTerm}%");
                        });
                });
            }
        }

        // Category filtering
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }
        
        // Size filtering
        if ($request->filled('size')) {
            $query->where('size', $request->get('size'));
        }
        
        // New Arrivals filter
        if ($request->has('new_arrival')) {
            $query->where('is_new_arrival', true);
        }
        
        // Best Sellers filter
        if ($request->has('best_seller')) {
            $query->where('is_best_seller', true);
        }

        // Sort functionality
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        switch ($sortBy) {
            case 'price':
                $query->orderBy('price', $sortDirection);
                break;
            case 'rating':
                $query->withAvg('ratings', 'rating')
                    ->orderBy('ratings_avg_rating', $sortDirection);
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('name', $sortDirection);
        }

        $products = $query->paginate(12)->appends(request()->query());
        $categories = Category::orderBy('name')->get();
        
        // Get counts for filters
        $newArrivalCount = Product::where('is_new_arrival', true)->count();
        $bestSellerCount = Product::where('is_best_seller', true)->count();
        
        // Set price range based on actual product prices
        $priceRange = [
            'min' => $priceStats ? $priceStats->min_price : null,
            'max' => $priceStats ? $priceStats->max_price : null
        ];
        
        // Apply new arrival filter if set
        if ($request->has('new_arrival')) {
            $query->where('is_new_arrival', true);
        }

        // Apply best seller filter if set
        if ($request->has('best_seller')) {
            $query->where('is_best_seller', true);
        }

        // Execute the query with pagination
        $products = $query->paginate(12)->appends(request()->query());

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
            'priceStats' => $priceStats,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'newArrivalCount' => $newArrivalCount,
            'bestSellerCount' => $bestSellerCount,
            'isNewArrival' => $request->has('new_arrival'),
            'isBestSeller' => $request->has('best_seller'),
            'priceRange' => [
                'min' => $minPrice,
                'max' => $maxPrice
            ]
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(string $slug): View
    {
        $product = Product::with(['category', 'inventory'])
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('slug', $slug)
            ->firstOrFail();

        // Get related products (same category, exclude current product)
        $relatedProducts = Product::with(['category', 'inventory'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Display a listing of new arrival products.
     */
    public function newArrivals(Request $request): View
    {
        $query = Product::with(['category', 'inventory'])
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('is_new_arrival', true)
            ->latest();

        // Handle search if present
        if ($request->filled('search')) {
            $searchTerm = trim($request->get('search'));
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $products = $query->paginate(12);

        return view('products.new-arrivals', [
            'products' => $products,
        ]);
    }

    /**
     * Display a listing of best seller products.
     */
    public function bestSellers(Request $request): View
    {
        $query = Product::with(['category', 'inventory'])
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('is_best_seller', true)
            ->orderBy('name', 'asc');

        // Handle search if present
        if ($request->filled('search')) {
            $searchTerm = trim($request->get('search'));
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        $products = $query->paginate(12);

        return view('products.best-sellers', [
            'products' => $products,
        ]);
    }
}
