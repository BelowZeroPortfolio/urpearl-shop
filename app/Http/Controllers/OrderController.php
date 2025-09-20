<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->middleware('auth');
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = $user->orders()
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order ID
        if ($request->filled('search')) {
            $query->where('id', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(10);

        // Get order statistics for the user
        $stats = [
            'total_orders' => Auth::user()->orders()->count(),
            'pending_orders' => Auth::user()->orders()->where('status', 'pending')->count(),
            'paid_orders' => Auth::user()->orders()->where('status', 'paid')->count(),
            'shipped_orders' => Auth::user()->orders()->where('status', 'shipped')->count(),
            'cancelled_orders' => Auth::user()->orders()->where('status', 'cancelled')->count(),
        ];

        return view('orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order');
        }

        $order->load(['orderItems.product', 'user']);

        // Get order status history (for now, we'll simulate it with created/updated dates)
        $statusHistory = $this->getOrderStatusHistory($order);

        return view('orders.show', compact('order', 'statusHistory'));
    }

    /**
     * Get order status history for display.
     */
    private function getOrderStatusHistory(Order $order): array
    {
        $history = [];
        
        // Order placed
        $history[] = [
            'status' => 'Order Placed',
            'date' => $order->created_at,
            'description' => 'Your order has been received and is being processed.',
            'completed' => true,
        ];

        // Payment status
        if (in_array($order->status->value, ['paid', 'shipped'])) {
            $history[] = [
                'status' => 'Payment Confirmed',
                'date' => $order->updated_at,
                'description' => 'Payment has been successfully processed.',
                'completed' => true,
            ];
        } else {
            $history[] = [
                'status' => 'Payment Pending',
                'date' => null,
                'description' => 'Waiting for payment confirmation.',
                'completed' => false,
            ];
        }

        // Shipping status
        if ($order->status->value === 'shipped') {
            $history[] = [
                'status' => 'Shipped',
                'date' => $order->updated_at,
                'description' => 'Your order has been shipped and is on its way.',
                'completed' => true,
            ];
        } else if ($order->status->value === 'cancelled') {
            $history[] = [
                'status' => 'Cancelled',
                'date' => $order->updated_at,
                'description' => 'This order has been cancelled.',
                'completed' => true,
            ];
        } else {
            $history[] = [
                'status' => 'Preparing for Shipment',
                'date' => null,
                'description' => 'Your order is being prepared for shipment.',
                'completed' => false,
            ];
        }

        return $history;
    }

    /**
     * Display order confirmation page.
     */
    public function confirmation(Order $order)
    {
        // Ensure user can only view their own order confirmation
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order');
        }

        $order->load(['orderItems.product', 'user']);

        return view('orders.confirmation', compact('order'));
    }

    /**
     * Cancel an order (if allowed).
     */
    public function cancel(Order $order)
    {
        // Ensure user can only cancel their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order');
        }

        try {
            $this->orderService->cancelOrder($order);
            
            return redirect()->route('orders.show', $order)
                ->with('success', 'Order has been cancelled successfully. Inventory has been restored.');
        } catch (\Exception $e) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
}