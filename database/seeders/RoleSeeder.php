<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use App\Models\BatikUmkmPartner;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // --- Pastikan role ada ---
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $partnerRole = Role::firstOrCreate(['name' => 'partner']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // --- 2 Admin ---
        $admins = [
            ['name' => 'Admin Wahid', 'email' => 'wahid_admin@example.com'],
            ['name' => 'Admin Siti', 'email' => 'siti_admin@example.com'],
        ];

        foreach ($admins as $admin) {
            User::firstOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role_id' => $adminRole->id,
                ]
            );
        }

        // --- 12 Partner ---
        $partners = [];
        for ($i = 1; $i <= 12; $i++) {
            $partners[] = User::firstOrCreate(
                ['email' => "partner{$i}@example.com"],
                [
                    'name' => "Partner {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role_id' => $partnerRole->id,
                ]
            );
        }

        // --- 3 Guest ---
        $guests = [];
        for ($i = 1; $i <= 3; $i++) {
            $guests[] = User::firstOrCreate(
                ['email' => "guest{$i}@example.com"],
                [
                    'name' => "Guest {$i}",
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role_id' => $userRole->id,
                ]
            );
        }

        // --- 12 Batik UMKM Partner (untuk 12 partner) ---
        $lokasi = [
            ['-7.0042', '113.8630'],
            ['-7.0165', '113.8572'],
            ['-7.0281', '113.8425'],
            ['-7.0312', '113.8493'],
            ['-7.0411', '113.8534'],
            ['-7.0513', '113.8620'],
            ['-7.0624', '113.8689'],
            ['-7.0700', '113.8702'],
            ['-7.0801', '113.8744'],
            ['-7.0920', '113.8815'],
            ['-7.1000', '113.8870'],
            ['-7.1122', '113.8901'],
        ];

        $pemasaranOptions = [
            ['Lokal'],
            ['Nasional'],
            ['Lokal', 'Nasional'],
            ['Lokal', 'Luar Negeri'],
            ['Nasional', 'Luar Negeri'],
            ['Lokal', 'Nasional', 'Luar Negeri'],
        ];

        foreach ($partners as $index => $partnerUser) {
            $coords = $lokasi[$index % count($lokasi)];
            BatikUmkmPartner::firstOrCreate(
                ['user_id' => $partnerUser->id],
                [
                    'business_name' => 'Batik ' . Str::random(5),
                    'address' => 'Desa Batik No. ' . rand(1, 100),
                    'owner_name' => $partnerUser->name,
                    'contact' => '08' . rand(1000000000, 9999999999),
                    'nib' => 'NIB-' . rand(100000, 999999),
                    'description' => 'Usaha batik khas Sumenep.',
                    'images_partner' => "images/partner_$i.jpg",
                    'latitude' => $coords[0],
                    'longitude' => $coords[1],
                    'pemasaran' => $pemasaranOptions[array_rand($pemasaranOptions)],
                ]
            );
        }

        echo "✅ Seeder selesai: 2 admin, 12 partner, 3 guest, dan 12 UMKM telah dibuat.\n";
    }
}
