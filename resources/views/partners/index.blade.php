@extends('layouts.app')

@section('content')
<div class="container mt-4 vh-100">
    <h3 class="mb-3">Daftar Mitra UMKM Batik</h3>

    {{-- 🔍 Pencarian dan Tombol Aksi --}}
    <div class="d-flex justify-content-between mb-3">
        <form method="GET" action="{{ route('dashboard.partners.index') }}" class="d-flex">
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari UMKM..." class="form-control me-2">
            <button type="submit" class="btn btn-secondary">Cari</button>
        </form>

        {{-- Tombol berdasarkan role --}}
        @if($user->role_id == 1)
             <a href="{{ route('dashboard.partners.create') }}" class="btn btn-success">
                Daftar UMKM
            </a>
        @elseif($user->role_id == 2 && !$user->partner)
            <a href="{{ route('dashboard.partners.create') }}" class="btn btn-primary">
                + Tambah UMKM
            </a>
        @elseif($user->role_id == 3 && !$user->partner)
            <a href="{{ route('dashboard.partners.create') }}" class="btn btn-success">
                Daftar UMKM
            </a>
        @endif
    </div>

    {{-- ✅ Alert --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- 📋 Tabel --}}
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Nama Usaha</th>
                <th>Gambar UMKM</th>
                <th>Pemilik</th>
                <th>Pemasaran</th>
                <th>Kontak</th>
                <th>Status</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partners as $index => $partner)
                <tr @if($partner->user_id == $user->id) class="table-primary" @endif>
                    <td>{{ $partners->firstItem() + $index }}</td>
                    <td>{{ $partner->business_name }}</td>
                     <td>
                         @if($partner->images_partner)
                            <img src="{{ asset('storage/' . $partner->images_partner) }}" width="70" class="rounded">
                        @else
                            <span class="text-muted">Tidak ada</span>
                        @endif
                    </td>
                    <td>{{ $partner->owner_name }}</td>
                    <td>
                        @if(!empty($partner->pemasaran))
                            {{ implode(', ', $partner->pemasaran) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $partner->contact ?? '-' }}</td>
                    <td>
                        @switch($partner->validation_status)
                            @case('Terverifikasi')
                                <span class="badge bg-success">Terverifikasi</span>
                                @break
                            @case('Pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                                @break
                            @case('Tolak')
                                <span class="badge bg-danger">Tolak</span>
                                @break
                            @default
                                <span class="badge bg-secondary">Belum Divalidasi</span>
                        @endswitch
                    </td>
                    <td class="text">
                        {{-- Lihat --}}
                        <a href="{{ route('dashboard.partners.show', $partner->partner_id) }}" class="btn btn-info btn-sm" title="Lihat">
                            <i class="bi bi-eye"></i>
                        </a>

                        {{-- Edit (admin atau pemilik) --}}
                        @if($user->role_id == 1 || $partner->user_id == $user->id)
                            <a href="{{ route('dashboard.partners.edit', $partner->partner_id) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        @endif

                        {{-- Hapus (hanya admin) --}}
                        @if($user->role_id == 1)
                            <form action="{{ route('dashboard.partners.destroy', $partner->partner_id) }}" method="POST" style="display:inline-block">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" title="Hapus" onclick="return confirm('Yakin hapus data ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">Belum ada data mitra UMKM.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- 📄 Pagination --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $partners->links() }}
    </div>
</div>
@endsection
