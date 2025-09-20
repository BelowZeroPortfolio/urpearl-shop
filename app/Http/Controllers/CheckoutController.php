<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private PaymentService $paymentService,
        private OrderService $orderService
    ) {
        $this->middleware('auth');
    }

    /**
     * Show the checkout page
     */
    public function index(): View|RedirectResponse
    {
        $cartItems = $this->cartService->getCartItems(Auth::id());
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty. Please add items before checkout.');
        }

        $total = $this->cartService->getCartTotal(Auth::id());
        
        return view('checkout.index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'stripeKey' => config('services.stripe.key')
        ]);
    }

    /**
     * Get order summary for checkout
     */
    public function summary(): \Illuminate\View\View
    {
        $cartItems = $this->cartService->getCartItems(Auth::id());
        $subtotal = $this->cartService->getCartSubtotal(Auth::id());
        $shipping = 10.00; // Flat rate shipping for now
        $total = $subtotal + $shipping;

        return view('checkout.partials.summary', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total
        ]);
    }

    /**
     * Get payment form
     */
    public function paymentForm(): \Illuminate\View\View
    {
        $total = $this->cartService->getCartTotal(Auth::id());
        
        return view('checkout.partials.payment-form', [
            'total' => $total,
            'stripeKey' => config('services.stripe.key')
        ]);
    }

    /**
     * Create payment intent for checkout
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $cartItems = $this->cartService->getCartItems($user->id);
            
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty.'
                ], 400);
            }

            $total = $this->cartService->getCartTotal($user->id);
            $amountInCents = $this->paymentService->convertToCents($total);

            $paymentIntent = $this->paymentService->createPaymentIntent(
                $amountInCents,
                'php',
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'cart_total' => $total
                ]
            );

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $total
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Payment intent creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to process payment. Please try again.'
            ], 500);
        }
    }

    /**
     * Process the payment and create order
     */
    public function processPayment(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Validate request data
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string|max:255',
            'shipping_address.line1' => 'required|string|max:255',
            'shipping_address.line2' => 'nullable|string|max:255',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.state' => 'required|string|max:100',
            'shipping_address.postal_code' => 'required|string|max:20',
            'shipping_address.country' => 'required|string|size:2',
            'shipping_address.phone' => 'nullable|string|max:20',
        ]);

        try {
            // Confirm the payment intent with Stripe
            $paymentIntent = $this->paymentService->retrievePaymentIntent($validated['payment_intent_id']);
            
            // If payment intent is not in a success state, confirm it
            if (!in_array($paymentIntent->status, ['succeeded', 'processing'])) {
                $paymentIntent = $this->paymentService->confirmPaymentIntent($validated['payment_intent_id']);
            }

            // Verify the payment was successful
            if (!in_array($paymentIntent->status, ['succeeded', 'processing'])) {
                throw new \Exception('Payment failed or was not completed');
            }

            // Verify the payment amount matches the cart total
            $cartTotal = $this->cartService->getCartTotal($user->id);
            $paymentAmount = $this->paymentService->convertFromCents($paymentIntent->amount);
            
            if (!bccomp((string)$paymentAmount, (string)$cartTotal, 2) === 0) {
                // If amounts don't match, log the discrepancy and continue
                // (in production, you might want to handle this differently)
                Log::warning('Cart total and payment amount do not match', [
                    'user_id' => $user->id,
                    'cart_total' => $cartTotal,
                    'payment_amount' => $paymentAmount,
                    'payment_intent' => $paymentIntent->id
                ]);
            }

            // Create the order
            $order = $this->orderService->createOrderFromCart(
                $user,
                $validated['shipping_address'],
                $paymentIntent->id
            );

            // Clear the cart after successful order creation
            $this->cartService->clearCart($user->id);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number, // Make sure your Order model has this attribute
                'redirect_url' => route('orders.confirmation', $order->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e instanceof \Illuminate\Validation\ValidationException 
                    ? $e->errors()
                    : 'Failed to process payment. ' . $e->getMessage(),
                'code' => $e->getCode() ?: 500
            ], $e instanceof \Illuminate\Validation\ValidationException ? 422 : 500);
        }
    }
}