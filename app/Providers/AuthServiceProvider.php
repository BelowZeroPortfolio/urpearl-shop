<?php

namespace App\Providers;

use App\Enums\UserRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Admin-only gates
        Gate::define('admin-access', function ($user) {
            return $user->role === UserRole::ADMIN->value;
        });

        Gate::define('manage-products', function ($user) {
            return $user->role === UserRole::ADMIN->value;
        });

        Gate::define('manage-inventory', function ($user) {
            return $user->role === UserRole::ADMIN->value;
        });

        Gate::define('manage-orders', function ($user) {
            return $user->role === UserRole::ADMIN->value;
        });

        Gate::define('view-dashboard', function ($user) {
            return $user->role === UserRole::ADMIN->value;
        });

        Gate::define('manage-notifications', function ($user) {
            return $user->role === UserRole::ADMIN->value;
        });

        // Buyer-specific gates
        Gate::define('place-orders', function ($user) {
            return $user->role === UserRole::BUYER->value;
        });

        Gate::define('write-reviews', function ($user, $product) {
            // Only buyers who have purchased the product can write reviews
            return $user->role === UserRole::BUYER->value && 
                   $user->orders()->whereHas('items', function($query) use ($product) {
                       return $query->where('product_id', $product->id);
                   })->where('status', 'completed')->exists();
        });

        Gate::define('manage-cart', function ($user) {
            return $user->role === UserRole::BUYER->value;
        });
    }
}
