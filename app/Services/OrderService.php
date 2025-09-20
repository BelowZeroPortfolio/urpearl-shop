<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\CartItem;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Enums\NotificationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;

class OrderService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create an order from user's cart items.
     */
    public function createOrderFromCart(User $user, array $shippingAddress, ?string $stripePaymentId = null): Order
    {
        return DB::transaction(function () use ($user, $shippingAddress, $stripePaymentId) {
            // Get user's cart items with products
            $cartItems = $user->cartItems()->with('product.inventory')->get();
            
            if ($cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            // Validate inventory availability for all items
            $this->validateInventoryAvailability($cartItems);

            // Calculate total amount
            $totalAmount = $cartItems->sum(function ($cartItem) {
                return $cartItem->product->price * $cartItem->quantity;
            });

            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => $stripePaymentId ? OrderStatus::PAID : OrderStatus::PENDING,
                'stripe_payment_id' => $stripePaymentId,
                'shipping_address' => $shippingAddress,
            ]);

            // Create order items and decrement inventory
            foreach ($cartItems as $cartItem) {
                $this->createOrderItem($order, $cartItem);
                $this->decrementInventoryForOrderItem($cartItem);
            }

            // Clear the user's cart
            $user->cartItems()->delete();

            // Send order confirmation email
            $this->sendOrderConfirmationEmail($order);

            // Log order creation
            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'items_count' => $cartItems->count(),
            ]);

            return $order;
        });
    }

    /**
     * Create an order with specific items (not from cart).
     */
    public function createOrder(User $user, array $items, array $shippingAddress, ?string $stripePaymentId = null): Order
    {
        return DB::transaction(function () use ($user, $items, $shippingAddress, $stripePaymentId) {
            // Validate and prepare items
            $validatedItems = $this->validateAndPrepareItems($items);

            // Calculate total amount
            $totalAmount = collect($validatedItems)->sum(function ($item) {
                return $item['product']->price * $item['quantity'];
            });

            // Create the order
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => $stripePaymentId ? OrderStatus::PAID : OrderStatus::PENDING,
                'stripe_payment_id' => $stripePaymentId,
                'shipping_address' => $shippingAddress,
            ]);

            // Create order items and decrement inventory
            foreach ($validatedItems as $item) {
                $this->createOrderItemFromArray($order, $item);
                $this->decrementInventoryForProduct($item['product'], $item['quantity']);
            }

            // Send order confirmation email
            $this->sendOrderConfirmationEmail($order);

            // Log order creation
            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'items_count' => count($validatedItems),
            ]);

            return $order;
        });
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Order $order, OrderStatus $status): bool
    {
        try {
            $oldStatus = $order->status;
            $order->status = $status;
            $order->save();

            // Log status change
            Log::info('Order status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus->value,
                'new_status' => $status->value,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'status' => $status->value,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cancel an order and restore inventory.
     */
    public function cancelOrder(Order $order): bool
    {
        if (!$order->isPending() && !$order->isPaid()) {
            throw new \Exception('Only pending or paid orders can be cancelled');
        }

        return DB::transaction(function () use ($order) {
            // Restore inventory for all order items
            foreach ($order->orderItems as $orderItem) {
                $this->inventoryService->incrementStock($orderItem->product, $orderItem->quantity);
            }

            // Update order status
            $order->status = OrderStatus::CANCELLED;
            $order->save();

            Log::info('Order cancelled and inventory restored', [
                'order_id' => $order->id,
                'items_count' => $order->orderItems->count(),
            ]);

            return true;
        });
    }

    /**
     * Get order statistics.
     */
    public function getOrderStats(): array
    {
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', OrderStatus::PENDING)->count();
        $paidOrders = Order::where('status', OrderStatus::PAID)->count();
        $shippedOrders = Order::where('status', OrderStatus::SHIPPED)->count();
        $cancelledOrders = Order::where('status', OrderStatus::CANCELLED)->count();
        $totalRevenue = Order::whereIn('status', [OrderStatus::PAID, OrderStatus::SHIPPED])
            ->sum('total_amount');

        return [
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'paid_orders' => $paidOrders,
            'shipped_orders' => $shippedOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_revenue' => $totalRevenue,
        ];
    }

    /**
     * Validate inventory availability for cart items.
     */
    protected function validateInventoryAvailability($cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $inventory = $cartItem->product->inventory;
            
            if (!$inventory) {
                throw new \Exception("Product '{$cartItem->product->name}' has no inventory record");
            }

            if (!$inventory->hasSufficientStock($cartItem->quantity)) {
                throw new \Exception("Insufficient stock for product '{$cartItem->product->name}'. Available: {$inventory->quantity}, Requested: {$cartItem->quantity}");
            }
        }
    }

    /**
     * Validate and prepare items array.
     */
    protected function validateAndPrepareItems(array $items): array
    {
        $validatedItems = [];

        foreach ($items as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                throw new \Exception('Each item must have product_id and quantity');
            }

            $product = Product::with('inventory')->find($item['product_id']);
            if (!$product) {
                throw new \Exception("Product with ID {$item['product_id']} not found");
            }

            $inventory = $product->inventory;
            if (!$inventory) {
                throw new \Exception("Product '{$product->name}' has no inventory record");
            }

            if (!$inventory->hasSufficientStock($item['quantity'])) {
                throw new \Exception("Insufficient stock for product '{$product->name}'. Available: {$inventory->quantity}, Requested: {$item['quantity']}");
            }

            $validatedItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
            ];
        }

        return $validatedItems;
    }

    /**
     * Create order item from cart item.
     */
    protected function createOrderItem(Order $order, CartItem $cartItem): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $cartItem->product_id,
            'quantity' => $cartItem->quantity,
            'price' => $cartItem->product->price,
        ]);
    }

    /**
     * Create order item from array data.
     */
    protected function createOrderItemFromArray(Order $order, array $item): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item['product']->id,
            'quantity' => $item['quantity'],
            'price' => $item['product']->price,
        ]);
    }

    /**
     * Decrement inventory for cart item.
     */
    protected function decrementInventoryForOrderItem(CartItem $cartItem): void
    {
        $success = $this->inventoryService->decrementStock($cartItem->product, $cartItem->quantity);
        
        if (!$success) {
            throw new \Exception("Failed to decrement inventory for product '{$cartItem->product->name}'");
        }
    }

    /**
     * Decrement inventory for product.
     */
    protected function decrementInventoryForProduct(Product $product, int $quantity): void
    {
        $success = $this->inventoryService->decrementStock($product, $quantity);
        
        if (!$success) {
            throw new \Exception("Failed to decrement inventory for product '{$product->name}'");
        }
    }

    /**
     * Send order confirmation email.
     */
    protected function sendOrderConfirmationEmail(Order $order): void
    {
        try {
            Mail::to($order->user->email)->send(new OrderConfirmation($order));
            
            Log::info('Order confirmation email sent', [
                'order_id' => $order->id,
                'user_email' => $order->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'user_email' => $order->user->email,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception here as order creation should not fail due to email issues
        }
    }
}