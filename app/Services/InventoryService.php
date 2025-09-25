<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Enums\NotificationType;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }
    /**
     * Update inventory quantity for a product.
     */
    public function updateStock(Product $product, int $quantity, int $lowStockThreshold = null): bool
    {
        try {
            DB::beginTransaction();

            $inventory = $product->inventory;
            if (!$inventory) {
                $inventory = $product->inventory()->create([
                    'quantity' => $quantity,
                    'low_stock_threshold' => $lowStockThreshold ?? 5, // Default threshold
                ]);
            } else {
                $inventory->quantity = $quantity;
                if ($lowStockThreshold !== null) {
                    $inventory->low_stock_threshold = $lowStockThreshold;
                }
                $inventory->save();
            }

            // Check for low stock and create notification if needed
            $this->checkLowStockAndNotify($inventory);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update stock', [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Increment stock quantity.
     */
    public function incrementStock(Product $product, int $quantity): bool
    {
        try {
            DB::beginTransaction();

            $inventory = $product->inventory;
            if (!$inventory) {
                return $this->updateStock($product, $quantity);
            }

            $inventory->incrementStock($quantity);
            
            // Check if we need to clear low stock notifications
            $this->clearLowStockNotificationsIfNeeded($inventory);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to increment stock', [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Decrement stock quantity.
     */
    public function decrementStock(Product $product, int $quantity): bool
    {
        try {
            DB::beginTransaction();

            $inventory = $product->inventory;
            if (!$inventory || !$inventory->hasSufficientStock($quantity)) {
                DB::rollBack();
                return false;
            }

            $inventory->decrementStock($quantity);
            
            // Check for low stock and create notification if needed
            $this->checkLowStockAndNotify($inventory);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to decrement stock', [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Update low stock threshold for a product.
     */
    public function updateLowStockThreshold(Product $product, int $threshold): bool
    {
        try {
            $inventory = $product->inventory;
            if (!$inventory) {
                $inventory = $product->inventory()->create([
                    'quantity' => 0,
                    'low_stock_threshold' => $threshold,
                ]);
            } else {
                $inventory->low_stock_threshold = $threshold;
                $inventory->save();
            }

            // Check for low stock with new threshold
            $this->checkLowStockAndNotify($inventory);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update low stock threshold', [
                'product_id' => $product->id,
                'threshold' => $threshold,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all low stock products.
     */
    public function getLowStockProducts()
    {
        return Product::with(['inventory', 'category'])
            ->whereHas('inventory', function ($query) {
                $query->whereRaw('quantity <= low_stock_threshold');
            })
            ->get();
    }

    /**
     * Get inventory statistics.
     */
    public function getInventoryStats(): array
    {
        $totalProducts = Product::count();
        
        // Get all products with their inventory in a single query
        $products = Product::with('inventory')->get();
        
        // Initialize counters
        $outOfStockCount = 0;
        $lowStockCount = 0;
        $inStockCount = 0;
        $noInventoryCount = 0;
        $totalStockValue = 0;
        $outOfStockProducts = [];
        $lowStockProducts = [];
        
        foreach ($products as $product) {
            if (!$product->inventory) {
                $noInventoryCount++;
                continue;
            }
            
            $quantity = $product->inventory->quantity;
            $threshold = $product->inventory->low_stock_threshold;
            
            // Calculate stock value (price * quantity) for in-stock items
            if ($quantity > 0) {
                $totalStockValue += $product->price * $quantity;
            }
            
            if ($quantity <= 0) {
                $outOfStockCount++;
                $outOfStockProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity
                ];
            } elseif ($quantity > 0 && $quantity <= $threshold) {
                $lowStockCount++;
                $lowStockProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'threshold' => $threshold
                ];
            } else {
                $inStockCount++;
            }
        }
        
        // Calculate inventory health percentage
        $inventoryHealth = $totalProducts > 0 
            ? round((($inStockCount + $lowStockCount * 0.5) / $totalProducts) * 100, 2)
            : 100;
            
        // Ensure inventory health is between 0 and 100
        $inventoryHealth = max(0, min(100, $inventoryHealth));

        return [
            'total_products' => $totalProducts,
            'in_stock_count' => $inStockCount,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'no_inventory_count' => $noInventoryCount,
            'inventory_health' => $inventoryHealth,
            'total_stock_value' => number_format($totalStockValue, 2),
            'avg_stock_level' => $inStockCount > 0 
                ? round($inStockCount / $totalProducts * 100, 2) 
                : 0,
            'out_of_stock_products' => $outOfStockProducts,
            'low_stock_products' => $lowStockProducts,
        ];
    }

    /**
     * Check for low stock and create notification if needed.
     */
    public function checkLowStockAndNotify(Inventory $inventory): void
    {
        if ($inventory->isLowStock()) {
            $this->createLowStockNotification($inventory);
        }
    }

    /**
     * Create low stock notification for admin users.
     */
    protected function createLowStockNotification(Inventory $inventory): void
    {
        $product = $inventory->product;
        
        // Check if notification already exists for this product
        $existingNotification = \App\Models\Notification::where('type', NotificationType::LOW_STOCK)
            ->whereJsonContains('payload->product_id', $product->id)
            ->whereNull('read_at')
            ->first();

        if ($existingNotification) {
            return; // Don't create duplicate notifications
        }

        // Use NotificationService to create and send notifications
        $this->notificationService->createLowStockNotification($product);

        Log::info('Low stock notification created', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $inventory->quantity,
            'threshold' => $inventory->low_stock_threshold,
        ]);
    }

    /**
     * Clear low stock notifications if stock is above threshold.
     */
    protected function clearLowStockNotificationsIfNeeded(Inventory $inventory): void
    {
        if (!$inventory->isLowStock()) {
            \App\Models\Notification::where('type', NotificationType::LOW_STOCK)
                ->whereJsonContains('payload->product_id', $inventory->product_id)
                ->whereNull('read_at')
                ->delete();

            Log::info('Low stock notifications cleared', [
                'product_id' => $inventory->product_id,
                'quantity' => $inventory->quantity,
                'threshold' => $inventory->low_stock_threshold,
            ]);
        }
    }

    /**
     * Bulk update inventory for multiple products.
     */
    public function bulkUpdateInventory(array $updates): array
    {
        $results = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($updates as $update) {
                $product = Product::find($update['product_id']);
                if (!$product) {
                    $results[] = [
                        'product_id' => $update['product_id'],
                        'success' => false,
                        'message' => 'Product not found'
                    ];
                    continue;
                }

                $success = true;
                $message = 'Updated successfully';

                if (isset($update['quantity'])) {
                    $success = $this->updateStock($product, $update['quantity']);
                    if (!$success) {
                        $message = 'Failed to update quantity';
                    }
                }

                if ($success && isset($update['low_stock_threshold'])) {
                    $success = $this->updateLowStockThreshold($product, $update['low_stock_threshold']);
                    if (!$success) {
                        $message = 'Failed to update threshold';
                    }
                }

                $results[] = [
                    'product_id' => $update['product_id'],
                    'success' => $success,
                    'message' => $message
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk inventory update failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        return $results;
    }
}