<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory listing with low-stock indicators.
     */
    public function index(Request $request)
    {
        $query = Product::with(['inventory', 'category']);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low_stock':
                    $query->whereHas('inventory', function ($q) {
                        $q->whereRaw('quantity <= low_stock_threshold AND quantity > 0');
                    });
                    break;
                case 'out_of_stock':
                    // Include products without inventory records as out of stock
                    $query->where(function ($q) {
                        $q->whereHas('inventory', function ($subQ) {
                            $subQ->where('quantity', '<=', 0);
                        })->orWhereDoesntHave('inventory');
                    });
                    break;
                case 'in_stock':
                    $query->whereHas('inventory', function ($q) {
                        $q->whereRaw('quantity > low_stock_threshold');
                    });
                    break;
            }
        }

        // Search by product name or ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Sort by stock quantity
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'quantity_asc':
                    $query->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
                        ->orderByRaw('COALESCE(inventories.quantity, 0) ASC')
                        ->select('products.*');
                    break;
                case 'quantity_desc':
                    $query->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
                        ->orderByRaw('COALESCE(inventories.quantity, 0) DESC')
                        ->select('products.*');
                    break;
                default:
                    $query->orderBy('name');
            }
        } else {
            $query->orderBy('name');
        }

        $products = $query->paginate(20)->appends(request()->query());
        
        // Ensure all products have inventory records (fallback for any products that might not have them)
        foreach ($products as $product) {
            if (!$product->inventory) {
                $product->inventory()->create([
                    'quantity' => 0,
                    'low_stock_threshold' => 5,
                ]);
                // Refresh the relationship
                $product->load('inventory');
                \Log::warning('Created missing inventory record for product', [
                    'product_id' => $product->id,
                    'product_name' => $product->name
                ]);
            }
        }
        
        $categories = Category::orderBy('name')->get();
        $stats = $this->inventoryService->getInventoryStats();

        return view('admin.inventory.index', compact('products', 'categories', 'stats'));
    }

    /**
     * Show the form for editing inventory.
     */
    public function edit(Product $product)
    {
        $product->load('inventory', 'category');

        // Create inventory record if it doesn't exist
        if (!$product->inventory) {
            $product->inventory()->create([
                'quantity' => 0,
                'low_stock_threshold' => 10,
            ]);
            $product->refresh();
        }

        return view('admin.inventory.edit', compact('product'));
    }

    /**
     * Update inventory for a specific product.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $success = $this->inventoryService->updateStock(
            $product,
            $validated['quantity'],
            $validated['low_stock_threshold']
        );

        if ($success) {
            return redirect()->route('admin.inventory.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Inventory updated successfully!',
                    'duration' => 3000
                ]);
        }

        return redirect()->back()
            ->with('error', 'Failed to update inventory. Please try again.')
            ->withInput();
    }

    /**
     * Bulk update inventory.
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'updates' => 'required|array|min:1',
            'updates.*.product_id' => 'required|exists:products,id',
            'updates.*.quantity' => 'nullable|integer|min:0',
            'updates.*.low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $results = $this->inventoryService->bulkUpdateInventory($request->updates);

            $successCount = collect($results)->where('success', true)->count();
            $totalCount = count($results);

            return response()->json([
                'success' => true,
                'message' => "Successfully updated {$successCount} out of {$totalCount} products",
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products for dashboard.
     */
    public function lowStock()
    {
        $lowStockProducts = $this->inventoryService->getLowStockProducts();

        return response()->json([
            'success' => true,
            'products' => $lowStockProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category->name ?? 'Uncategorized',
                    'current_quantity' => $product->inventory->quantity,
                    'threshold' => $product->inventory->low_stock_threshold,
                    'stock_status' => $product->stock_status,
                ];
            })
        ]);
    }

    /**
     * Quick stock adjustment (increment/decrement).
     */
    public function adjustStock(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'action' => ['required', Rule::in(['increment', 'decrement'])],
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = false;
        $message = '';

        if ($request->action === 'increment') {
            $success = $this->inventoryService->incrementStock($product, $request->quantity);
            $message = $success ? 'Stock incremented successfully' : 'Failed to increment stock';
        } else {
            $success = $this->inventoryService->decrementStock($product, $request->quantity);
            $message = $success ? 'Stock decremented successfully' : 'Insufficient stock or update failed';
        }

        $product->refresh();

        return response()->json([
            'success' => $success,
            'message' => $message,
            'new_quantity' => $product->inventory->quantity ?? 0,
            'stock_status' => $product->stock_status,
        ]);
    }

    /**
     * Export inventory data.
     */
    public function export(Request $request)
    {
        $products = Product::with(['inventory', 'category'])->get();

        $csvData = [];
        $csvData[] = ['Product Name', 'SKU', 'Category', 'Current Stock', 'Low Stock Threshold', 'Stock Status'];

        foreach ($products as $product) {
            $csvData[] = [
                $product->name,
                $product->sku,
                $product->category->name ?? 'Uncategorized',
                $product->inventory->quantity ?? 0,
                $product->inventory->low_stock_threshold ?? 0,
                $product->stock_status,
            ];
        }

        $filename = 'inventory_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Create missing inventory records for products that don't have them.
     */
    public function createMissingInventoryRecords()
    {
        $productsWithoutInventory = Product::doesntHave('inventory')->get();
        $created = 0;

        foreach ($productsWithoutInventory as $product) {
            try {
                $product->inventory()->create([
                    'quantity' => 0,
                    'low_stock_threshold' => 5,
                ]);
                $created++;
            } catch (\Exception $e) {
                \Log::error('Failed to create inventory for product', [
                    'product_id' => $product->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Created {$created} inventory records for products that were missing them.",
            'created_count' => $created,
            'total_products_without_inventory' => $productsWithoutInventory->count()
        ]);
    }
}
