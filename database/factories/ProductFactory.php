<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pearlTypes = ['Akoya', 'Tahitian', 'South Sea', 'Freshwater', 'Cultured'];
        $jewelryTypes = ['Necklace', 'Earrings', 'Bracelet', 'Ring', 'Pendant', 'Set'];
        $styles = ['Classic', 'Modern', 'Vintage', 'Elegant', 'Luxury', 'Traditional'];
        
        $pearlType = $this->faker->randomElement($pearlTypes);
        $jewelryType = $this->faker->randomElement($jewelryTypes);
        $style = $this->faker->randomElement($styles);
        
        $name = "{$style} {$pearlType} Pearl {$jewelryType}";

        // Generate realistic descriptions
        $descriptions = [
            "Exquisite {$pearlType} pearls carefully selected for their lustrous beauty and perfect round shape.",
            "Handcrafted {$jewelryType} featuring premium {$pearlType} pearls with exceptional nacre quality.",
            "Timeless {$style} design showcasing the natural elegance of {$pearlType} pearls.",
            "Premium quality {$pearlType} pearls set in a {$style} {$jewelryType} perfect for any occasion.",
        ];

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'description' => $this->faker->randomElement($descriptions) . ' ' . $this->faker->sentence(10),
            'price' => $this->faker->randomFloat(2, 50, 2000),
            'sku' => 'PEARL-' . $this->faker->unique()->numberBetween(1000, 9999),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'image' => $this->faker->imageUrl(400, 400, 'fashion', true, 'pearl jewelry'),
        ];
    }

    /**
     * Create a product with a specific category.
     *
     * @param int $categoryId
     * @return $this
     */
    public function forCategory(int $categoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Create an expensive product.
     *
     * @return $this
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 1000, 5000),
        ]);
    }

    /**
     * Create an affordable product.
     *
     * @return $this
     */
    public function affordable(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 50, 300),
        ]);
    }

    /**
     * Create a product with no image.
     *
     * @return $this
     */
    public function withoutImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => null,
        ]);
    }
}