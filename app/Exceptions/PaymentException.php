<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentException extends Exception
{
    protected $paymentError;
    protected $stripeError;

    public function __construct(string $message, string $paymentError = null, $stripeError = null)
    {
        $this->paymentError = $paymentError;
        $this->stripeError = $stripeError;
        
        parent::__construct($message);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_type' => 'payment_failed',
                'payment_error' => $this->paymentError
            ], 422);
        }

        return response()->view('errors.payment-failed', [
            'message' => $this->getMessage(),
            'paymentError' => $this->paymentError
        ], 422);
    }

    public function getPaymentError(): ?string
    {
        return $this->paymentError;
    }

    public function getStripeError()
    {
        return $this->stripeError;
    }
}