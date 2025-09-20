<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Add a product to the user's cart.
     */
    public function addToCart(Product $product, int $quantity = 1, ?User $user = null): CartItem
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            throw new \Exception('User must be authenticated to add items to cart');
        }

        // Check if product has sufficient stock
        if ($product->inventory && $product->inventory->quantity < $quantity) {
            throw new \Exception('Insufficient stock available');
        }

        // Find existing cart item or create new one
        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // Update existing cart item
            $newQuantity = $cartItem->quantity + $quantity;
            
            // Check stock for new total quantity
            if ($product->inventory && $product->inventory->quantity < $newQuantity) {
                throw new \Exception('Insufficient stock available for requested quantity');
            }
            
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        return $cartItem->load('product');
    }

    /**
     * Update the quantity of a cart item.
     */
    public function updateQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        if ($quantity <= 0) {
            $cartItem->delete();
            return $cartItem;
        }

        // Check stock availability
        if ($cartItem->product->inventory && $cartItem->product->inventory->quantity < $quantity) {
            throw new \Exception('Insufficient stock available for requested quantity');
        }

        $cartItem->update(['quantity' => $quantity]);
        
        return $cartItem->load('product');
    }

    /**
     * Remove a product from the cart.
     */
    public function removeFromCart(CartItem $cartItem): bool
    {
        return $cartItem->delete();
    }

    /**
     * Get all cart items for a user.
     */
    public function getCartItems(?User $user = null): Collection
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return new Collection();
        }

        return CartItem::with(['product', 'product.inventory'])
            ->where('user_id', $user->id)
            ->get();
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getCartItemCount(?User $user = null): int
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return 0;
        }

        return CartItem::where('user_id', $user->id)->sum('quantity');
    }

    /**
     * Get the total price of all items in the cart.
     */
    public function getCartTotal(?User $user = null): float
    {
        $cartItems = $this->getCartItems($user);
        
        return $cartItems->sum(function ($cartItem) {
            return $cartItem->quantity * $cartItem->product->price;
        });
    }

    /**
     * Clear all items from the user's cart.
     */
    public function clearCart(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        return CartItem::where('user_id', $user->id)->delete();
    }

    /**
     * Check if all cart items have sufficient stock.
     */
    public function validateCartStock(?User $user = null): array
    {
        $cartItems = $this->getCartItems($user);
        $errors = [];

        foreach ($cartItems as $cartItem) {
            if (!$cartItem->product->inventory) {
                $errors[] = "Product '{$cartItem->product->name}' is no longer available";
                continue;
            }

            if ($cartItem->product->inventory->quantity < $cartItem->quantity) {
                $available = $cartItem->product->inventory->quantity;
                $errors[] = "Only {$available} units of '{$cartItem->product->name}' are available";
            }
        }

        return $errors;
    }
}