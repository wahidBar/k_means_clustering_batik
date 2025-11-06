<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BatikUmkmPartner;
use Illuminate\Support\Facades\File;

class BatikUmkmPartnerSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/data/Batik_Sumenep_Data.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("❌ File CSV tidak ditemukan: $csvPath");
            return;
        }

        $csv = array_map('str_getcsv', file($csvPath));
        $header = array_shift($csv);
        $csvData = [];

        foreach ($csv as $row) {
            $csvData[] = array_combine($header, $row);
        }

        $pemasaranOptions = [
            ['Lokal'],
            ['Nasional'],
            ['Lokal', 'Nasional'],
            ['Nasional', 'Luar Negeri'],
            ['Lokal', 'Nasional', 'Luar Negeri'],
        ];

        // Partner dari CSV (Terverifikasi)
        foreach ($csvData as $row) {
            $owner = trim($row['PEMILIK']);
            $email = strtolower(str_replace(' ', '.', $owner)) . '@gmail.com';

            BatikUmkmPartner::updateOrCreate([
                'business_name' => $row['NAMA UMKM'],
            ], [
                'owner_name' => $owner,
                'email' => $email,
                'address' => $row['ALAMAT'],
                'validation_status' => 'Terverifikasi',
                'pemasaran' => json_encode(explode(' ', str_replace(['dan', 'Luar', 'Negeri'], ['Luar Negeri', 'Luar Negeri', 'Luar Negeri'], $row['PEMASARAN']))),
            ]);
        }

        // Tambahan partner random (Pending/Tolak)
        $extraPartners = [
            ['Batik Lestari', 'Rina Kartika', 'Desa Giring Kecamatan Manding', 'Pending'],
            ['Batik Pelangi Sumenep', 'Ahmad Junaidi', 'Desa Daramista Kecamatan Lenteng', 'Tolak'],
            ['Batik Mega Mendung', 'Susi Anggraini', 'Desa Kerta Timur Kecamatan Dasuk', 'Pending'],
            ['Batik Sinar Harapan', 'Nurul Lestari', 'Desa Kacongan Kecamatan Kota', 'Tolak'],
            ['Batik Anggun', 'Dedi Rahman', 'Desa Kolor Kecamatan Kota', 'Pending'],
            ['Batik Kenongo', 'Fatimah Aini', 'Desa Pamolokan Kecamatan Kota', 'Pending'],
            ['Batik Arjuna', 'Rudi Hartono', 'Desa Bangkal Kecamatan Kota', 'Tolak'],
            ['Batik Putri Madura', 'Siti Rahmah', 'Desa Baban Kecamatan Gapura', 'Pending'],
            ['Batik Cantika', 'Nurhayati', 'Desa Kasengan Kecamatan Manding', 'Tolak'],
            ['Batik Dewi', 'Aliyah Hasan', 'Desa Talango Kecamatan Talango', 'Pending'],
        ];

        foreach ($extraPartners as $p) {
            BatikUmkmPartner::create([
                'business_name' => $p[0],
                'owner_name' => $p[1],
                'email' => strtolower(str_replace(' ', '.', $p[1])) . '@gmail.com',
                'address' => $p[2],
                'validation_status' => $p[3],
                'pemasaran' => json_encode($pemasaranOptions[array_rand($pemasaranOptions)]),
            ]);
        }
    }
}
