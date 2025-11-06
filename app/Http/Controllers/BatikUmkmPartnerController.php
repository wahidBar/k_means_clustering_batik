<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\BatikUmkmPartner;
use App\Models\ValidationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class BatikUmkmPartnerController extends Controller
{
    // 🧭 INDEX
    public function index(Request $request)
    {
        $search = $request->input('q');
        $user = Auth::user();

        $partners = BatikUmkmPartner::with('user')
            ->when($search, function ($query, $search) {
                $query->where('business_name', 'LIKE', "%{$search}%")
                    ->orWhere('owner_name', 'LIKE', "%{$search}%");
            })
            // UMKM milik user sendiri muncul paling atas
            ->orderByRaw("CASE WHEN user_id = ? THEN 0 ELSE 1 END", [$user->id])
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends(['q' => $search]); // supaya query pencarian tetap di pagination

        return view('partners.index', compact('partners', 'search', 'user'));
    }


    // 🆕 CREATE
    public function create()
    {
        $user = Auth::user();

        // 🛑 Cegah guest/partner memiliki lebih dari 1 UMKM
        if (in_array($user->role_id, [2, 3]) && BatikUmkmPartner::where('user_id', $user->id)->exists()) {
            return redirect()->route('dashboard.partners.index')
                ->with('error', 'Anda sudah memiliki data UMKM.');
        }

        // 🔍 Ambil calon user (guest atau partner) yang belum punya UMKM
        $users = collect();
        if ($user->role_id == 1) {
            $users = User::whereIn('role_id', [2, 3])
                ->whereDoesntHave('partner') // relasi singular di model User
                ->get()
                ->map(function ($u) {
                    // Ambil validasi terakhir (jika ada)
                    $lastValidation = ValidationHistory::whereHas('partner', function ($q) use ($u) {
                        $q->where('user_id', $u->id);
                    })
                        ->latest('validation_date')
                        ->first();

                    $u->validation_status = $lastValidation->status ?? 'Belum Divalidasi';
                    $u->note = $lastValidation->note ?? 'Belum Divalidasi';
                    return $u;
                });
        }

        return view('partners.create', compact('users', 'user'));
    }


    // 💾 STORE
    public function store(Request $request)
    {
        $user = Auth::user();

        // Cegah user dengan role tertentu punya lebih dari satu UMKM
        if ($user->role_id == 3 && BatikUmkmPartner::where('user_id', $user->id)->exists()) {
            return redirect()->route('dashboard.partners.index')
                ->with('error', 'Anda sudah mendaftar UMKM.');
        }

        if ($user->role_id == 2 && BatikUmkmPartner::where('user_id', $user->id)->exists()) {
            return redirect()->route('dashboard.partners.index')
                ->with('error', 'Anda hanya dapat memiliki satu UMKM.');
        }

        $validated = $request->validate([
            'business_name' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'owner_name' => 'required|string|max:50',
            'pemasaran' => 'required|array',
            'pemasaran.*' => 'in:Lokal,Nasional,Luar Negeri',
            'contact' => 'required|string|max:18',
            'nib' => 'required|string|max:30',
            'description' => 'nullable|string',
            'images_partner' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'latitude' => 'required|string|regex:/^-?\d+(\.\d+)?$/',
            'longitude' => 'required|string|regex:/^-?\d+(\.\d+)?$/',
            'status' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'note' => 'nullable|string',
        ], [
            'latitude.required' => 'Silakan pilih lokasi pada peta.',
            'longitude.required' => 'Silakan pilih lokasi pada peta.',
        ]);

        // Tentukan user_id berdasarkan role
        if ($user->role_id == 1 && $request->filled('user_id')) {
            $validated['user_id'] = $request->user_id;
        } else {
            $validated['user_id'] = $user->id;
        }

        // Tentukan status awal (default Pending)
        $validated['status'] = $request->validation_status ?? 'Pending';

        // Validate Gambar
        if ($request->hasFile('images_partner')) {
            $validated['images_partner'] = $request->file('images_partner')->store('partners', 'public');
        }


        // Simpan data UMKM
        $partner = BatikUmkmPartner::create($validated);

        // Catatan validasi pertama
        ValidationHistory::create([
            'partner_id' => $partner->partner_id,
            'user_id' => $user->id,
            'validation_date' => Carbon::now(),
            'status' => $request->validation_status ?? 'Pending',
            'note' => $request->validation_status === 'Terverifikasi'
                ? ($request->note ?? 'UMKM telah diverifikasi.')
                : ($request->note ?? 'Menunggu verifikasi admin.'),

        ]);

        return redirect()->route('dashboard.partners.index')
            ->with('success', 'Data UMKM berhasil disimpan.');
    }


    // 👁️ SHOW
    public function show(BatikUmkmPartner $partner)
    {
        $user = Auth::user();

        if ($user->role_id == 1 || $user->role_id == 2) {
            $validationHistories = ValidationHistory::where('partner_id', $partner->partner_id)
                ->orderByDesc('updated_at')
                ->get();
            $latestValidation = $validationHistories->first();
        } else {
            // Guest hanya lihat validasi terakhir
            $validationHistories = collect();
            $latestValidation = ValidationHistory::where('partner_id', $partner->partner_id)
                ->latest('updated_at')
                ->first();
        }

        return view('partners.show', compact('partner', 'validationHistories', 'latestValidation', 'user'));
    }

    // 🖋️ EDIT
    public function edit(BatikUmkmPartner $partner)
    {
        $this->authorizeAccess($partner);

        $user = Auth::user();

        $users = $user->role_id == 1
            ? User::where('role_id', 3)->get()
            : collect();


        $lastValidation = ValidationHistory::where('partner_id', $partner->partner_id)
            ->latest('updated_at')
            ->first();

        $note = $lastValidation->note ?? '';

        return view('partners.edit', compact('partner', 'users', 'user', 'note'));
    }


    // 🔄 UPDATE
    public function update(Request $request, BatikUmkmPartner $partner)
    {
        $this->authorizeAccess($partner);

        $validated = $request->validate([
            'business_name' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'owner_name' => 'required|string|max:50',
            'pemasaran' => 'required|array',
            'pemasaran.*' => 'in:Lokal,Nasional,Luar Negeri',
            'contact' => 'required|string|max:18',
            'nib' => 'required|string|max:30',
            'description' => 'nullable|string',
            'images_partner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'nullable|string|max:18',
            'note' => 'nullable|string|max:255',
            'latitude' => 'required|string|regex:/^-?\d+(\.\d+)?$/',
            'longitude' => 'required|string|regex:/^-?\d+(\.\d+)?$/',
            'user_id' => 'nullable|exists:users,id',
            'validation_status' => 'nullable|string|max:50'
        ]);

        $user = Auth::user();

        // Jika admin (role_id == 1), boleh ubah user_id pemilik UMKM
        if ($user->role_id == 1 && $request->filled('user_id')) {
            $partner->user_id = $request->user_id;
        }

        // Perbarui status (jika admin mengubah status validasi)
        $validated['status'] = $request->validation_status ?? $partner->status;

        // validate Gambar
        if ($request->hasFile('images_partner')) {
            $validated['images_partner'] = $request->file('images_partner')->store('partners', 'public');
        }

        $validated['nib']  = $request->nib ?? $partner->nib;

        // Update data utama UMKM
        $partner->update($validated);
        // dd($validated);
        // Jika admin mengubah status validasi, catat riwayat
        if ($user->role_id == 1 && $request->filled('validation_status')) {
            $status = $request->validation_status;
            $note = $request->validation_status === 'Terverifikasi'
                ? ($request->note ?? 'UMKM telah diverifikasi.')
                : ($request->note ?? 'Menunggu verifikasi admin.');


            ValidationHistory::create([
                'partner_id' => $partner->partner_id,
                'user_id' => $user->id,
                'validation_date' => Carbon::now(),
                'status' => $status,
                'note' => $note,
            ]);

            // Jika status diverifikasi → ubah role user menjadi "partner" (role_id = 2)
            if ($status === 'Terverifikasi' && $partner->user) {
                $partner->user->update(['role_id' => 2]);
            }
        }

        return redirect()->route('dashboard.partners.index')
            ->with('success', 'Data UMKM berhasil diperbarui.');
    }


    // 🗑️ DESTROY
    public function destroy(BatikUmkmPartner $partner)
    {
        $this->authorizeAccess($partner);
        $partner->delete();

        return redirect()->route('dashboard.partners.index')
            ->with('success', 'Data UMKM berhasil dihapus.');
    }

    // 🔒 Autorisasi akses
    private function authorizeAccess(BatikUmkmPartner $partner)
    {
        $user = Auth::user();

        if ($user->role_id == 3 && $partner->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data.');
        }

        if ($user->role_id == 2 && $partner->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah data ini.');
        }
    }
}
