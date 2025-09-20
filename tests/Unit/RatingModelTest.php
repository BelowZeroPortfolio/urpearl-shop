<?php

namespace Tests\Unit;

use App\Models\Rating;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_rating_has_correct_fillable_attributes()
    {
        $fillable = ['user_id', 'product_id', 'rating', 'review', 'is_verified_purchase'];

        $rating = new Rating();
        $this->assertEquals($fillable, $rating->getFillable());
    }

    public function test_rating_casts_rating_to_integer()
    {
        $rating = Rating::factory()->create(['rating' => '4']);
        
        $this->assertIsInt($rating->rating);
        $this->assertEquals(4, $rating->rating);
    }

    public function test_rating_casts_is_verified_purchase_to_boolean()
    {
        $rating = Rating::factory()->create(['is_verified_purchase' => 1]);
        
        $this->assertIsBool($rating->is_verified_purchase);
        $this->assertTrue($rating->is_verified_purchase);
    }

    public function test_rating_belongs_to_user()
    {
        $user = User::factory()->create();
        $rating = Rating::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $rating->user);
        $this->assertEquals($user->id, $rating->user->id);
    }

    public function test_rating_belongs_to_product()
    {
        $product = Product::factory()->create();
        $rating = Rating::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $rating->product);
        $this->assertEquals($product->id, $rating->product->id);
    }

    public function test_validation_rules_returns_correct_array()
    {
        $expectedRules = [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'product_id' => 'required|exists:products,id',
        ];

        $this->assertEquals($expectedRules, Rating::validationRules());
    }

    public function test_is_valid_rating_returns_true_for_valid_ratings()
    {
        $validRatings = [1, 2, 3, 4, 5];

        foreach ($validRatings as $ratingValue) {
            $rating = Rating::factory()->create(['rating' => $ratingValue]);
            $this->assertTrue($rating->isValidRating(), "Rating {$ratingValue} should be valid");
        }
    }

    public function test_is_valid_rating_returns_false_for_invalid_ratings()
    {
        $invalidRatings = [0, 6, -1, 10];

        foreach ($invalidRatings as $ratingValue) {
            $rating = Rating::factory()->make(['rating' => $ratingValue]);
            $this->assertFalse($rating->isValidRating(), "Rating {$ratingValue} should be invalid");
        }
    }

    public function test_rating_factory_creates_valid_rating()
    {
        $rating = Rating::factory()->create();
        
        $this->assertInstanceOf(Rating::class, $rating);
        $this->assertTrue($rating->isValidRating());
        $this->assertGreaterThanOrEqual(1, $rating->rating);
        $this->assertLessThanOrEqual(5, $rating->rating);
    }

    public function test_rating_factory_can_create_verified_purchase()
    {
        $rating = Rating::factory()->verifiedPurchase()->create();
        
        $this->assertTrue($rating->is_verified_purchase);
    }

    public function test_rating_factory_can_create_unverified_purchase()
    {
        $rating = Rating::factory()->unverifiedPurchase()->create();
        
        $this->assertFalse($rating->is_verified_purchase);
    }
}