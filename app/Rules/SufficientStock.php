<?php

namespace App\Rules;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SufficientStock implements ValidationRule
{
    protected Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->product->inventory) {
            $fail('Product inventory information is not available.');
            return;
        }

        $availableStock = $this->product->inventory->quantity;
        
        if ($value > $availableStock) {
            if ($availableStock === 0) {
                $fail('This product is currently out of stock.');
            } else {
                $fail("Only {$availableStock} items available in stock.");
            }
        }
    }
}