<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Pearl Necklaces',
            'Pearl Earrings', 
            'Pearl Bracelets',
            'Pearl Rings',
            'Pearl Sets',
            'Cultured Pearls',
            'Freshwater Pearls',
            'Saltwater Pearls',
            'Akoya Pearls',
            'Tahitian Pearls',
            'South Sea Pearls',
            'Pearl Pendants',
            'Vintage Pearls',
            'Modern Pearl Jewelry',
            'Bridal Pearl Collection',
            'Custom Pearl Jewelry',
            'Pearl Accessories',
            'Pearl Charms',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}