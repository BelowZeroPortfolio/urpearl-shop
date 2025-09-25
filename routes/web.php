<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckoutController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals'])->name('products.new-arrivals');
Route::get('/products/best-sellers', [ProductController::class, 'bestSellers'])->name('products.best-sellers');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Rating routes - public viewing, authenticated for creating/editing
Route::get('/products/{product}/ratings', [RatingController::class, 'index'])->name('ratings.index');
Route::get('/products/{product}/ratings/data', [RatingController::class, 'getRatings'])->name('ratings.data');
Route::get('/products/{product}/can-review', [RatingController::class, 'canReview'])->name('ratings.can-review');

// Authentication routes
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.perform');
    
    // Registration routes
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.perform');
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout')->middleware('auth');

// Checkout routes
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::get('/checkout/summary', [CheckoutController::class, 'summary'])->name('checkout.summary');
    Route::get('/checkout/payment-form', [CheckoutController::class, 'paymentForm'])->name('checkout.payment.form');
    Route::post('/checkout/process', [CheckoutController::class, 'processPayment'])->name('checkout.process');
});

Route::prefix('auth')->group(function () {
    Route::get('/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

// Authenticated user routes
Route::middleware(['auth'])->group(function () {
    // Cart routes - available to all authenticated users
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/data', [CartController::class, 'data'])->name('cart.data');
    
    // Order routes - available to all authenticated users
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    
    // Buyer-specific routes
    Route::middleware(['can:place-orders'])->group(function () {
        Route::get('/checkout', function () {
            return view('checkout.index');
        })->name('checkout.index');
    });
    
    // Rating routes - authenticated users only
    Route::get('/products/{product}/ratings/create', [RatingController::class, 'create'])->name('ratings.create');
    Route::post('/products/{product}/ratings', [RatingController::class, 'store'])->name('ratings.store');
    Route::get('/products/{product}/ratings/{rating}/edit', [RatingController::class, 'edit'])->name('ratings.edit');
    Route::put('/products/{product}/ratings/{rating}', [RatingController::class, 'update'])->name('ratings.update');
    Route::delete('/products/{product}/ratings/{rating}', [RatingController::class, 'destroy'])->name('ratings.destroy');
    
    // Profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
        Route::put('/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('password');
    });
});

// Admin routes - protected by admin middleware and gates
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard - requires view-dashboard gate
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:view-dashboard')
        ->name('dashboard');
    
    // Product management routes - requires manage-products gate
    Route::middleware(['can:manage-products'])->group(function () {
        Route::resource('products', AdminProductController::class);
        Route::post('/products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    });
    
    // Inventory management routes - requires manage-inventory gate
    Route::middleware(['can:manage-inventory'])->group(function () {
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/{product}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::post('/inventory/bulk-update', [InventoryController::class, 'bulkUpdate'])->name('inventory.bulk-update');
        Route::post('/inventory/create-missing', [InventoryController::class, 'createMissingInventoryRecords'])->name('inventory.create-missing');
        Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
        Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
        Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    });
    
    // Order management routes - requires manage-orders gate
    Route::middleware(['can:manage-orders'])->group(function () {
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('/orders/{order}/details', [AdminOrderController::class, 'getOrderDetails'])->name('orders.details');
        Route::get('/orders/stats', [AdminOrderController::class, 'getStats'])->name('orders.stats');
    });
    
    // Notification management routes - requires manage-notifications gate
    Route::middleware(['can:manage-notifications'])->group(function () {
        Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/api', [\App\Http\Controllers\Admin\NotificationController::class, 'getNotifications'])->name('notifications.api');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::delete('/notifications/{notification}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('notifications.destroy');
    });
});

// Development routes - only available in local environment
if (app()->environment('local')) {
    Route::prefix('dev')->name('dev.')->group(function () {
        Route::get('/mail-test', [\App\Http\Controllers\Dev\MailTestController::class, 'index'])->name('mail-test');
        Route::get('/mail-test/config', [\App\Http\Controllers\Dev\MailTestController::class, 'testMailConfig'])->name('mail-test.config');
        Route::get('/mail-test/preview/low-stock-alert', [\App\Http\Controllers\Dev\MailTestController::class, 'previewLowStockAlert'])->name('mail-test.preview.low-stock');
        Route::get('/mail-test/preview/order-confirmation', [\App\Http\Controllers\Dev\MailTestController::class, 'previewOrderConfirmation'])->name('mail-test.preview.order');
        Route::post('/mail-test/send/low-stock-alert', [\App\Http\Controllers\Dev\MailTestController::class, 'sendTestLowStockAlert'])->name('mail-test.send.low-stock');
        Route::post('/mail-test/send/order-confirmation', [\App\Http\Controllers\Dev\MailTestController::class, 'sendTestOrderConfirmation'])->name('mail-test.send.order');
        
        // Tailwind CSS test page
        Route::get('/tailwind-test', function () {
            return view('test-tailwind');
        })->name('tailwind-test');
    });
}
