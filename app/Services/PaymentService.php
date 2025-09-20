<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent for the given amount
     *
     * @param int $amount Amount in cents
     * @param string $currency Currency code (default: 'php')
     * @param array $metadata Additional metadata
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent(int $amount, string $currency = 'php', array $metadata = []): PaymentIntent
    {
        try {
            return PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
                'currency' => $currency,
                'metadata' => $metadata
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a payment intent by ID
     *
     * @param string $paymentIntentId
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent retrieval failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId
            ]);
            throw $e;
        }
    }

    /**
     * Confirm a payment intent
     *
     * @param string $paymentIntentId
     * @param array $params Additional parameters
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function confirmPaymentIntent(string $paymentIntentId, array $params = []): PaymentIntent
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId)->confirm($params);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent confirmation failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
                'params' => $params
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a payment intent
     *
     * @param string $paymentIntentId
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function cancelPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        try {
            return PaymentIntent::retrieve($paymentIntentId)->cancel();
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent cancellation failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId
            ]);
            throw $e;
        }
    }

    /**
     * Convert amount to cents for Stripe
     *
     * @param float $amount
     * @return int
     */
    public function convertToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert amount from cents to decimal
     *
     * @param int $cents
     * @return float
     */
    public function convertFromCents(int $cents): float
    {
        return $cents / 100;
    }
}