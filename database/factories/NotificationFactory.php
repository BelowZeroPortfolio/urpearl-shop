<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(NotificationType::cases());
        
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'type' => $type,
            'title' => $this->getTitleForType($type),
            'message' => $this->getMessageForType($type),
            'payload' => $this->getPayloadForType($type),
            'read_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Create a notification for a specific user.
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
     * Create a low stock notification.
     *
     * @return $this
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NotificationType::LOW_STOCK,
            'title' => 'Low Stock Alert',
            'message' => 'Product "' . $this->faker->words(3, true) . '" is running low on stock.',
            'payload' => [
                'product_id' => $this->faker->numberBetween(1, 100),
                'current_stock' => $this->faker->numberBetween(1, 5),
                'threshold' => $this->faker->numberBetween(5, 10),
            ],
        ]);
    }

    /**
     * Create an order created notification.
     *
     * @return $this
     */
    public function orderCreated(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NotificationType::ORDER_CREATED,
            'title' => 'New Order Received',
            'message' => 'A new order has been placed by ' . $this->faker->name(),
            'payload' => [
                'order_id' => $this->faker->numberBetween(1, 1000),
                'customer_name' => $this->faker->name(),
                'total_amount' => $this->faker->randomFloat(2, 50, 500),
            ],
        ]);
    }

    /**
     * Create an order status changed notification.
     *
     * @return $this
     */
    public function orderStatusChanged(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => NotificationType::ORDER_STATUS_CHANGED,
            'title' => 'Order Status Updated',
            'message' => 'Order status has been changed to ' . $this->faker->randomElement(['paid', 'shipped', 'cancelled']),
            'payload' => [
                'order_id' => $this->faker->numberBetween(1, 1000),
                'old_status' => $this->faker->randomElement(['pending', 'paid']),
                'new_status' => $this->faker->randomElement(['paid', 'shipped', 'cancelled']),
            ],
        ]);
    }

    /**
     * Create an unread notification.
     *
     * @return $this
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Create a read notification.
     *
     * @return $this
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Get title for notification type.
     *
     * @param NotificationType $type
     * @return string
     */
    private function getTitleForType(NotificationType $type): string
    {
        return match ($type) {
            NotificationType::LOW_STOCK => 'Low Stock Alert',
            NotificationType::ORDER_CREATED => 'New Order Received',
            NotificationType::ORDER_STATUS_CHANGED => 'Order Status Updated',
        };
    }

    /**
     * Get message for notification type.
     *
     * @param NotificationType $type
     * @return string
     */
    private function getMessageForType(NotificationType $type): string
    {
        return match ($type) {
            NotificationType::LOW_STOCK => 'Product "' . $this->faker->words(3, true) . '" is running low on stock.',
            NotificationType::ORDER_CREATED => 'A new order has been placed by ' . $this->faker->name(),
            NotificationType::ORDER_STATUS_CHANGED => 'Order status has been changed to ' . $this->faker->randomElement(['paid', 'shipped', 'cancelled']),
        };
    }

    /**
     * Get payload for notification type.
     *
     * @param NotificationType $type
     * @return array
     */
    private function getPayloadForType(NotificationType $type): array
    {
        return match ($type) {
            NotificationType::LOW_STOCK => [
                'product_id' => $this->faker->numberBetween(1, 100),
                'current_stock' => $this->faker->numberBetween(1, 5),
                'threshold' => $this->faker->numberBetween(5, 10),
            ],
            NotificationType::ORDER_CREATED => [
                'order_id' => $this->faker->numberBetween(1, 1000),
                'customer_name' => $this->faker->name(),
                'total_amount' => $this->faker->randomFloat(2, 50, 500),
            ],
            NotificationType::ORDER_STATUS_CHANGED => [
                'order_id' => $this->faker->numberBetween(1, 1000),
                'old_status' => $this->faker->randomElement(['pending', 'paid']),
                'new_status' => $this->faker->randomElement(['paid', 'shipped', 'cancelled']),
            ],
        };
    }
}