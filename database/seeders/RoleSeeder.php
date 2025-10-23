<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $partnerRole = Role::firstOrCreate(['name' => 'partner']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Buat user admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id, // ✅ tambahkan ini
        ]);
        // Buat user partner
        User::create([
            'name' => 'Partner User',
            'email' => 'akbarkaal50@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $partnerRole->id, // ✅ tambahkan ini juga
        ]);
        // Buat user biasa
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role_id' => $userRole->id, // ✅ tambahkan ini juga
        ]);
    }
}
