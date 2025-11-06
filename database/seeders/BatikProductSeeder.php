<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BatikProduct;
use App\Models\BatikUmkmPartner;

class BatikProductSeeder extends Seeder
{
    public function run(): void
    {
        $partners = BatikUmkmPartner::all();

        foreach ($partners as $partner) {
            BatikProduct::updateOrCreate([
                'partner_id' => $partner->id,
            ], [
                'product_name' => $partner->business_name,
                'description' => 'Produk batik unggulan oleh ' . $partner->owner_name,
            ]);
        }
    }
}
