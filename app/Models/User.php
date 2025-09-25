<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'provider',
        'provider_id',
    ];

    /**
     * Get the user's role.
     */
    public function getRoleAttribute($value)
    {
        \Log::info('Getting role', [
            'user_id' => $this->id,
            'role' => $value,
            'attributes' => $this->attributes
        ]);
        return $value;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return strtolower($this->role) === strtolower(UserRole::ADMIN->value);
    }

    /**
     * Check if the user is a buyer.
     */
    public function isBuyer(): bool
    {
        return strtolower($this->role) === strtolower(UserRole::BUYER->value);
    }

    /**
     * Assign admin role to the user.
     */
    public function makeAdmin(): self
    {
        $this->role = UserRole::ADMIN->value;
        $this->save();
        return $this;
    }

    /**
     * Assign buyer role to the user.
     */
    public function makeBuyer(): self
    {
        $this->role = UserRole::BUYER->value;
        $this->save();
        return $this;
    }

    /**
     * Get the cart items for the user.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the ratings for the user.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
