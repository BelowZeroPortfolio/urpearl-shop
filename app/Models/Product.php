<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'size',
        'category_id',
        'image',
        'is_new_arrival',
        'is_best_seller',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_new_arrival' => 'boolean',
        'is_best_seller' => 'boolean',
    ];

    /**
     * Get the size name with full text.
     */
    public function getSizeNameAttribute(): string
    {
        if (empty($this->size)) {
            return 'One Size';
        }

        return match(strtoupper($this->size)) {
            'XS' => 'Extra Small',
            'S' => 'Small',
            'M' => 'Medium',
            'L' => 'Large',
            'XL' => 'Extra Large',
            'XXL' => 'Double Extra Large',
            default => (string) $this->size,
        };
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the inventory for the product.
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Get the cart items for the product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the ratings for the product.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the average rating for the product.
     */
    public function getAverageRatingAttribute(): float
    {
        return $this->ratings()->avg('rating') ?? 0.0;
    }

    /**
     * Get the stock status for the product.
     */
    public function getStockStatusAttribute(): string
    {
        if (!$this->inventory) {
            return 'out_of_stock';
        }

        if ($this->inventory->quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->inventory->isLowStock()) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Check if the product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->inventory && $this->inventory->quantity > 0;
    }
}
