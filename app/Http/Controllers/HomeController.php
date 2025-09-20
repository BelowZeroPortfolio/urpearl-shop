<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index(): View
    {
        // Get featured products (latest 6 products with inventory)
        $featuredProducts = Product::with(['category', 'inventory', 'ratings'])
            ->whereHas('inventory', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->latest()
            ->limit(6)
            ->get();

        // Get categories with product counts
        $categories = Category::withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('home', compact('featuredProducts', 'categories'));
    }
}