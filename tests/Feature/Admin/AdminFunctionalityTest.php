<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
use App\Enums\UserRole;
use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
        $this->buyer = User::factory()->buyer()->create();
    }

    public function test_admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Admin Dashboard');
    }

    public function test_buyer_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->buyer)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_from_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_dashboard_displays_correct_kpis()
    {
        // Create test data
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);
        
        $lowStockProduct = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create([
            'product_id' => $lowStockProduct->id,
            'quantity' => 2,
            'low_stock_threshold' => 5
        ]);

        Order::factory()->count(3)->create(['total_amount' => 100.00]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('6'); // Total products
        $response->assertSee('1'); // Low stock count
        $response->assertSee('3'); // Total orders
        $response->assertSee('₱300.00'); // Total revenue
    }

    public function test_admin_can_view_products_list()
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.index');
        
        foreach ($products as $product) {
            $response->assertSee($product->name);
            $response->assertSee($product->sku);
        }
    }

    public function test_admin_can_create_new_product()
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Test Pearl Necklace',
            'description' => 'Beautiful test necklace',
            'price' => 299.99,
            'sku' => 'TEST-001',
            'category_id' => $category->id,
            'image' => UploadedFile::fake()->image('product.jpg'),
            'quantity' => 10,
            'low_stock_threshold' => 5
        ];

        $response = $this->actingAs($this->admin)
            ->post('/admin/products', $productData);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success', 'Product created successfully!');

        // Assert product was created
        $this->assertDatabaseHas('products', [
            'name' => 'Test Pearl Necklace',
            'sku' => 'TEST-001',
            'price' => 299.99,
            'category_id' => $category->id
        ]);

        // Assert inventory was created
        $product = Product::where('sku', 'TEST-001')->first();
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        // Assert image was stored
        Storage::disk('public')->assertExists('products/' . $product->image);
    }

    public function test_admin_can_update_existing_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create(['product_id' => $product->id]);

        $updateData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated description',
            'price' => 399.99,
            'sku' => $product->sku,
            'category_id' => $category->id,
            'quantity' => 15,
            'low_stock_threshold' => 8
        ];

        $response = $this->actingAs($this->admin)
            ->put("/admin/products/{$product->id}", $updateData);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success', 'Product updated successfully!');

        // Assert product was updated
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 399.99
        ]);

        // Assert inventory was updated
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'quantity' => 15,
            'low_stock_threshold' => 8
        ]);
    }

    public function test_admin_can_delete_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Inventory::factory()->create(['product_id' => $product->id]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/products/{$product->id}");

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('success', 'Product deleted successfully!');

        // Assert product was soft deleted or removed
        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    public function test_admin_can_view_inventory_management()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'low_stock_threshold' => 5
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/inventory');

        $response->assertStatus(200);
        $response->assertViewIs('admin.inventory.index');
        $response->assertSee($product->name);
        $response->assertSee('3'); // Current quantity
        $response->assertSee('Low Stock'); // Status indicator
    }

    public function test_admin_can_update_inventory_levels()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5,
            'low_stock_threshold' => 3
        ]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/inventory/{$inventory->id}", [
                'quantity' => 20,
                'low_stock_threshold' => 8
            ]);

        $response->assertRedirect('/admin/inventory');
        $response->assertSessionHas('success', 'Inventory updated successfully!');

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity' => 20,
            'low_stock_threshold' => 8
        ]);
    }

    public function test_admin_can_view_orders_list()
    {
        $orders = Order::factory()->count(3)->create();
        
        foreach ($orders as $order) {
            OrderItem::factory()->create(['order_id' => $order->id]);
        }

        $response = $this->actingAs($this->admin)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
        
        foreach ($orders as $order) {
            $response->assertSee($order->id);
            $response->assertSee($order->status->value);
        }
    }

    public function test_admin_can_update_order_status()
    {
        $order = Order::factory()->pending()->create();

        $response = $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->id}", [
                'status' => OrderStatus::SHIPPED->value
            ]);

        $response->assertRedirect('/admin/orders');
        $response->assertSessionHas('success', 'Order status updated successfully!');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::SHIPPED->value
        ]);
    }

    public function test_admin_can_view_order_details()
    {
        $order = Order::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100.00
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.show');
        $response->assertSee($order->id);
        $response->assertSee($product->name);
        $response->assertSee('₱200.00'); // Total for order item
    }

    public function test_admin_can_view_notifications()
    {
        $notifications = Notification::factory()->count(3)->create([
            'user_id' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/notifications');

        $response->assertStatus(200);
        $response->assertViewIs('admin.notifications.index');
        
        foreach ($notifications as $notification) {
            $response->assertSee($notification->data['message'] ?? 'Notification');
        }
    }

    public function test_admin_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->admin->id,
            'read_at' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/notifications/{$notification->id}/read");

        $response->assertStatus(200);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_product_validation_prevents_invalid_data()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/products', [
                'name' => '', // Required field empty
                'price' => -10, // Invalid price
                'sku' => '', // Required field empty
                'category_id' => 99999, // Non-existent category
                'quantity' => -5, // Invalid quantity
                'low_stock_threshold' => -1 // Invalid threshold
            ]);

        $response->assertSessionHasErrors([
            'name',
            'price',
            'sku',
            'category_id',
            'quantity',
            'low_stock_threshold'
        ]);
    }

    public function test_admin_cannot_delete_product_with_existing_orders()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create();
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/products/{$product->id}");

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('error', 'Cannot delete product with existing orders');

        // Product should still exist
        $this->assertDatabaseHas('products', [
            'id' => $product->id
        ]);
    }

    public function test_buyer_cannot_access_admin_product_management()
    {
        $response = $this->actingAs($this->buyer)->get('/admin/products');
        $response->assertStatus(403);

        $response = $this->actingAs($this->buyer)->post('/admin/products', []);
        $response->assertStatus(403);
    }

    public function test_admin_search_functionality_works()
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create([
            'name' => 'Pearl Necklace',
            'category_id' => $category->id
        ]);
        $product2 = Product::factory()->create([
            'name' => 'Diamond Ring',
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/products?search=Pearl');

        $response->assertStatus(200);
        $response->assertSee($product1->name);
        $response->assertDontSee($product2->name);
    }
}