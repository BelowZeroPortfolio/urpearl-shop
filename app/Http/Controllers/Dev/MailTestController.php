<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Mail\LowStockAlert;
use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailTestController extends Controller
{
    /**
     * Display mail testing interface (only in development)
     */
    public function index()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        return view('dev.mail-test');
    }

    /**
     * Preview low stock alert email
     */
    public function previewLowStockAlert()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        // Get a sample product and admin user
        $product = Product::with('inventory')->first();
        $admin = User::where('role', 'admin')->first();

        if (!$product || !$admin) {
            return response()->json([
                'error' => 'No sample data available. Please seed the database first.'
            ], 400);
        }

        $mailable = new LowStockAlert($product, $admin);
        
        return $mailable->render();
    }

    /**
     * Preview order confirmation email
     */
    public function previewOrderConfirmation()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        // Get a sample order
        $order = Order::with(['orderItems.product', 'user'])->first();

        if (!$order) {
            return response()->json([
                'error' => 'No sample order available. Please create an order first.'
            ], 400);
        }

        $mailable = new OrderConfirmation($order);
        
        return $mailable->render();
    }

    /**
     * Send test low stock alert email
     */
    public function sendTestLowStockAlert(Request $request)
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        $request->validate([
            'email' => 'required|email'
        ]);

        $product = Product::with('inventory')->first();
        $admin = User::where('role', 'admin')->first();

        if (!$product || !$admin) {
            return response()->json([
                'error' => 'No sample data available. Please seed the database first.'
            ], 400);
        }

        try {
            Mail::to($request->email)->send(new LowStockAlert($product, $admin));
            
            return response()->json([
                'success' => true,
                'message' => 'Low stock alert email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test order confirmation email
     */
    public function sendTestOrderConfirmation(Request $request)
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        $request->validate([
            'email' => 'required|email'
        ]);

        $order = Order::with(['orderItems.product', 'user'])->first();

        if (!$order) {
            return response()->json([
                'error' => 'No sample order available. Please create an order first.'
            ], 400);
        }

        try {
            Mail::to($request->email)->send(new OrderConfirmation($order));
            
            return response()->json([
                'success' => true,
                'message' => 'Order confirmation email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test mail configuration
     */
    public function testMailConfig()
    {
        if (!app()->environment('local')) {
            abort(404);
        }

        $config = [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username' => config('mail.mailers.smtp.username'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];

        return response()->json([
            'mail_config' => $config,
            'queue_connection' => config('queue.default'),
        ]);
    }
}