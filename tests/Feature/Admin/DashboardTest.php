<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\Category;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a default category for products if it doesn't exist
        if (!Category::where('slug', 'test-category')->exists()) {
            Category::factory()->create(['name' => 'Test Category', 'slug' => 'test-category']);
        }
    }

    /** @test */
    public function admin_can_access_dashboard()
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'email' => 'admin1@test.com'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function buyer_cannot_access_admin_dashboard()
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'email' => 'buyer1@test.com'
        ]);

        $response = $this->actingAs($buyer)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function dashboard_displays_correct_kpis()
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'email' => 'admin2@test.com'
        ]);
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'email' => 'buyer2@test.com'
        ]);
        
        // Create a single category to avoid conflicts
        $category = Category::firstOrCreate([
            'slug' => 'test-category'
        ], [
            'name' => 'Test Category'
        ]);
        
        // Create test data
        $products = Product::factory()->count(5)->create([
            'category_id' => $category->id
        ]);
        $orders = Order::factory()->count(3)->create([
            'user_id' => $buyer->id,
            'status' => OrderStatus::PAID, 
            'total_amount' => 100.00
        ]);
        
        // Create inventory with low stock
        foreach ($products as $product) {
            Inventory::factory()->create([
                'product_id' => $product->id,
                'quantity' => 2,
                'low_stock_threshold' => 5
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('kpis');
        
        $kpis = $response->viewData('kpis');
        
        $this->assertEquals(5, $kpis['total_products']);
        $this->assertEquals(3, $kpis['total_orders']);
        $this->assertEquals(300.00, $kpis['total_revenue']);
        $this->assertEquals(5, $kpis['low_stock_count']); // All products have low stock
    }

    /** @test */
    public function dashboard_displays_recent_orders()
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'email' => 'admin@test.com'
        ]);
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER,
            'email' => 'buyer@test.com'
        ]);
        
        // Create recent orders
        Order::factory()->count(5)->create([
            'user_id' => $buyer->id,
            'status' => OrderStatus::PAID
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('recentOrders');
        
        $recentOrders = $response->viewData('recentOrders');
        $this->assertCount(5, $recentOrders);
    }

    /** @test */
    public function dashboard_displays_low_stock_products()
    {
        $admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'email' => 'admin3@test.com'
        ]);
        
        // Create a single category to avoid conflicts
        $category = Category::firstOrCreate([
            'slug' => 'test-category'
        ], [
            'name' => 'Test Category'
        ]);
        
        // Create products with low stock
        $lowStockProducts = Product::factory()->count(3)->create([
            'category_id' => $category->id
        ]);
        foreach ($lowStockProducts as $product) {
            Inventory::factory()->create([
                'product_id' => $product->id,
                'quantity' => 2,
                'low_stock_threshold' => 5
            ]);
        }
        
        // Create products with normal stock
        $normalStockProducts = Product::factory()->count(2)->create([
            'category_id' => $category->id
        ]);
        foreach ($normalStockProducts as $product) {
            Inventory::factory()->create([
                'product_id' => $product->id,
                'quantity' => 10,
                'low_stock_threshold' => 5
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('lowStockProducts');
        
        $lowStockProducts = $response->viewData('lowStockProducts');
        $this->assertCount(3, $lowStockProducts);
    }
}