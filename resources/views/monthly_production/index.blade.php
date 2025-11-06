@extends('layouts.app')

@section('content')
    <div class="container vh-100">
        <h4 class="mb-4">Data Produksi Bulanan</h4>
        <div class="d-flex justify-content-between mb-3">
            <form action="{{ route('dashboard.monthly_production.index') }}" method="GET" class="d-flex"
                style="max-width: 300px;">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari UMKM..."
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-secondary">Cari</button>
            </form>
            @if(in_array($user->role_id, [1, 2]))
                <div class="mb-3 text-end">
                    <a href="{{ route('dashboard.monthly_production.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Tambah Data
                    </a>
                </div>
            @endif
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Partner</th>
                    <th>Produk</th>
                    <th>Bulan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Validator</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productions as $p)
                    <tr>
                        <td>{{ $p->partner->business_name }}</td>
                        <td>{{ $p->product->product_name }}</td>
                        <td>{{ $p->month }}</td>
                        <td>{{ $p->total_quantity }}</td>
                        <td>
                            @if($p->validation_status == 'Approved')
                                <span class="badge bg-success">Disetujui</span>
                            @elseif($p->validation_status == 'Rejected')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td>{{ $p->validator->name ?? '-' }}</td>
                        <td class="text-center">
                            @if($user->role_id == 1 && $p->validation_status == 'Pending')
                                <form action="{{ route('dashboard.monthly_production.approve', $p->production_id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <form action="{{ route('dashboard.monthly_production.reject', $p->production_id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-danger" title="Reject"><i class="bi bi-x-lg"></i></button>
                                </form>
                            @elseif($user->role_id == 2 && $p->validation_status == 'Rejected')
                                <a href="{{ route('dashboard.monthly_production.edit', $p->production_id) }}"
                                    class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i></a>
                            @else
                                <a href="{{ route('dashboard.monthly_production.show', $p->production_id) }}"
                                    class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i></a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">
            {{ $productions->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
