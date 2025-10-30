<?php

namespace App\Http\Controllers;

use App\Models\ValidationHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ValidationHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role_id == 1) {
            // 🧭 Admin: lihat semua riwayat validasi
            $histories = ValidationHistory::with(['partner.user', 'user'])
                ->orderByDesc('updated_at')
                ->paginate(10);
        }

        elseif ($user->role_id == 2) {
            // 🧭 Partner: tampilkan semua validasi (semua UMKM),
            // tapi UMKM miliknya sendiri tampil paling atas

            $partnerIds = $user->partner
                ? collect([$user->partner->partner_id]) // satu partner_id
                : collect();

            // Ambil semua dulu
            $allHistories = ValidationHistory::with(['partner.user', 'user'])
                ->orderByDesc('updated_at')
                ->get();

            // Urutkan agar milik sendiri di atas
            $sorted = $allHistories->sortBy(function ($history) use ($partnerIds) {
                return $partnerIds->contains($history->partner_id) ? 0 : 1;
            })->values();

            // Manual pagination
            $perPage = 10;
            $page = $request->get('page', 1);
            $offset = ($page - 1) * $perPage;

            $histories = new LengthAwarePaginator(
                $sorted->slice($offset, $perPage),
                $sorted->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        else {
            abort(403, 'Kamu tidak memiliki izin untuk melihat halaman ini.');
        }

        return view('validation_histories.index', compact('histories', 'user'));
    }
}
