<?php

namespace App\Rules;

use App\Models\Order;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidOrderStatusTransition implements ValidationRule
{
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currentStatus = $this->order->status->value;
        $newStatus = $value;

        $validTransitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['shipped', 'cancelled'],
            'shipped' => [], // Cannot change from shipped
            'cancelled' => [], // Cannot change from cancelled
        ];

        if ($currentStatus === $newStatus) {
            return; // Same status is allowed
        }

        if (!isset($validTransitions[$currentStatus]) || 
            !in_array($newStatus, $validTransitions[$currentStatus])) {
            $fail("Cannot change order status from {$currentStatus} to {$newStatus}.");
        }
    }
}