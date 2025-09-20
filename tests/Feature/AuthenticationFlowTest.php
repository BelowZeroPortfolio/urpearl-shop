<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_unauthenticated_user_can_access_login_page()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Welcome to UrPearl SHOP');
        $response->assertSee('Continue with Google');
        $response->assertViewIs('auth.login');
    }

    public function test_authenticated_user_redirected_from_login_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/login');
        
        $response->assertRedirect('/');
    }

    public function test_google_oauth_redirect_initiates_correctly()
    {
        $response = $this->get('/auth/google');

        $response->assertStatus(302);
        $response->assertRedirect();
        // Should redirect to Google OAuth URL
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_google_callback_creates_new_buyer_user()
    {
        $this->mockSocialiteUser([
            'id' => '123456789',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'avatar' => 'https://example.com/avatar.jpg'
        ]);

        $response = $this->get('/auth/google/callback');

        // Assert user was created with correct data
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'provider' => 'google',
            'provider_id' => '123456789',
            'role' => UserRole::BUYER->value,
            'avatar' => 'https://example.com/avatar.jpg'
        ]);

        // Assert user is authenticated and redirected
        $user = User::where('email', 'john@example.com')->first();
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    public function test_google_callback_logs_in_existing_user()
    {
        // Create existing user
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'provider' => 'google',
            'provider_id' => '987654321',
            'name' => 'Existing User'
        ]);

        $this->mockSocialiteUser([
            'id' => '987654321',
            'name' => 'Updated Name',
            'email' => 'existing@example.com',
            'avatar' => 'https://example.com/new-avatar.jpg'
        ]);

        $response = $this->get('/auth/google/callback');

        // Assert user data was updated
        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'email' => 'existing@example.com',
            'name' => 'Updated Name',
            'avatar' => 'https://example.com/new-avatar.jpg'
        ]);

        // Assert user is authenticated
        $this->assertAuthenticatedAs($existingUser->fresh());
        $response->assertRedirect('/');
    }

    public function test_admin_user_redirected_to_dashboard_after_login()
    {
        // Create admin user
        $adminUser = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'provider' => 'google',
            'provider_id' => 'admin123'
        ]);

        $this->mockSocialiteUser([
            'id' => 'admin123',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'avatar' => 'https://example.com/admin-avatar.jpg'
        ]);

        $response = $this->get('/auth/google/callback');

        // Assert admin is redirected to dashboard
        $this->assertAuthenticatedAs($adminUser->fresh());
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_buyer_user_redirected_to_home_after_login()
    {
        $buyerUser = User::factory()->buyer()->create([
            'email' => 'buyer@example.com',
            'provider' => 'google',
            'provider_id' => 'buyer123'
        ]);

        $this->mockSocialiteUser([
            'id' => 'buyer123',
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'avatar' => 'https://example.com/buyer-avatar.jpg'
        ]);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticatedAs($buyerUser->fresh());
        $response->assertRedirect('/');
    }

    public function test_logout_clears_authentication()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/logout');
        
        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_logout_redirects_unauthenticated_user()
    {
        $response = $this->post('/logout');
        
        $response->assertRedirect('/login');
    }

    public function test_google_callback_handles_missing_user_data()
    {
        $this->mockSocialiteUser([
            'id' => '123456789',
            'name' => null,
            'email' => 'test@example.com',
            'avatar' => null
        ]);

        $response = $this->get('/auth/google/callback');

        // Should still create user with available data
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'provider_id' => '123456789',
            'role' => UserRole::BUYER->value
        ]);

        $response->assertRedirect('/');
    }

    public function test_google_callback_handles_duplicate_email_different_provider()
    {
        // Create user with same email but different provider
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'provider' => 'facebook',
            'provider_id' => 'facebook123'
        ]);

        $this->mockSocialiteUser([
            'id' => 'google123',
            'name' => 'Google User',
            'email' => 'duplicate@example.com',
            'avatar' => 'https://example.com/avatar.jpg'
        ]);

        $response = $this->get('/auth/google/callback');

        // Should create new user with Google provider
        $this->assertDatabaseHas('users', [
            'email' => 'duplicate@example.com',
            'provider' => 'google',
            'provider_id' => 'google123'
        ]);

        // Should have 2 users with same email but different providers
        $this->assertEquals(2, User::where('email', 'duplicate@example.com')->count());
    }

    public function test_authentication_middleware_protects_routes()
    {
        // Test protected route without authentication
        $response = $this->get('/cart');
        $response->assertRedirect('/login');

        // Test protected route with authentication
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/cart');
        $response->assertStatus(200);
    }

    public function test_admin_middleware_protects_admin_routes()
    {
        $buyerUser = User::factory()->buyer()->create();
        $adminUser = User::factory()->admin()->create();

        // Test admin route with buyer user
        $response = $this->actingAs($buyerUser)->get('/admin/dashboard');
        $response->assertStatus(403);

        // Test admin route with admin user
        $response = $this->actingAs($adminUser)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Mock Socialite user for testing
     */
    private function mockSocialiteUser(array $userData): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($userData['id']);
        $socialiteUser->shouldReceive('getName')->andReturn($userData['name'] ?? null);
        $socialiteUser->shouldReceive('getEmail')->andReturn($userData['email']);
        $socialiteUser->shouldReceive('getAvatar')->andReturn($userData['avatar'] ?? null);

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }
}