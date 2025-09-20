<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('manage-orders');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['pending', 'paid', 'shipped', 'cancelled'])
            ],
            'notes' => 'nullable|string|max:500',
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
            'status.required' => 'Order status is required.',
            'status.in' => 'Invalid order status selected.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $order = $this->route('order');
            $newStatus = $this->input('status');
            
            if ($order && !$this->isValidStatusTransition($order->status->value, $newStatus)) {
                $validator->errors()->add('status', 
                    "Cannot change order status from {$order->status->value} to {$newStatus}."
                );
            }
        });
    }

    /**
     * Check if status transition is valid.
     */
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $validTransitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['shipped', 'cancelled'],
            'shipped' => [], // Cannot change from shipped
            'cancelled' => [], // Cannot change from cancelled
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []) || $currentStatus === $newStatus;
    }
}