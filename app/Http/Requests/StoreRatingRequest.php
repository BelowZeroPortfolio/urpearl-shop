<?php

namespace App\Http\Requests;

use App\Models\Rating;
use App\Models\OrderItem;
use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $product = $this->route('product');
        $user = auth()->user();

        // Check if user has purchased this product
        $hasPurchased = OrderItem::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->whereIn('status', ['paid', 'shipped']);
        })->where('product_id', $product->id)->exists();

        if (!$hasPurchased) {
            return false;
        }

        // Check if user has already rated this product
        $existingRating = Rating::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();

        return !$existingRating;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000|min:10',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Please select a rating.',
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',
            'review.max' => 'Review cannot exceed 1000 characters.',
            'review.min' => 'Review must be at least 10 characters if provided.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'rating' => 'star rating',
        ];
    }
}