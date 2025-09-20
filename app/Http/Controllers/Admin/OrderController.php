<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of all orders for admin.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by order ID or user name/email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(15);

        // Get order statistics for dashboard cards
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', OrderStatus::PENDING)->count(),
            'paid_orders' => Order::where('status', OrderStatus::PAID)->count(),
            'shipped_orders' => Order::where('status', OrderStatus::SHIPPED)->count(),
            'cancelled_orders' => Order::where('status', OrderStatus::CANCELLED)->count(),
            'total_revenue' => Order::whereIn('status', [OrderStatus::PAID, OrderStatus::SHIPPED])
                                  ->sum('total_amount'),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order for admin.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product']);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStatus = $order->status;
        $newStatus = OrderStatus::from($request->status);

        // Don't update if status is the same
        if ($oldStatus === $newStatus) {
            return redirect()->back()
                ->with('info', 'Order status is already set to ' . $newStatus->value);
        }

        // Validate status transitions
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            return redirect()->back()
                ->with('error', "Invalid status transition from {$oldStatus->value} to {$newStatus->value}. Please check the allowed transitions.");
        }

        try {
            DB::transaction(function () use ($order, $newStatus, $request, $oldStatus) {
                $order->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);

                // Log status change for audit trail
                logger()->info('Order status updated by admin', [
                    'order_id' => $order->id,
                    'customer_email' => $order->user->email,
                    'old_status' => $oldStatus->value,
                    'new_status' => $newStatus->value,
                    'admin_user' => auth()->user()->name,
                    'admin_email' => auth()->user()->email,
                    'notes' => $request->notes,
                    'timestamp' => now()->toISOString(),
                ]);
            });

            return redirect()->back()
                ->with('success', "Order status successfully updated from {$oldStatus->value} to {$newStatus->value}.");
                
        } catch (\Exception $e) {
            logger()->error('Failed to update order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'admin_user' => auth()->user()->name,
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update order status. Please try again.');
        }
    }

    /**
     * Get order statistics for dashboard.
     */
    public function getStats()
    {
        return response()->json([
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', OrderStatus::PENDING)->count(),
            'paid_orders' => Order::where('status', OrderStatus::PAID)->count(),
            'shipped_orders' => Order::where('status', OrderStatus::SHIPPED)->count(),
            'cancelled_orders' => Order::where('status', OrderStatus::CANCELLED)->count(),
            'total_revenue' => Order::whereIn('status', [OrderStatus::PAID, OrderStatus::SHIPPED])
                                  ->sum('total_amount'),
            'recent_orders' => Order::with(['user', 'orderItems'])
                                  ->orderBy('created_at', 'desc')
                                  ->limit(5)
                                  ->get(),
        ]);
    }

    /**
     * Get order details as JSON (for AJAX requests).
     */
    public function getOrderDetails(Order $order)
    {
        $order->load(['user', 'orderItems.product']);

        return response()->json([
            'order' => $order,
            'allowed_transitions' => $this->getAllowedTransitions($order->status),
        ]);
    }

    /**
     * Validate if status transition is allowed.
     */
    private function isValidStatusTransition(OrderStatus $from, OrderStatus $to): bool
    {
        $allowedTransitions = $this->getAllowedTransitions($from);
        return in_array($to->value, $allowedTransitions);
    }

    /**
     * Get allowed status transitions for a given status.
     */
    private function getAllowedTransitions(OrderStatus $status): array
    {
        return match($status) {
            OrderStatus::PENDING => [OrderStatus::PAID->value, OrderStatus::CANCELLED->value],
            OrderStatus::PAID => [OrderStatus::SHIPPED->value, OrderStatus::CANCELLED->value],
            OrderStatus::SHIPPED => [], // No transitions from shipped
            OrderStatus::CANCELLED => [], // No transitions from cancelled
        };
    }
}