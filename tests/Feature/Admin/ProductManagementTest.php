<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN
        ]);
        
        // Create a category
        $this->category = Category::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);
    }

    /** @test */
    public function admin_can_view_products_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.index');
    }

    /** @test */
    public function admin_can_view_create_product_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.create');
        $response->assertViewHas('categories');
    }

    /** @test */
    public function admin_can_create_product()
    {
        Storage::fake('public');

        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product description',
            'price' => 99.99,
            'sku' => 'TEST-001',
            'category_id' => $this->category->id,
            'image' => UploadedFile::fake()->image('product.jpg')
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), $productData);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'category_id' => $this->category->id
        ]);

        $product = Product::first();
        $this->assertTrue(Storage::disk('public')->exists($product->image));
    }

    /** @test */
    public function admin_can_view_product_details()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.show', $product));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.show');
        $response->assertViewHas('product', $product);
    }

    /** @test */
    public function admin_can_view_edit_product_form()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.edit');
        $response->assertViewHas('product', $product);
        $response->assertViewHas('categories');
    }

    /** @test */
    public function admin_can_update_product()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Original Name',
            'price' => 50.00
        ]);

        $updateData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated description',
            'price' => 75.00,
            'sku' => $product->sku,
            'category_id' => $this->category->id
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), $updateData);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 75.00
        ]);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_product_management()
    {
        $buyer = User::factory()->create([
            'role' => UserRole::BUYER
        ]);

        $response = $this->actingAs($buyer)
            ->get(route('admin.products.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function product_validation_works()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), []);

        $response->assertSessionHasErrors([
            'name', 'description', 'price', 'sku', 'category_id'
        ]);
    }
}