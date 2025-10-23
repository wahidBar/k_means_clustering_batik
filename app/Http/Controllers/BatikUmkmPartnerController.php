<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BatikUmkmPartner;
// use BatikUmkmPartner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @method void middleware($middleware, array $options = [])
 */

class BatikUmkmPartnerController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth')->except(['index', 'show']);
    // }

    public function index(Request $request)
    {
        $search = $request->input('q');

        $partners = BatikUmkmPartner::with('user')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(business_name) LIKE ?', [strtolower("{$search}%")])
                        ->orWhere('business_name', $search);
                });
            })
            ->orderBy('business_name')
            ->latest()
            ->paginate(10);


        return view('partners.index', compact('partners', 'search'));
    }


    public function create()
    {
        $user = Auth::user();

        // Jika admin, ambil semua user dengan role_id = 2 (partner)
        $users = $user->role_id == 1 ? User::whereIn('role_id', [2, 3])->get() : collect(); // kosong jika bukan admin

        return view('partners.create', compact('users', 'user'));
    }

    public function store(Request $request)
    {
        try {
            // --- 1️⃣ Validasi input (disesuaikan varchar)
            $validated = $request->validate([
                'business_name' => 'required|string|max:50',
                'owner_name' => 'required|string|max:30',
                'address' => 'required|string|max:255',
                'owner_name' => 'required|string|max:18',
                'contact' => 'required|string|max:18',
                'description' => 'nullable|string',
                'latitude' => 'required|string|regex:/^-?\d+(\.\d+)?$/',
                'longitude' => 'required|string|regex:/^-?\d+(\.\d+)?$/',
                'user_id' => 'nullable|exists:users,id',
            ], [
                'latitude.required' => 'Silakan pilih lokasi pada peta.',
                'longitude.required' => 'Silakan pilih lokasi pada peta.',
                'latitude.regex' => 'Format latitude tidak valid.',
                'longitude.regex' => 'Format longitude tidak valid.',
            ]);

            // --- 2️⃣ Tentukan pemilik UMKM (user_id)
            if (Auth::user()->role_id == 1) {
                // Admin bisa memilih user
                $validated['user_id'] = $request->input('user_id');
            } else {
                // Partner otomatis jadi pemilik
                $validated['user_id'] = Auth::id();
            }

            // --- 3️⃣ Simpan ke database
            BatikUmkmPartner::create($validated);

            // --- 4️⃣ Redirect sukses
            return redirect()
                ->route('dashboard.partners.index')
                ->with('success', 'Data UMKM berhasil disimpan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Terjadi kesalahan pada input. Silakan periksa kembali.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function show(BatikUmkmPartner $partner)
    {
        return view('partners.show', compact('partner'));
    }

    public function edit(BatikUmkmPartner $partner)
    {
        $user = Auth::user();

        // Partner hanya boleh edit UMKM miliknya sendiri
        if ($user->role_id == 2 && $partner->user_id !== $user->id) {
            abort(403, 'Kamu tidak memiliki izin untuk mengedit data ini.');
        }

        // Admin bisa memilih pemilik UMKM
        $users = $user->role_id == 1
            ? User::where('role_id', 2)->get()
            : collect();

        return view('partners.edit', compact('partner', 'users', 'user'));
    }

    public function update(Request $request, BatikUmkmPartner $partner)
    {
        try {
            $user = Auth::user();

            // Partner hanya boleh edit UMKM miliknya sendiri
            if ($user->role_id == 2 && $partner->user_id !== $user->id) {
                abort(403, 'Kamu tidak memiliki izin untuk mengedit data ini.');
            }

            $validated = $request->validate([
                'business_name' => 'required|string|max:50',
                'owner_name' => 'required|string|max:30',
                'address' => 'required|string|max:255',
                'owner_name' => 'required|string|max:18',
                'contact' => 'required|string|max:18',
                'description' => 'nullable|string',
                'latitude' => 'required|string|regex:/^-?\d+(\.\d+)?$/',
                'longitude' => 'required|string|regex:/^-?\d+(\.\d+)?$/',
                'user_id' => 'nullable|exists:users,id',
            ]);

            // Hanya admin yang bisa mengubah pemilik UMKM
            if ($user->role_id == 1 && $request->filled('user_id')) {
                $partner->user_id = $request->user_id;
            }

            $partner->update($validated);

            return redirect()->route('dashboard.partners.index')->with('success', 'Data UMKM berhasil diperbarui!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Terjadi kesalahan pada input. Silakan periksa kembali.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function destroy(BatikUmkmPartner $partner)
    {
        $user = Auth::user();

        // Partner hanya bisa hapus UMKM miliknya sendiri
        if ($user->role_id == 2 && $partner->user_id !== $user->id) {
            abort(403, 'Kamu tidak memiliki izin untuk menghapus data ini.');
        }

        $partner->delete();
        return redirect()->route('dashboard.partners.index')->with('success', 'Data UMKM berhasil dihapus!');
    }
}
