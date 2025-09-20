<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 3);
        $price = $this->faker->randomFloat(2, 50, 500);
        $total = $quantity * $price;

        return [
            'order_id' => Order::inRandomOrder()->first()?->id ?? Order::factory(),
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'total' => $total,
        ];
    }

    /**
     * Create an order item for a specific order.
     *
     * @param int $orderId
     * @return $this
     */
    public function forOrder(int $orderId): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $orderId,
        ]);
    }

    /**
     * Create an order item for a specific product.
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
     * Create an order item with specific price and quantity.
     *
     * @param float $price
     * @param int $quantity
     * @return $this
     */
    public function withPriceAndQuantity(float $price, int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
            'quantity' => $quantity,
            'total' => $price * $quantity,
        ]);
    }
}