@extends('layouts.app')

@section('content')
<div class="container py-5 vh-100">
    <h3 class="mb-4">Daftar Produk Batik</h3>
    <div class="d-flex justify-content-between mb-3">
        <form class="d-flex" method="GET">
            <input type="text" name="search" value="{{ $search }}" class="form-control me-2"
                placeholder="Cari produk...">
            <button class="btn btn-secondary" type="submit">Cari</button>
        </form>

        @if(Auth::user()->role_id <= 2)
        <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Produk
        </a>
        @endif
    </div>

    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Gambar</th>
                <th>Nama Produk</th>
                <th>Jenis</th>
                <th>UMKM</th>
                <th>Harga</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $p)
            <tr>
                <td>
                    @if($p->image)
                    <img src="{{ asset('storage/'.$p->image) }}" alt="gambar" width="60" class="rounded">
                    @else
                    <small class="text-muted">tidak ada</small>
                    @endif
                </td>
                <td>{{ $p->product_name }}</td>
                <td>{{ $p->type->type_name ?? '-' }}</td>
                <td>{{ $p->partner->business_name ?? '-' }}</td>
                <td>Rp {{ number_format($p->price ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">
                    <a href="{{ route('dashboard.products.show', $p) }}" class="btn btn-sm btn-info">
                        <i class="bi bi-eye"></i>
                    </a>
                    @if(Auth::user()->role_id == 1 || ($p->partner->user_id ?? null) == Auth::id())
                    <a href="{{ route('dashboard.products.edit', $p) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <form action="{{ route('dashboard.products.destroy', $p) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">Belum ada produk batik</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{ $products->links() }}
</div>
@endsection
