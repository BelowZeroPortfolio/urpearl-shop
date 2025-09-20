<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_correct_fillable_attributes()
    {
        $fillable = ['name', 'slug'];

        $category = new Category();
        $this->assertEquals($fillable, $category->getFillable());
    }

    public function test_category_auto_generates_slug_on_creation()
    {
        $category = Category::factory()->create(['name' => 'Pearl Necklaces']);
        
        $this->assertEquals('pearl-necklaces', $category->slug);
    }

    public function test_category_updates_slug_when_name_changes()
    {
        $category = Category::factory()->create(['name' => 'Original Category']);
        $originalSlug = $category->slug;
        
        // Clear the slug to trigger auto-generation
        $category->update(['name' => 'New Category Name', 'slug' => '']);
        
        $this->assertNotEquals($originalSlug, $category->fresh()->slug);
        $this->assertEquals('new-category-name', $category->fresh()->slug);
    }

    public function test_category_does_not_override_existing_slug()
    {
        $category = Category::factory()->create([
            'name' => 'Pearl Necklaces',
            'slug' => 'custom-slug'
        ]);
        
        $this->assertEquals('custom-slug', $category->slug);
    }

    public function test_category_has_many_products()
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->products);
        $this->assertInstanceOf(Product::class, $category->products->first());
    }

    public function test_category_factory_creates_valid_category()
    {
        $category = Category::factory()->create();
        
        $this->assertInstanceOf(Category::class, $category);
        $this->assertNotEmpty($category->name);
        $this->assertNotEmpty($category->slug);
    }

    public function test_category_slug_handles_special_characters()
    {
        $category = Category::factory()->create(['name' => 'Pearl & Diamond Jewelry!', 'slug' => '']);
        
        $this->assertStringContainsString('pearl', strtolower($category->slug));
        $this->assertStringContainsString('diamond', strtolower($category->slug));
        $this->assertStringContainsString('jewelry', strtolower($category->slug));
    }

    public function test_category_slug_handles_spaces_and_case()
    {
        $category = Category::factory()->create(['name' => 'LUXURY Pearl Necklaces', 'slug' => '']);
        
        $this->assertStringContainsString('luxury', strtolower($category->slug));
        $this->assertStringContainsString('pearl', strtolower($category->slug));
        $this->assertStringContainsString('necklaces', strtolower($category->slug));
    }
}