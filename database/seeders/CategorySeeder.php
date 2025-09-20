<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'T-Shirts',
                'slug' => 't-shirts',
            ],
            [
                'name' => 'Shirts',
                'slug' => 'shirts',
            ],
            [
                'name' => 'Jeans',
                'slug' => 'jeans',
            ],
            [
                'name' => 'Dresses',
                'slug' => 'dresses',
            ],
            [
                'name' => 'Jackets',
                'slug' => 'jackets',
            ],
            [
                'name' => 'Activewear',
                'slug' => 'activewear',
            ],
            [
                'name' => 'Loungewear',
                'slug' => 'loungewear',
            ],
            [
                'name' => 'Swimwear',
                'slug' => 'swimwear',
            ],
            [
                'name' => 'Formal Wear',
                'slug' => 'formal-wear',
            ],
            [
                'name' => 'Outerwear',
                'slug' => 'outerwear',
            ],
            [
                'name' => 'Skirts',
                'slug' => 'skirts',
            ],
            [
                'name' => 'Shorts',
                'slug' => 'shorts',
            ],
            [
                'name' => 'Jumpsuits',
                'slug' => 'jumpsuits',
            ],
            [
                'name' => 'Vintage Clothing',
                'slug' => 'vintage-clothing',
            ],
            [
                'name' => 'Plus Size',
                'slug' => 'plus-size',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        $this->command->info('Clothing categories created successfully!');
    }
}