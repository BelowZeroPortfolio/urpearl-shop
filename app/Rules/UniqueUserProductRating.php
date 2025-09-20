<?php

namespace App\Rules;

use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueUserProductRating implements ValidationRule
{
    protected User $user;
    protected Product $product;
    protected ?Rating $excludeRating;

    public function __construct(User $user, Product $product, ?Rating $excludeRating = null)
    {
        $this->user = $user;
        $this->product = $product;
        $this->excludeRating = $excludeRating;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Rating::where('user_id', $this->user->id)
            ->where('product_id', $this->product->id);

        if ($this->excludeRating) {
            $query->where('id', '!=', $this->excludeRating->id);
        }

        if ($query->exists()) {
            $fail('You have already reviewed this product.');
        }
    }
}