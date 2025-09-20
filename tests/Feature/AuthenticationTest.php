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

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_displays_correctly()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Welcome to UrPearl SHOP');
        $response->assertSee('Continue with Google');
    }

    public function test_google_redirect_works()
    {
        $response = $this->get('/auth/google');

        $response->assertStatus(302);
        $response->assertRedirect();
    }

    public function test_google_callback_creates_new_user()
    {
        // Mock the Socialite facade
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        // Make the callback request
        $response = $this->get('/auth/google/callback');

        // Assert user was created
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'provider' => 'google',
            'provider_id' => '123456789',
            'role' => UserRole::BUYER->value,
        ]);

        // Assert user is redirected to home
        $response->assertRedirect('/');
    }

    public function test_google_callback_logs_in_existing_user()
    {
        // Create existing user
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'provider' => 'google',
            'provider_id' => '123456789',
        ]);

        // Mock the Socialite facade
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456789');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        // Make the callback request
        $response = $this->get('/auth/google/callback');

        // Assert user is authenticated
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/');
    }

    public function test_admin_user_redirected_to_dashboard()
    {
        // Mock the Socialite facade for admin user
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('admin123');
        $socialiteUser->shouldReceive('getName')->andReturn('Admin User');
        $socialiteUser->shouldReceive('getEmail')->andReturn('admin@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/admin-avatar.jpg');

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        // Create admin user
        User::factory()->create([
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN,
        ]);

        // Make the callback request
        $response = $this->get('/auth/google/callback');

        // Assert admin is redirected to dashboard
        $response->assertRedirect('/admin/dashboard');
    }

    public function test_authenticated_user_cannot_access_login_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/login');
        
        $response->assertRedirect('/');
    }

    public function test_logout_works()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/logout');
        
        $this->assertGuest();
        $response->assertRedirect('/');
    }
}