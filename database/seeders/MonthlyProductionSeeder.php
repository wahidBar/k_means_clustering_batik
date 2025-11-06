<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BatikUmkmPartner;
use App\Models\BatikProduct;
use App\Models\MonthlyProduction;
use Illuminate\Support\Facades\File;

class MonthlyProductionSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('seeders/data/Batik_Sumenep_Data.csv');
        $csv = array_map('str_getcsv', file($csvPath));
        $header = array_shift($csv);
        $csvData = [];

        foreach ($csv as $row) {
            $csvData[] = array_combine($header, $row);
        }

        foreach ($csvData as $row) {
            $partner = BatikUmkmPartner::where('business_name', $row['NAMA UMKM'])->first();

            if ($partner) {
                $productId = BatikProduct::where('partner_id', $partner->id)->value('id');
                $kuantitas = (int) $row['KUANTITAS'];

                // Bagi kuantitas ke 2 bulan (Oktober & November)
                $qtyOkt = (int) round($kuantitas * 0.6);
                $qtyNov = $kuantitas - $qtyOkt;

                $months = [
                    now()->subMonth()->format('Y-m-01') => $qtyOkt,
                    now()->format('Y-m-01') => $qtyNov,
                ];

                foreach ($months as $month => $qty) {
                    MonthlyProduction::updateOrCreate([
                        'partner_id' => $partner->id,
                        'product_id' => $productId,
                        'month' => $month,
                    ], [
                        'quantity_total' => $qty,
                        'validation_status' => 'Approved',
                    ]);
                }
            }
        }

        // Tambahan partner non-terverifikasi
        $pendingPartners = BatikUmkmPartner::whereIn('validation_status', ['Pending', 'Tolak'])->get();
        foreach ($pendingPartners as $partner) {
            $productId = BatikProduct::where('partner_id', $partner->id)->value('id');
            MonthlyProduction::create([
                'partner_id' => $partner->id,
                'product_id' => $productId,
                'month' => now()->format('Y-m-01'),
                'quantity_total' => rand(50, 200),
                'validation_status' => collect(['Rejected', 'Pending'])->random(),
            ]);
        }
    }
}
