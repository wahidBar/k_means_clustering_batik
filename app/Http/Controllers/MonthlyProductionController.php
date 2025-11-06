<?php

namespace App\Http\Controllers;

use App\Models\MonthlyProduction;
use App\Models\BatikUmkmPartner;
use App\Models\BatikProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonthlyProductionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $search = request('search'); // ambil keyword pencarian dari input

        $query = MonthlyProduction::with(['partner', 'product'])
            ->when($user->role_id == 2 && $user->partner, function ($q) use ($user) {
                // Filter hanya data milik partner yang login
                $q->where('partner_id', $user->partner->partner_id);
            })
            ->when($user->role_id != 1 && (! $user->partner), function ($q) {
                // Guest hanya melihat data yang sudah disetujui
                $q->where('validation_status', 'approved');
            })
            ->when($search, function ($q) use ($search) {
                // Filter berdasarkan nama UMKM
                $q->whereHas('partner', function ($partnerQuery) use ($search) {
                    $partnerQuery->where('business_name', 'like', '%' . $search . '%');
                });
            })
            ->latest();

        $productions = $query->paginate(10)->withQueryString();

        return view('monthly_production.index', compact('productions', 'user', 'search'));
    }


    public function create()
    {
        $user = Auth::user();

        if ($user->role_id == 1) {
            // Admin → bisa pilih partner mana saja
            $partners = BatikUmkmPartner::with('products')->get();
            $products = collect(); // kosong dulu, diisi lewat AJAX
        } elseif ($user->role_id == 2 && $user->partner) {
            // Partner → hanya produk miliknya
            $partners = collect([$user->partner]);
            $products = $user->partner->products;
        } else {
            abort(403);
        }

        return view('monthly_production.create', compact('partners', 'products', 'user'));
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        // dd($request);
        // ✅ Validasi dasar
        $validated = $request->validate([
            'partner_id' => 'required|exists:batik_umkm_partner,partner_id',
            'product_id' => 'required|exists:batik_products,id',
            'month' => 'required|string',
            'total_quantity' => 'required|numeric|min:1',
            'production_notes' => 'nullable|string',
        ]);

        // ✅ Validasi format bulan (YYYY-MM)
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $validated['month'])) {
            return back()
                ->withErrors(['month' => 'Format bulan tidak valid. Gunakan format YYYY-MM.'])
                ->withInput();
        }

        // ✅ Jika user adalah partner, pastikan hanya bisa input untuk partner miliknya
        if ($user->role_id == 2) {
            $partner = $user->partner;
            if (!$partner || $partner->partner_id != $validated['partner_id']) {
                abort(403, 'Anda tidak dapat menambahkan produksi untuk UMKM lain.');
            }
            $validated['partner_id'] = $partner->partner_id;
        }

        // ✅ Ubah "YYYY-MM" menjadi tanggal lengkap untuk disimpan di kolom `date`
        $validated['month'] = $validated['month'] . '-01';

        // ✅ Cegah duplikasi data
        $exists = MonthlyProduction::where('partner_id', $validated['partner_id'])
            ->where('product_id', $validated['product_id'])
            ->whereMonth('month', date('m', strtotime($validated['month'])))
            ->whereYear('month', date('Y', strtotime($validated['month'])))
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['month' => '❌ Data produksi untuk produk ini pada bulan tersebut sudah ada.'])
                ->withInput();
        }

        // ✅ Simpan data
        MonthlyProduction::create([
            'partner_id' => $validated['partner_id'],
            'product_id' => $validated['product_id'],
            'month' => $validated['month'],
            'total_quantity' => $validated['total_quantity'],
            'production_notes' => $validated['production_notes'] ?? null,
            'validation_status' => 'Pending',
            'validated_by' => null,
            'validation_date' => null,
        ]);

        return redirect()->route('dashboard.monthly_production.index')
            ->with('success', '✅ Data produksi berhasil ditambahkan.');
    }

    // Route untuk AJAX produk berdasarkan partner_id
    public function getProductsByPartner($partner_id)
    {
        $products = BatikProduct::where('partner_id', $partner_id)->get(['id', 'product_name']);
        return response()->json($products);
    }

    public function edit($id)
    {
        $production = MonthlyProduction::with(['partner', 'product'])->findOrFail($id);
        $user = Auth::user();

        // Jika partner, hanya boleh edit produksinya sendiri
        if ($user->role_id == 2 && $production->partner_id != $user->partner->partner_id) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit data ini.');
        }

        $partners = BatikUmkmPartner::all();
        $products = BatikProduct::all();

        // Ubah format date ke YYYY-MM agar cocok untuk <input type="month">
        $production->month = date('Y-m', strtotime($production->month));

        // dd($production);
        return view('monthly_production.edit', compact('production', 'partners', 'products'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $production = MonthlyProduction::findOrFail($id);

        // dd($production);
        // ✅ Validasi
        $validated = $request->validate([
            'partner_id' => 'required|exists:batik_umkm_partner,partner_id',
            'product_id' => 'required|exists:batik_products,id', // perbaiki ke id
            'month' => 'required|string',
            'total_quantity' => 'required|numeric|min:1',
            'production_notes' => 'nullable|string',
        ]);

        // ✅ Pastikan format bulan benar (YYYY-MM)
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $validated['month'])) {
            return back()
                ->withErrors(['month' => 'Format bulan tidak valid. Gunakan format YYYY-MM.'])
                ->withInput();
        }

        // ✅ Partner hanya boleh update datanya sendiri
        if ($user->role_id == 2) {
            $partner = $user->partner;
            if (!$partner || $partner->partner_id != $production->partner_id) {
                abort(403, 'Anda tidak memiliki izin untuk mengedit data ini.');
            }
            $validated['partner_id'] = $partner->partner_id;
        }

        // ✅ Konversi ke format YYYY-MM-DD
        $validated['month'] = $validated['month'] . '-01';

        // ✅ Cegah duplikasi (produk & bulan sama, tapi bukan data ini)
        $exists = MonthlyProduction::where('partner_id', $validated['partner_id'])
            ->where('product_id', $validated['product_id'])
            ->whereMonth('month', date('m', strtotime($validated['month'])))
            ->whereYear('month', date('Y', strtotime($validated['month'])))
            ->where('production_id', '!=', $production->production_id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['month' => '❌ Data produksi untuk produk ini pada bulan tersebut sudah ada.'])
                ->withInput();
        }

        // ✅ Update data
        $production->update([
            'partner_id' => $validated['partner_id'],
            'product_id' => $validated['product_id'],
            'month' => $validated['month'],
            'total_quantity' => $validated['total_quantity'],
            'production_notes' => $validated['production_notes'] ?? null,
            'validation_status' => 'Pending',
            'validation_date' => now(),
        ]);

        return redirect()->route('dashboard.monthly_production.index')
            ->with('success', '✅ Data produksi berhasil diperbarui.');
    }


    public function show(MonthlyProduction $monthly_production)
    {
        $monthly_production->load(['partner', 'product', 'validator']);
        return view('monthly_production.show', compact('monthly_production'));
    }

    // --- ACTION VALIDATION ADMIN ---
    public function approve($id)
    {
        $production = MonthlyProduction::findOrFail($id);
        $production->update([
            'validation_status' => 'Approved',
            'validated_by' => Auth::id(),
            'validation_date' => now(),
        ]);

        return back()->with('success', 'Produksi disetujui.');
    }

    public function reject($id)
    {
        $production = MonthlyProduction::findOrFail($id);
        $production->update([
            'validation_status' => 'Rejected',
            'validated_by' => Auth::id(),
            'validation_date' => now(),
        ]);

        return back()->with('error', 'Produksi ditolak.');
    }
}
