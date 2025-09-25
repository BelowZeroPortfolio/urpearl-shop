<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products with search and pagination.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'inventory']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            $stockStatus = $request->get('stock_status');
            if ($stockStatus === 'in_stock') {
                $query->whereHas('inventory', function ($q) {
                    $q->whereRaw('quantity > low_stock_threshold');
                });
            } elseif ($stockStatus === 'low_stock') {
                $query->whereHas('inventory', function ($q) {
                    $q->whereRaw('quantity <= low_stock_threshold AND quantity > 0');
                });
            } elseif ($stockStatus === 'out_of_stock') {
                $query->whereHas('inventory', function ($q) {
                    $q->where('quantity', '<=', 0);
                });
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        \Log::info('=== START PRODUCT CREATION ===');
        \Log::info('Request data:', $request->all());
        
        try {
            \DB::beginTransaction();
            $validated = $request->validated();
            \Log::info('Validation passed:', $validated);

            // Handle new category creation if 'Other' was selected
            if ($request->category_id === 'new' && $request->filled('new_category')) {
                \Log::info('Creating new category:', ['name' => $request->new_category]);
                $category = Category::create([
                    'name' => $request->new_category,
                    'slug' => Str::slug($request->new_category),
                    'description' => $request->new_category,
                    'is_active' => true
                ]);
                $validated['category_id'] = $category->id;
                \Log::info('New category created:', $category->toArray());
            } else {
                $validated['category_id'] = $request->category_id;
                \Log::info('Using existing category ID:', ['category_id' => $request->category_id]);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                \Log::info('Processing image upload');
                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $validated['image'] = $imagePath;
                \Log::info('Image uploaded:', ['path' => $imagePath]);
            }

            // Generate slug if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
                \Log::info('Generated slug:', ['slug' => $validated['slug']]);
            }

            // Log the validated data before creating the product
            \Log::info('Creating product with data:', [
                'name' => $validated['name'],
                'is_new_arrival' => $validated['is_new_arrival'] ?? false,
                'is_best_seller' => $validated['is_best_seller'] ?? false,
                'type_of_is_new_arrival' => gettype($validated['is_new_arrival'] ?? null),
                'type_of_is_best_seller' => gettype($validated['is_best_seller'] ?? null)
            ]);
            
            // Create the product
            $product = Product::create($validated);
            \Log::info('Product created:', [
                'id' => $product->id,
                'name' => $product->name,
                'is_new_arrival' => $product->is_new_arrival,
                'is_best_seller' => $product->is_best_seller,
                'type_of_is_new_arrival' => gettype($product->is_new_arrival),
                'type_of_is_best_seller' => gettype($product->is_best_seller)
            ]);

            // Create an inventory record for the new product
            $inventory = $product->inventory()->create([
                'quantity' => 0, // Default to 0, can be updated later
                'low_stock_threshold' => 5, // Default threshold, can be configured
            ]);
            \Log::info('Inventory record created successfully:', [
                'inventory_id' => $inventory->id,
                'product_id' => $product->id,
                'quantity' => $inventory->quantity,
                'threshold' => $inventory->low_stock_threshold
            ]);

            \DB::commit();
            \Log::info('=== PRODUCT CREATION COMPLETED SUCCESSFULLY ===');
            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully with inventory record.');
                
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error creating product:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            \Log::info('=== PRODUCT CREATION FAILED ===');
            return back()->withInput()->with('error', 'Failed to create product. Please try again.');
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'inventory', 'ratings.user']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        \Log::info('=== START PRODUCT UPDATE ===');
        \Log::info('Update request data:', $request->all());
        \Log::info('Product before update:', $product->toArray());
        
        try {
            $validated = $request->validated();
            \Log::info('Validated data:', $validated);

            // Handle new category creation if 'Other' was selected
            if ($request->category_id === 'new' && $request->filled('new_category')) {
                \Log::info('Creating new category:', ['name' => $request->new_category]);
                $category = Category::create([
                    'name' => $request->new_category,
                    'slug' => Str::slug($request->new_category),
                    'description' => $request->new_category,
                    'is_active' => true
                ]);
                $validated['category_id'] = $category->id;
                \Log::info('New category created:', $category->toArray());
            } else if ($request->has('category_id') && $request->category_id !== 'new') {
                $validated['category_id'] = $request->category_id;
                \Log::info('Using existing category ID:', ['category_id' => $request->category_id]);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                \Log::info('New image file detected');
                // Delete old image if exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    \Log::info('Deleting old image:', ['path' => $product->image]);
                    Storage::disk('public')->delete($product->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $validated['image'] = $imagePath;
                \Log::info('New image uploaded:', ['path' => $imagePath]);
            } else if ($request->has('remove_image') && $request->remove_image) {
                // Handle image removal if needed
                \Log::info('Removing existing image');
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $validated['image'] = null;
            } else {
                \Log::info('No image changes detected');
                // Keep the existing image
                unset($validated['image']);
            }

            // Update slug if name changed
            if (isset($validated['name']) && $product->name !== $validated['name']) {
                $validated['slug'] = Str::slug($validated['name']);
                \Log::info('Name changed, updating slug:', ['old_name' => $product->name, 'new_name' => $validated['name'], 'new_slug' => $validated['slug']]);
            }

            \Log::info('Updating product with data:', $validated);
            $product->update($validated);
            $product->refresh(); // Refresh to get the latest data
            \Log::info('Product updated successfully:', $product->toArray());
            
            $redirectUrl = route('admin.products.index');
            
            \Log::info('=== END PRODUCT UPDATE (SUCCESS) ===');
            
            // For AJAX/JSON requests, return the JSON response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'redirect' => $redirectUrl . (str_contains($redirectUrl, '?') ? '&' : '?') . 'from_redirect=1'
                ]);
            }
            
            // For regular form submissions, redirect with success message
            return redirect($redirectUrl . (str_contains($redirectUrl, '?') ? '&' : '?') . 'from_redirect=1')
                ->with('success', 'Product updated successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error updating product:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'validated_data' => $validated ?? null,
                'product_id' => $product->id
            ]);
            
            $errorResponse = [
                'message' => 'Failed to update product. ' . $e->getMessage(),
                'errors' => method_exists($e, 'errors') ? $e->errors() : []
            ];
            
            \Log::error('Sending error response:', $errorResponse);
            \Log::info('=== END PRODUCT UPDATE (ERROR) ===');
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json($errorResponse, 422);
            }
            
            return back()->withInput()->with('error', 'Failed to update product. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Check if product has related orders
        if ($product->orderItems()->exists()) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Cannot delete product that has been ordered.');
        }

        // Delete associated cart items
        $product->cartItems()->delete();

        // Delete associated ratings
        $product->ratings()->delete();

        // Delete associated inventory
        if ($product->inventory) {
            $product->inventory->delete();
        }

        // Delete product image
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        // Delete the product
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Bulk delete products.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $productIds = $request->get('product_ids');
        $products = Product::whereIn('id', $productIds)->get();

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($products as $product) {
            // Check if product has related orders
            if ($product->orderItems()->exists()) {
                $skippedCount++;
                continue;
            }

            // Delete associated data
            $product->cartItems()->delete();
            $product->ratings()->delete();
            if ($product->inventory) {
                $product->inventory->delete();
            }

            // Delete product image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();
            $deletedCount++;
        }

        $message = "Deleted {$deletedCount} products.";
        if ($skippedCount > 0) {
            $message .= " Skipped {$skippedCount} products with existing orders.";
        }

        return redirect()->route('admin.products.index')
            ->with('success', $message);
    }
}