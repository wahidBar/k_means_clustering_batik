<?php

namespace App\Http\Controllers;

use App\Models\BatikProduct;
use App\Models\BatikUmkmPartner;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BatikProductController extends Controller
{
    // 🧭 INDEX — semua user bisa lihat produk
    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = BatikProduct::with(['partner', 'type'])
            ->when($search, function ($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%");
            })
            ->orderBy('product_name', 'asc');

        // Partner hanya lihat produk UMKM miliknya
        if (Auth::check() && Auth::user()->role_id == 2) {
            $query->whereHas('partner', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $products = $query->paginate(10);

        return view('products.index', compact('products', 'search'));
    }

    // 🆕 CREATE — hanya admin & partner
    public function create()
    {
        $user = Auth::user();

        if ($user->role_id == 3) {
            abort(403, 'Anda tidak memiliki izin untuk menambah produk.');
        }

        $types = Type::all();

        // Admin dapat memilih semua UMKM, partner hanya miliknya
        $partners = $user->role_id == 1
            ? BatikUmkmPartner::with('user')->get()
            : BatikUmkmPartner::where('user_id', $user->id)->get();

        return view('products.create', compact('types', 'partners', 'user'));
    }

    // 💾 STORE
    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id == 3) {
            abort(403, 'Anda tidak memiliki izin untuk menambah produk.');
        }

        $validated = $request->validate([
            'product_name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'partner_id' => 'required|exists:batik_umkm_partner,partner_id',
            'type_id' => 'required|exists:types,id',
        ]);

        // Partner hanya boleh menambah produk UMKM miliknya sendiri
        if ($user->role_id == 2) {
            $ownedPartnerIds = BatikUmkmPartner::where('user_id', $user->id)->pluck('partner_id')->toArray();
            if (!in_array($validated['partner_id'], $ownedPartnerIds)) {
                abort(403, 'Anda tidak memiliki izin menambahkan produk untuk UMKM ini.');
            }
        }

        // Upload gambar jika ada
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        BatikProduct::create($validated);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    // 👁️ SHOW — semua user bisa lihat
    public function show(BatikProduct $product)
    {
        return view('products.show', compact('product'));
    }

    // ✏️ EDIT — admin dan partner (hanya miliknya)
    public function edit(BatikProduct $product)
    {
        $this->authorizeAccess($product);

        $user = Auth::user();
        $types = Type::all();

        $partners = $user->role_id == 1
            ? BatikUmkmPartner::with('user')->get()
            : BatikUmkmPartner::where('user_id', $user->id)->get();

        return view('products.create', compact('product', 'types', 'partners', 'user'));
    }

    // 🔁 UPDATE
    public function update(Request $request, BatikProduct $product)
    {
        $this->authorizeAccess($product);

        $validated = $request->validate([
            'product_name' => 'required|string|max:50',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type_id' => 'required|exists:types,id',
            'partner_id' => 'required|exists:batik_umkm_partner,partner_id',
            'image' => 'nullable|image|max:2048',
        ]);

        // Partner hanya boleh update produk miliknya sendiri
        $user = Auth::user();
        if ($user->role_id == 2) {
            $ownedPartnerIds = BatikUmkmPartner::where('user_id', $user->id)->pluck('partner_id')->toArray();
            if (!in_array($validated['partner_id'], $ownedPartnerIds)) {
                abort(403, 'Anda tidak memiliki izin mengubah produk UMKM ini.');
            }
        }

        // Upload ulang gambar jika ada
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    // 🗑️ DELETE
    public function destroy(BatikProduct $product)
    {
        $this->authorizeAccess($product);
        $product->delete();

        return redirect()->route('dashboard.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // 🔒 Akses kontrol terpusat
    private function authorizeAccess(BatikProduct $product)
    {
        $user = Auth::user();

        // user biasa hanya boleh lihat
        if ($user->role_id == 3) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data.');
        }

        // partner hanya boleh edit/hapus produk milik UMKM-nya sendiri
        if ($user->role_id == 2) {
            $ownedPartnerIds = BatikUmkmPartner::where('user_id', $user->id)->pluck('partner_id')->toArray();
            if (!in_array($product->partner_id, $ownedPartnerIds)) {
                abort(403, 'Anda tidak memiliki izin untuk mengubah produk ini.');
            }
        }
    }
}
