<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('manage-products');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'size' => 'required|string|in:XS,S,M,L,XL,XXL',
            'category_id' => 'required',
            'new_category' => 'required_if:category_id,new|string|max:255|unique:categories,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:20480', // 20MB
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
            'image.required' => 'Product image is required.',
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