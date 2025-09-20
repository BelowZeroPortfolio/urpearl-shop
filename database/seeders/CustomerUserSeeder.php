<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        $customerUsers = [
            [
                'name' => 'Test Buyer',
                'email' => 'buyer@urpearl.com',
                'password' => Hash::make('buyer123'),
                'role' => UserRole::BUYER,
                'email_verified_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ],
            [
                'name' => 'Demo Shopper',
                'email' => 'shopper@urpearl.com',
                'password' => Hash::make('shopper123'),
                'role' => UserRole::BUYER,
                'email_verified_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ],
        ];

        foreach ($customerUsers as $customer) {
            User::firstOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }

        $this->command->info('✅ Customer users created successfully!');
    }
}
