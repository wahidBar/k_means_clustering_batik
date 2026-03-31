<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatikUmkmPartner;
use App\Models\MonthlyProduction;

class GISController extends Controller
{
    public function index(Request $request)
    {
        $clusterFilter = $request->get('cluster'); // cluster dari dropdown

        $partners = BatikUmkmPartner::with(['productions' => function ($q) {
            $q->where('validation_status', 'Approved')
                ->with('product:id,product_name');
        }])
            ->whereNotNull('cluster') // ✅ hanya tampilkan yang sudah punya cluster

            ->when($clusterFilter, function ($q) use ($clusterFilter) {
                $q->where('cluster', $clusterFilter);
            })
            ->get();

        // Format data
        $partners = $partners->map(function ($partner) {
            $total_quantity = $partner->productions->sum('total_quantity');
            $product_names = $partner->productions
                ->pluck('product.product_name')
                ->filter()
                ->unique()
                ->implode(', ');

            return (object) [
                'partner_id' => $partner->partner_id,
                'business_name' => $partner->business_name,
                'owner_name' => $partner->owner_name,
                'address' => $partner->address,
                'latitude' => $partner->latitude,
                'longitude' => $partner->longitude,
                'pemasaran' => is_array($partner->pemasaran) ? implode(', ', $partner->pemasaran) : $partner->pemasaran,
                'cluster' => $partner->cluster,
                'total_quantity' => $total_quantity,
                'product_names' => $product_names,
            ];
        });

        // Filter koordinat valid
        $partners_with_coords = $partners->filter(function ($p) {
            $lat = str_replace(',', '.', trim($p->latitude));
            $lon = str_replace(',', '.', trim($p->longitude));
            return is_numeric($lat) && is_numeric($lon);
        })->map(function ($p) {
            $p->latitude = (float) str_replace(',', '.', trim($p->latitude));
            $p->longitude = (float) str_replace(',', '.', trim($p->longitude));
            return $p;
        });

        $partners_no_coords = $partners->reject(function ($p) {
            return is_numeric(str_replace(',', '.', $p->latitude)) &&
                is_numeric(str_replace(',', '.', $p->longitude));
        });

        $clusters = BatikUmkmPartner::select('cluster')
            ->whereNotNull('cluster')
            ->distinct()
            ->pluck('cluster');

        return view('gis.umkm', compact('partners_with_coords', 'partners_no_coords', 'clusters', 'clusterFilter'));
    }
}
