<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\{
    Role,
    User,
    Type,
    BatikUmkmPartner,
    BatikProduct,
    MonthlyProduction
};
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // --------------------
        // 1) Roles & Admins
        // --------------------
        $adminRole   = Role::firstOrCreate(['name' => 'admin']);
        $partnerRole = Role::firstOrCreate(['name' => 'partner']);
        $userRole    = Role::firstOrCreate(['name' => 'user']);

        // Admins
        $admins = [
            ['name' => 'Admin Wahid', 'email' => 'wahid_admin@example.com'],
            ['name' => 'Admin Siti',  'email' => 'siti_admin@example.com'],
        ];
        foreach ($admins as $a) {
            User::firstOrCreate(
                ['email' => $a['email']],
                [
                    'name' => $a['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $adminRole->id,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Guests
        for ($g = 1; $g <= 3; $g++) {
            User::firstOrCreate(
                ['email' => "guest{$g}@example.com"],
                [
                    'name' => "Guest {$g}",
                    'password' => Hash::make('password'),
                    'role_id' => $userRole->id,
                    'email_verified_at' => now(),
                ]
            );
        }

        // --------------------
        // 2) CSV setup
        // --------------------
        $csvCandidates = [
            database_path('seeders/data/Batik Sumenep Data.csv'),
            database_path('seeders/data/Batik_Sumenep_Data.csv'),
            database_path('seeders/data/batik_sumenep_data.csv'),
        ];

        $csvPath = collect($csvCandidates)->first(fn($p) => File::exists($p));
        if (!$csvPath) {
            $this->command->error("❌ CSV tidak ditemukan. Taruh di: database/seeders/data/Batik Sumenep Data.csv");
            return;
        }

        $lines = preg_split("/\r\n|\n|\r/", trim(File::get($csvPath)));
        if (count($lines) < 2) {
            $this->command->error("CSV kosong atau format tidak valid.");
            return;
        }

        $delimiter = (substr_count($lines[0], "\t") > 1) ? "\t" : ",";
        $rows = array_map(fn($line) => str_getcsv($line, $delimiter), $lines);
        $header = array_shift($rows);
        $header = array_map('trim', $header);

        // --------------------
        // 3) Mapping kolom
        // --------------------
        $index = function ($names) use ($header) {
            foreach ($names as $n) {
                $i = array_search($n, $header);
                if ($i !== false) return $i;
            }
            return null;
        };

        $iNama = $index(['NAMA UMKM']);
        $iPemilik = $index(['PEMILIK']);
        $iAlamat = $index(['ALAMAT']);
        $iKuantitas = $index(['KUANTITAS']);
        $iLatitude = $index(['LATITUDE']);
        $iLongitude = $index(['LONGITUDE']);
        $iPemasaran = $index(['PEMASARAN']);
        $iKet = $index(['KET']);

        if (is_null($iNama) || is_null($iPemilik) || is_null($iKuantitas)) {
            $this->command->error("Header CSV tidak sesuai (butuh NAMA UMKM, PEMILIK, KUANTITAS).");
            return;
        }

        // --------------------
        // 4) Helper data
        // --------------------
        $pemasaranOptions = [
            ['Lokal'],
            ['Nasional'],
            ['Lokal', 'Nasional'],
            ['Nasional', 'Luar Negeri'],
            ['Lokal', 'Nasional', 'Luar Negeri'],
        ];
        $types = Type::pluck('id')->toArray();
        $adminIds = User::where('role_id', $adminRole->id)->pluck('id')->toArray();

        $csvPartnersCount = 0;
        $totalProductions = 0;

        // --------------------
        // 5) Partners dari CSV
        // --------------------
        foreach ($rows as $idx => $row) {
            if (!isset($row[$iNama])) continue;

            $nama = trim($row[$iNama]);
            $pemilik = trim($row[$iPemilik] ?? 'Unknown');
            $alamat = trim($row[$iAlamat] ?? '-');
            $kuantitas = (int) filter_var($row[$iKuantitas] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            $latitude = trim($row[$iLatitude] ?? '-');
            $longitude = trim($row[$iLongitude] ?? '-');
            $pemasaranRaw = trim($row[$iPemasaran] ?? '');
            $keterangan = trim($row[$iKet] ?? '');

            $pemasaranArr = [];
            if (stripos($pemasaranRaw, 'Lokal') !== false) $pemasaranArr[] = 'Lokal';
            if (stripos($pemasaranRaw, 'Nasional') !== false) $pemasaranArr[] = 'Nasional';
            if (stripos($pemasaranRaw, 'Luar') !== false) $pemasaranArr[] = 'Luar Negeri';
            if (empty($pemasaranArr)) $pemasaranArr = $pemasaranOptions[array_rand($pemasaranOptions)];

            $email = Str::slug($pemilik, '.') . '@example.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $pemilik,
                    'password' => Hash::make('password'),
                    'role_id' => $partnerRole->id,
                    'email_verified_at' => now(),
                ]
            );

            $partner = BatikUmkmPartner::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $nama,
                    'owner_name' => $pemilik,
                    'address' => $alamat,
                    'pemasaran' => $pemasaranArr,
                    'contact' => '08' . rand(1000000000, 9999999999),
                    'description' => $keterangan ?: 'UMKM batik lokal',
                    'nib' => 'NIB-' . rand(100000, 999999),
                    'images_partner' => 'images/partner_csv_' . ($idx + 1) . '.jpg',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    // 'latitude' => strval(-7.0 - ($idx * 0.001)),
                    // 'longitude' => strval(113.8 + ($idx * 0.001)),
                    'validation_status' => 'Terverifikasi',
                ]
            );

            $product = BatikProduct::create([
                'partner_id' => $partner->partner_id,
                'type_id' => $types[array_rand($types) ?? 0] ?? null,
                'product_name' => 'Batik ' . Str::limit($nama, 50),
                'description' => $keterangan ?: 'Produk batik khas',
                'price' => rand(120000, 450000),
                'image' => 'images/product_csv_' . ($idx + 1) . '.jpg',
            ]);

            // --------------------
            // Kuantitas dibagi dua bulan, total tetap sama
            // --------------------
            if ($kuantitas > 0) {
                $qty1 = (int) round($kuantitas / 2);
                $qty2 = $kuantitas - $qty1;

                $months = [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->startOfMonth(),
                ];
                $qtys = [$qty1, $qty2];

                foreach ($months as $k => $month) {
                    MonthlyProduction::create([
                        'partner_id' => $partner->partner_id,
                        'product_id' => $product->id,
                        'month' => $month,
                        'total_quantity' => $qtys[$k],
                        'production_notes' => 'Data import CSV — bulan ' . $month->format('M Y'),
                        'validation_status' => 'Approved',
                        'validated_by' => $adminIds[array_rand($adminIds)],
                        'validation_date' => now()->subDays(rand(1, 30)),
                    ]);
                    $totalProductions++;
                }
            }

            $csvPartnersCount++;
        }

        // --------------------
        // 6) Tambahan dummy partner
        // --------------------
        $extra = [
            ['Batik Kenanga', 'Rahmawati', 'Desa Batuputih, Batuputih'],
            ['Batik Anggrek', 'Ahmad Fajar', 'Desa Talango, Talango'],
            ['Batik Pesisir', 'Lilis Suryani', 'Desa Kalianget, Kalianget'],
            ['Batik Karunia', 'Hendra Setiawan', 'Desa Lenteng, Lenteng'],
            ['Batik Kencono', 'Desi Marlina', 'Desa Pragaan, Pragaan'],
            ['Batik Arjuna', 'Eko Susanto', 'Desa Rubaru, Rubaru'],
            ['Batik Sinar Baru', 'Nina Herlina', 'Desa Dungkek, Dungkek'],
            ['Batik Mawar', 'Rifqi Ramadhan', 'Desa Gapura, Gapura'],
            ['Batik Sari Madura', 'Yani Kusuma', 'Desa Ganding, Ganding'],
            ['Batik Pelangi', 'Taufik Ismail', 'Desa Dasuk, Dasuk'],
        ];

        foreach ($extra as $i => $e) {
            $statusPartner = rand(0, 1) ? 'Pending' : 'Tolak';
            $statusProd = $statusPartner === 'Pending' ? 'Pending' : 'Rejected';
            $pemasaranArr = $pemasaranOptions[array_rand($pemasaranOptions)];

            $email = Str::slug($e[1], '.') . '@example.com';
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $e[1],
                    'password' => Hash::make('password'),
                    'role_id' => $partnerRole->id,
                    'email_verified_at' => now(),
                ]
            );

            $partner = BatikUmkmPartner::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $e[0],
                    'owner_name' => $e[1],
                    'address' => $e[2],
                    'pemasaran' => $pemasaranArr,
                    'contact' => '08' . rand(1000000000, 9999999999),
                    'description' => 'UMKM batik tambahan',
                    'nib' => 'NIB-' . rand(100000, 999999),
                    'images_partner' => 'images/partner_extra_' . ($i + 1) . '.jpg',
                    'latitude' => strval(-7.2 + ($i * 0.002)),
                    'longitude' => strval(113.7 + ($i * 0.002)),
                    'validation_status' => $statusPartner,
                ]
            );

            $product = BatikProduct::create([
                'partner_id' => $partner->id,
                'type_id' => $types[array_rand($types) ?? 0] ?? null,
                'product_name' => 'Batik Motif ' . ucfirst(Str::random(4)),
                'description' => 'Produk batik variasi',
                'price' => rand(80000, 300000),
                'image' => 'images/product_extra_' . ($i + 1) . '.jpg',
            ]);

            MonthlyProduction::create([
                'partner_id' => $partner->id,
                'product_id' => $product->id,
                'month' => Carbon::now()->subMonths(rand(0, 5))->startOfMonth(),
                'total_quantity' => rand(20, 300),
                'production_notes' => 'Data tambahan simulasi',
                'latitude' => strval(-7.0 - ($idx * 0.001)),
                'longitude' => strval(113.8 + ($idx * 0.001)),
                'validation_status' => $statusProd,
            ]);
            $totalProductions++;
        }

        // --------------------
        // 7) Summary
        // --------------------
        $this->command->info("✅ Seeder selesai:");
        $this->command->info("- Admins: " . count($admins));
        $this->command->info("- Guests: 3");
        $this->command->info("- Partner CSV (Terverifikasi): $csvPartnersCount");
        $this->command->info("- Partner Tambahan (Pending/Tolak): " . count($extra));
        $this->command->info("- Total MonthlyProduction rows added: $totalProductions");
    }
}
