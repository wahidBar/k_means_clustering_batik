@extends('layouts.app')

@section('content')
<div class="d-flex vh-100 bg-light">
    <!-- <div class="card shadow-lg border-0" style="width: 400px; border-radius: 15px;"> -->
    <div class="container mt-4">
        <h3 class="mb-3">Daftar Mitra UMKM Batik</h3>

        <div class="container mb-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="{{ route('dashboard.partners.create') }}" class="btn btn-primary">
                        + Tambah Mitra
                    </a>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="{{ route('dashboard.partners.index') }}" class="d-flex">
                        <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Cari UMKM..." class="form-control me-2">
                        <button type="submit" class="btn btn-secondary">Cari</button>
                    </form>
                </div>
            </div>
        </div>


        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Usaha</th>
                    <th>Pemilik</th>
                    <th>Kontak</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($partners as $index => $partner)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $partner->business_name }}</td>
                    <td>{{ $partner->owner_name }}</td>
                    <td>{{ $partner->contact }}</td>
                    <td>{{ $partner->validation_status }}</td>
                    <td>
                        <a href="{{ route('dashboard.partners.show', $partner->partner_id) }}" class="btn btn-info btn-sm">Lihat</a>
                        <a href="{{ route('dashboard.partners.edit', $partner->partner_id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('dashboard.partners.destroy', $partner->partner_id) }}" method="POST" style="display:inline-block">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            {{ $partners->links() }}
        </div>
    </div>
    <!-- </div> -->
</div>
@endsection
