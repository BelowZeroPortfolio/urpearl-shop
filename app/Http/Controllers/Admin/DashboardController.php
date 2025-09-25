<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\User;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends AdminController
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Calculate KPIs
        $kpis = $this->calculateKPIs();
        
        // Get recent orders
        $recentOrders = $this->getRecentOrders();
        
        // Get low stock products
        $lowStockProducts = $this->getLowStockProducts();
        
        return view('admin.dashboard', compact('kpis', 'recentOrders', 'lowStockProducts'));
    }

    /**
     * Calculate key performance indicators.
     */
    private function calculateKPIs(): array
    {
        // Total products
        $totalProducts = Product::count();
        
        // Total orders
        $totalOrders = Order::count();
        
        // Total revenue (from processing and shipped orders)
        $totalRevenue = Order::whereIn('status', [
                OrderStatus::PROCESSING->value,
                OrderStatus::SHIPPED->value,
                OrderStatus::DELIVERED->value
            ])
            ->sum('total_amount');
        
        // Low stock count
        $lowStockCount = Inventory::whereRaw('quantity <= low_stock_threshold')->count();
        
        // Monthly revenue (current month)
        $monthlyRevenue = Order::whereIn('status', [
                OrderStatus::PROCESSING->value,
                OrderStatus::SHIPPED->value,
                OrderStatus::DELIVERED->value
            ])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('total_amount');
        
        // Monthly orders (current month)
        $monthlyOrders = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        // Previous month data for comparison
        $previousMonth = Carbon::now()->subMonth();
        $previousMonthRevenue = Order::whereIn('status', [
                OrderStatus::PROCESSING->value,
                OrderStatus::SHIPPED->value,
                OrderStatus::DELIVERED->value
            ])
            ->whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->sum('total_amount');
        
        $previousMonthOrders = Order::whereMonth('created_at', $previousMonth->month)
            ->whereYear('created_at', $previousMonth->year)
            ->count();
        
        // Calculate percentage changes
        $revenueChange = $previousMonthRevenue > 0 
            ? (($monthlyRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100 
            : 0;
        
        $ordersChange = $previousMonthOrders > 0 
            ? (($monthlyOrders - $previousMonthOrders) / $previousMonthOrders) * 100 
            : 0;
        
        // Total customers
        $totalCustomers = User::where('role', 'buyer')->count();
        
        return [
            'total_products' => $totalProducts,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'low_stock_count' => $lowStockCount,
            'monthly_revenue' => $monthlyRevenue,
            'monthly_orders' => $monthlyOrders,
            'revenue_change' => round($revenueChange, 1),
            'orders_change' => round($ordersChange, 1),
            'total_customers' => $totalCustomers,
        ];
    }

    /**
     * Get recent orders for the dashboard.
     */
    private function getRecentOrders()
    {
        return Order::with(['user', 'orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get products with low stock.
     */
    private function getLowStockProducts()
    {
        return Product::with('inventory')
            ->whereHas('inventory', function ($query) {
                $query->whereRaw('quantity <= low_stock_threshold');
            })
            ->limit(5)
            ->get();
    }
}