<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'admin1@example.com',
        ], [
            'name' => 'Admin Siti',
            'password' => Hash::make('password'),
            'role_id' => 1,
        ]);

        User::updateOrCreate([
            'email' => 'admin2@example.com',
        ], [
            'name' => 'Admin Wahid',
            'password' => Hash::make('password'),
            'role_id' => 1,
        ]);
    }
}
