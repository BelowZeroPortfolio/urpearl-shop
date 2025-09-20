<?php

namespace App\Rules;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasPurchasedProduct implements ValidationRule
{
    protected User $user;
    protected Product $product;

    public function __construct(User $user, Product $product)
    {
        $this->user = $user;
        $this->product = $product;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $hasPurchased = OrderItem::whereHas('order', function ($query) {
            $query->where('user_id', $this->user->id)
                  ->whereIn('status', ['paid', 'shipped']);
        })->where('product_id', $this->product->id)->exists();

        if (!$hasPurchased) {
            $fail('You can only review products you have purchased.');
        }
    }
}