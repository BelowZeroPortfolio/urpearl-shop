<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'quantity',
        'low_stock_threshold',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    /**
     * Get the product that owns the inventory.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if the inventory is low stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    /**
     * Decrement the stock quantity.
     */
    public function decrementStock(int $quantity): bool
    {
        if ($this->quantity < $quantity) {
            return false;
        }

        $this->quantity -= $quantity;
        return $this->save();
    }

    /**
     * Increment the stock quantity.
     */
    public function incrementStock(int $quantity): bool
    {
        $this->quantity += $quantity;
        return $this->save();
    }

    /**
     * Check if there's sufficient stock for the given quantity.
     */
    public function hasSufficientStock(int $quantity): bool
    {
        return $this->quantity >= $quantity;
    }
}