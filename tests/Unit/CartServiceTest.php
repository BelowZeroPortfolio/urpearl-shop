<?php

namespace Tests\Unit;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Inventory;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
    }

    public function test_can_add_product_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        $this->actingAs($user);

        $cartItem = $this->cartService->addToCart($product, 2);

        $this->assertInstanceOf(CartItem::class, $cartItem);
        $this->assertEquals($user->id, $cartItem->user_id);
        $this->assertEquals($product->id, $cartItem->product_id);
        $this->assertEquals(2, $cartItem->quantity);
    }

    public function test_can_update_existing_cart_item_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'low_stock_threshold' => 5
        ]);

        $this->actingAs($user);

        // Add product first time
        $this->cartService->addToCart($product, 2);
        
        // Add same product again
        $cartItem = $this->cartService->addToCart($product, 3);

        $this->assertEquals(5, $cartItem->quantity);
        $this->assertEquals(1, CartItem::where('user_id', $user->id)->count());
    }

    public function test_throws_exception_when_insufficient_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'low_stock_threshold' => 1
        ]);

        $this->actingAs($user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock available');

        $this->cartService->addToCart($product, 5);
    }

    public function test_can_get_cart_items()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        Inventory::factory()->create([
            'product_id' => $product1->id,
            'quantity' => 10
        ]);
        Inventory::factory()->create([
            'product_id' => $product2->id,
            'quantity' => 10
        ]);

        $this->actingAs($user);

        $this->cartService->addToCart($product1, 2);
        $this->cartService->addToCart($product2, 1);

        $cartItems = $this->cartService->getCartItems();

        $this->assertCount(2, $cartItems);
    }

    public function test_can_calculate_cart_total()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 100.00]);
        $product2 = Product::factory()->create(['price' => 50.00]);
        
        Inventory::factory()->create([
            'product_id' => $product1->id,
            'quantity' => 10
        ]);
        Inventory::factory()->create([
            'product_id' => $product2->id,
            'quantity' => 10
        ]);

        $this->actingAs($user);

        $this->cartService->addToCart($product1, 2); // 200.00
        $this->cartService->addToCart($product2, 1); // 50.00

        $total = $this->cartService->getCartTotal();

        $this->assertEquals(250.00, $total);
    }

    public function test_can_clear_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $this->actingAs($user);

        $this->cartService->addToCart($product, 2);
        $this->assertEquals(1, CartItem::where('user_id', $user->id)->count());

        $this->cartService->clearCart();
        $this->assertEquals(0, CartItem::where('user_id', $user->id)->count());
    }
}