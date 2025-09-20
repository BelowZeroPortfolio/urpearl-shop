<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Rating;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_correct_fillable_attributes()
    {
        $fillable = [
            'name', 'email', 'password', 'avatar', 'role', 'provider', 'provider_id'
        ];

        $user = new User();
        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_casts_role_to_enum()
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertEquals(UserRole::ADMIN, $user->role);
    }

    public function test_is_admin_method_returns_true_for_admin_user()
    {
        $adminUser = User::factory()->admin()->create();
        $buyerUser = User::factory()->buyer()->create();

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($buyerUser->isAdmin());
    }

    public function test_is_buyer_method_returns_true_for_buyer_user()
    {
        $adminUser = User::factory()->admin()->create();
        $buyerUser = User::factory()->buyer()->create();

        $this->assertFalse($adminUser->isBuyer());
        $this->assertTrue($buyerUser->isBuyer());
    }

    public function test_make_admin_method_updates_role()
    {
        $user = User::factory()->buyer()->create();
        
        $this->assertEquals(UserRole::BUYER, $user->role);
        
        $user->makeAdmin();
        
        $this->assertEquals(UserRole::ADMIN, $user->fresh()->role);
    }

    public function test_make_buyer_method_updates_role()
    {
        $user = User::factory()->admin()->create();
        
        $this->assertEquals(UserRole::ADMIN, $user->role);
        
        $user->makeBuyer();
        
        $this->assertEquals(UserRole::BUYER, $user->fresh()->role);
    }

    public function test_user_has_many_cart_items()
    {
        $user = User::factory()->create();
        CartItem::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->cartItems);
        $this->assertInstanceOf(CartItem::class, $user->cartItems->first());
    }

    public function test_user_has_many_orders()
    {
        $user = User::factory()->create();
        Order::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    public function test_user_has_many_ratings()
    {
        $user = User::factory()->create();
        Rating::factory()->count(4)->create(['user_id' => $user->id]);

        $this->assertCount(4, $user->ratings);
        $this->assertInstanceOf(Rating::class, $user->ratings->first());
    }

    public function test_user_has_many_notifications()
    {
        $user = User::factory()->create();
        Notification::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertCount(5, $user->notifications);
        $this->assertInstanceOf(Notification::class, $user->notifications->first());
    }

    public function test_user_factory_creates_buyer_by_default()
    {
        $user = User::factory()->create();
        
        $this->assertEquals(UserRole::BUYER, $user->role);
    }

    public function test_user_factory_can_create_admin()
    {
        $user = User::factory()->admin()->create();
        
        $this->assertEquals(UserRole::ADMIN, $user->role);
    }

    public function test_user_factory_can_create_with_google_oauth()
    {
        $user = User::factory()->withGoogle()->create();
        
        $this->assertEquals('google', $user->provider);
        $this->assertNotNull($user->provider_id);
        $this->assertNull($user->password);
    }
}