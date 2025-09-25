<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        \Log::info('UpdateProductRequest - authorize() called');
        return $this->user() && $this->user()->can('manage-products');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'size' => 'required|string|in:XS,S,M,L,XL,XXL',
            'is_new_arrival' => 'boolean',
            'is_best_seller' => 'boolean',
        ];
        
        // Handle category validation
        if ($this->input('category_id') === 'new') {
            $rules['new_category'] = 'required|string|max:255|unique:categories,name';
        } else {
            $rules['category_id'] = ['required', 'exists:categories,id'];
        }

        // Only validate image if it's present in the request
        if ($this->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:20480';
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // If no image is being uploaded, remove it from the request
        if (!$this->hasFile('image') && $this->input('image') === null) {
            $this->request->remove('image');
        }

        // Handle checkbox values properly - convert string "1" to boolean true, everything else to false
        $this->merge([
            'is_new_arrival' => $this->input('is_new_arrival') === '1' || $this->input('is_new_arrival') === 1,
            'is_best_seller' => $this->input('is_best_seller') === '1' || $this->input('is_best_seller') === 1,
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'description.required' => 'Product description is required.',
            'description.min' => 'Product description must be at least 10 characters.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Product price must be a valid number.',
            'price.min' => 'Product price must be greater than 0.',
            'price.max' => 'Product price cannot exceed ₱999,999.99.',
            'size.required' => 'Please select a size.',
            'size.in' => 'Please select a valid size (XS, S, M, L, XL, XXL).',
            'category_id.required' => 'Please select a category.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, or webp.',
            'image.max' => 'The image may not be greater than 20MB in size.',
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
            'category_id' => 'category',
            'sku' => 'SKU',
        ];
    }
}