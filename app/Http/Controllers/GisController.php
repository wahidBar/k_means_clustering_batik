<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatikUmkmPartner;
use App\Models\MonthlyProduction;
use App\Models\BatikProduct;

class GISController extends Controller
{
    public function index()
    {
        // 🔹 Ambil semua partner dengan relasi produksinya (yang disetujui)
        $partners = BatikUmkmPartner::with(['productions' => function ($q) {
            $q->where('validation_status', 'Approved')
                ->with('product:id,product_name');
        }])->get();

        // 🔹 Olah data tiap partner untuk kebutuhan peta
        $partners = $partners->map(function ($partner) {
            $total_quantity = $partner->productions->sum('total_quantity');
            $product_names = $partner->productions
                ->pluck('product.product_name')
                ->filter()
                ->unique()
                ->implode(', ');

            return (object) [
                'partner_id'      => $partner->partner_id,
                'business_name'   => $partner->business_name,
                'owner_name'      => $partner->owner_name,
                'address'         => $partner->address,
                'latitude'        => $partner->latitude,
                'longitude'       => $partner->longitude,
                'pemasaran'       => $partner->pemasaran,
                'cluster'         => $partner->cluster,
                'total_quantity'  => $total_quantity,
                'product_names'   => $product_names,
            ];
        });

        // 🔹 Filter & konversi koordinat yang valid
        $partners_with_coords = $partners->filter(function ($p) {
            $lat = str_replace(',', '.', trim($p->latitude));
            $lon = str_replace(',', '.', trim($p->longitude));
            return is_numeric($lat) && is_numeric($lon);
        })->map(function ($p) {
            $p->latitude  = (float) str_replace(',', '.', trim($p->latitude));
            $p->longitude = (float) str_replace(',', '.', trim($p->longitude));
            return $p;
        })->values(); // reset index agar array JSON rapi

        // 🔹 Data yang tidak punya koordinat
        $partners_no_coords = $partners->reject(function ($p) {
            $lat = str_replace(',', '.', trim($p->latitude));
            $lon = str_replace(',', '.', trim($p->longitude));
            return is_numeric($lat) && is_numeric($lon);
        })->values();
dd($partners_no_coords);
        // 🔹 Kirim ke view tanpa debug / dd
        return view('gis.umkm', compact('partners_with_coords', 'partners_no_coords'));
    }
}
