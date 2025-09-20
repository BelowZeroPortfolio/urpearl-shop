<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@urpearl.com'],
            [
                'name' => 'UrPearl Admin',
                'email' => 'admin@urpearl.com',
                'password' => Hash::make('admin123'),
                'role' => UserRole::ADMIN->value,
                'email_verified_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ]
        );

        // Create additional admin users if needed
        $adminUsers = [
            [
                'name' => 'Store Manager',
                'email' => 'manager@urpearl.com',
                'password' => Hash::make('manager123'),
                'role' => UserRole::ADMIN->value,
                'email_verified_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ],
            [
                'name' => 'Inventory Admin',
                'email' => 'inventory@urpearl.com',
                'password' => Hash::make('inventory123'),
                'role' => UserRole::ADMIN->value,
                'email_verified_at' => now(),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ],
        ];

        foreach ($adminUsers as $adminData) {
            User::firstOrCreate(
                ['email' => $adminData['email']],
                $adminData
            );
        }

        $this->command->info('Admin users created successfully!');
    }
}
