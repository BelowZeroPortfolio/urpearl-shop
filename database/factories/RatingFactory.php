<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rating::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = $this->faker->numberBetween(1, 5);
        
        // Generate realistic reviews based on rating
        $reviews = [
            5 => [
                'Absolutely stunning! The pearls are beautiful and the quality is exceptional.',
                'Perfect in every way. Exceeded my expectations!',
                'Amazing quality and fast shipping. Highly recommend!',
                'Beautiful pearls with excellent luster. Love it!',
            ],
            4 => [
                'Very nice quality, just as described. Happy with my purchase.',
                'Good product, well made. Minor imperfections but overall satisfied.',
                'Nice pearls, good value for money. Would buy again.',
                'Pretty good quality, delivery was quick.',
            ],
            3 => [
                'Decent quality for the price. Nothing special but acceptable.',
                'Average product. Met expectations but nothing more.',
                'Okay quality, some minor issues but usable.',
                'Fair quality, could be better but not bad.',
            ],
            2 => [
                'Not as described. Quality is below expectations.',
                'Disappointed with the quality. Expected better.',
                'Some issues with the product. Not entirely satisfied.',
                'Below average quality for the price paid.',
            ],
            1 => [
                'Very poor quality. Would not recommend.',
                'Terrible product. Complete waste of money.',
                'Extremely disappointed. Quality is awful.',
                'Do not buy this. Very poor quality.',
            ],
        ];

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'rating' => $rating,
            'review' => $this->faker->randomElement($reviews[$rating]),
            'is_verified_purchase' => $this->faker->boolean(80), // 80% chance of verified purchase
        ];
    }

    /**
     * Create a rating for a specific user.
     *
     * @param int $userId
     * @return $this
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Create a rating for a specific product.
     *
     * @param int $productId
     * @return $this
     */
    public function forProduct(int $productId): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $productId,
        ]);
    }

    /**
     * Create a 5-star rating.
     *
     * @return $this
     */
    public function fiveStars(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'review' => $this->faker->randomElement([
                'Absolutely stunning! The pearls are beautiful and the quality is exceptional.',
                'Perfect in every way. Exceeded my expectations!',
                'Amazing quality and fast shipping. Highly recommend!',
                'Beautiful pearls with excellent luster. Love it!',
            ]),
        ]);
    }

    /**
     * Create a 1-star rating.
     *
     * @return $this
     */
    public function oneStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 1,
            'review' => $this->faker->randomElement([
                'Very poor quality. Would not recommend.',
                'Terrible product. Complete waste of money.',
                'Extremely disappointed. Quality is awful.',
                'Do not buy this. Very poor quality.',
            ]),
        ]);
    }

    /**
     * Create a verified purchase rating.
     *
     * @return $this
     */
    public function verifiedPurchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified_purchase' => true,
        ]);
    }

    /**
     * Create a non-verified purchase rating.
     *
     * @return $this
     */
    public function unverifiedPurchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified_purchase' => false,
        ]);
    }
}