<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $cartItem = $this->route('cartItem');
        return auth()->check() && $cartItem && $cartItem->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:0|max:100',
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
            'quantity.required' => 'Please specify a quantity.',
            'quantity.integer' => 'Quantity must be a whole number.',
            'quantity.min' => 'Quantity cannot be negative.',
            'quantity.max' => 'You cannot have more than 100 of the same item.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cartItem = $this->route('cartItem');
            
            if ($cartItem && $cartItem->product && $cartItem->product->inventory) {
                $requestedQuantity = $this->input('quantity');
                $availableStock = $cartItem->product->inventory->quantity;
                
                if ($requestedQuantity > 0 && $requestedQuantity > $availableStock) {
                    $validator->errors()->add('quantity', 
                        "Only {$availableStock} items available in stock."
                    );
                }
            }
        });
    }
}