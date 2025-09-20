<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('manage-inventory');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:0|max:999999',
            'low_stock_threshold' => 'nullable|integer|min:0|max:1000',
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
            'quantity.required' => 'Stock quantity is required.',
            'quantity.integer' => 'Stock quantity must be a whole number.',
            'quantity.min' => 'Stock quantity cannot be negative.',
            'quantity.max' => 'Stock quantity cannot exceed 999,999.',
            'low_stock_threshold.integer' => 'Low stock threshold must be a whole number.',
            'low_stock_threshold.min' => 'Low stock threshold cannot be negative.',
            'low_stock_threshold.max' => 'Low stock threshold cannot exceed 1,000.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $quantity = $this->input('quantity');
            $threshold = $this->input('low_stock_threshold');
            
            if ($threshold !== null && $threshold > $quantity) {
                $validator->errors()->add('low_stock_threshold', 
                    'Low stock threshold cannot be greater than current stock quantity.'
                );
            }
        });
    }
}