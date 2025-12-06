<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pelabuhan.com',
            'password' => Hash::make('password123'),
            'role' => 'SUPER_ADMIN',
            'phone' => '081111111111',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@pelabuhan.com',
            'password' => Hash::make('password123'),
            'role' => 'ADMIN',
            'phone' => '081222222222',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Loket Staff
        User::create([
            'name' => 'Budi Loket 1',
            'email' => 'budi@pelabuhan.com',
            'password' => Hash::make('password123'),
            'role' => 'LOKET_STAFF',
            'phone' => '081333333333',
            'counter_id' => 1,
            'counter_status' => 'AVAILABLE',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Sari Loket 2',
            'email' => 'sari@pelabuhan.com',
            'password' => Hash::make('password123'),
            'role' => 'LOKET_STAFF',
            'phone' => '081444444444',
            'counter_id' => 2,
            'counter_status' => 'AVAILABLE',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Driver
        User::create([
            'name' => 'Joko Driver',
            'email' => 'joko@pelabuhan.com',
            'password' => Hash::make('password123'),
            'role' => 'DRIVER',
            'phone' => '081555555555',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Rina Driver',
            'email' => 'rina@pelabuhan.com',
            'password' => Hash::make('password123'),
            'role' => 'DRIVER',
            'phone' => '081666666666',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}