<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ValidationController extends Controller
{
    /**
     * Validate a single field via AJAX
     */
    public function validateField(Request $request): JsonResponse
    {
        $field = $request->input('field');
        $value = $request->input('value');
        $rules = $request->input('rules', []);
        
        if (!$field || empty($rules)) {
            return response()->json([
                'valid' => true
            ]);
        }
        
        // Convert rules array to validation rule string
        $ruleString = implode('|', $rules);
        
        // Create validator
        $validator = Validator::make(
            [$field => $value],
            [$field => $ruleString]
        );
        
        // Add custom validation logic based on field type
        $this->addCustomValidation($validator, $field, $value, $request);
        
        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first($field)
            ]);
        }
        
        return response()->json([
            'valid' => true
        ]);
    }
    
    /**
     * Add custom validation logic for specific fields
     */
    private function addCustomValidation($validator, string $field, $value, Request $request): void
    {
        $validator->after(function ($validator) use ($field, $value, $request) {
            switch ($field) {
                case 'quantity':
                    $this->validateQuantity($validator, $value, $request);
                    break;
                    
                case 'sku':
                    $this->validateSku($validator, $value, $request);
                    break;
                    
                case 'email':
                    $this->validateEmail($validator, $value, $request);
                    break;
            }
        });
    }
    
    /**
     * Validate quantity against stock levels
     */
    private function validateQuantity($validator, $value, Request $request): void
    {
        $productId = $request->input('product_id');
        
        if ($productId && $value > 0) {
            $product = \App\Models\Product::with('inventory')->find($productId);
            
            if ($product && $product->inventory) {
                $availableStock = $product->inventory->quantity;
                
                if ($value > $availableStock) {
                    if ($availableStock === 0) {
                        $validator->errors()->add('quantity', 'This product is currently out of stock.');
                    } else {
                        $validator->errors()->add('quantity', "Only {$availableStock} items available in stock.");
                    }
                }
            }
        }
    }
    
    /**
     * Validate SKU uniqueness
     */
    private function validateSku($validator, $value, Request $request): void
    {
        $productId = $request->input('product_id');
        
        $query = \App\Models\Product::where('sku', $value);
        
        if ($productId) {
            $query->where('id', '!=', $productId);
        }
        
        if ($query->exists()) {
            $validator->errors()->add('sku', 'This SKU is already in use.');
        }
    }
    
    /**
     * Validate email format and uniqueness if needed
     */
    private function validateEmail($validator, $value, Request $request): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $validator->errors()->add('email', 'Please enter a valid email address.');
            return;
        }
        
        $checkUnique = $request->input('check_unique', false);
        $userId = $request->input('user_id');
        
        if ($checkUnique) {
            $query = \App\Models\User::where('email', $value);
            
            if ($userId) {
                $query->where('id', '!=', $userId);
            }
            
            if ($query->exists()) {
                $validator->errors()->add('email', 'This email address is already registered.');
            }
        }
    }
}