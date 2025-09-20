<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->middleware('auth');
        $this->cartService = $cartService;
    }

    /**
     * Display the shopping cart.
     */
    public function index(): View
    {
        $cartItems = $this->cartService->getCartItems();
        $cartTotal = $this->cartService->getCartTotal();
        $stockErrors = $this->cartService->validateCartStock();

        return view('cart.index', compact('cartItems', 'cartTotal', 'stockErrors'));
    }

    /**
     * Add a product to the cart.
     */
    public function add(AddToCartRequest $request, Product $product): JsonResponse|RedirectResponse
    {

        try {
            $cartItem = $this->cartService->addToCart($product, $request->quantity);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully',
                    'cart_count' => $this->cartService->getCartItemCount(),
                    'cart_total' => $this->cartService->getCartTotal(),
                    'cart_item' => $cartItem
                ]);
            }

            return redirect()->back()->with('success', 'Product added to cart successfully');
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(UpdateCartRequest $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {

        try {
            $updatedCartItem = $this->cartService->updateQuantity($cartItem, $request->quantity);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $request->quantity > 0 ? 'Cart updated successfully' : 'Item removed from cart',
                    'cart_count' => $this->cartService->getCartItemCount(),
                    'cart_total' => $this->cartService->getCartTotal(),
                    'cart_item' => $request->quantity > 0 ? $updatedCartItem : null
                ]);
            }

            $message = $request->quantity > 0 ? 'Cart updated successfully' : 'Item removed from cart';
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(CartItem $cartItem): JsonResponse|RedirectResponse
    {
        // Ensure the cart item belongs to the authenticated user
        if ($cartItem->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->cartService->removeFromCart($cartItem);
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart',
                    'cart_count' => $this->cartService->getCartItemCount(),
                    'cart_total' => $this->cartService->getCartTotal()
                ]);
            }

            return redirect()->back()->with('success', 'Item removed from cart');
            
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): JsonResponse|RedirectResponse
    {
        try {
            $this->cartService->clearCart();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart cleared successfully',
                    'cart_count' => 0,
                    'cart_total' => 0
                ]);
            }

            return redirect()->back()->with('success', 'Cart cleared successfully');
            
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    /**
     * Get cart data for AJAX requests.
     */
    public function data(): JsonResponse
    {
        return response()->json([
            'cart_items' => $this->cartService->getCartItems(),
            'cart_count' => $this->cartService->getCartItemCount(),
            'cart_total' => $this->cartService->getCartTotal(),
            'stock_errors' => $this->cartService->validateCartStock()
        ]);
    }
}