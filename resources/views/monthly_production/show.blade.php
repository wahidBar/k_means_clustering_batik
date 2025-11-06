@extends('layouts.app')
@section('content')
    <div class="container py-4 vh-100">

        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="row">
                    {{-- Kolom kiri: Gambar UMKM dan Produk --}}
                    <div class="col-md-4 text-center">
                        <img src="{{ asset('storage/' . $monthly_production->partner->images_partner) }}"
                            class="img-fluid rounded mb-3 shadow" alt="Gambar UMKM"
                            style="max-height: 180px; object-fit: cover;">
                        <h5 class="fw-bold">{{ $monthly_production->partner->umkm_name }}</h5>
                        <hr>
                        <img src="{{ asset('storage/' . $monthly_production->product->images) }}"
                            class="img-fluid rounded shadow" alt="Gambar Produk"
                            style="max-height: 180px; object-fit: cover;">
                        <p class="mt-2">{{ $monthly_production->product->product_name }}</p>
                    </div>

                    {{-- Kolom kanan: Detail Produksi --}}
                    <div class="col-md-8">
                        <h4 class="fw-semibold mb-3">Detail Produksi Bulanan</h4>
                        <table class="table table-borderless">
                            <tr>
                                <th width="35%">Bulan</th>
                                <td>{{ \Carbon\Carbon::parse($monthly_production->month)->translatedFormat('F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Total Produksi</th>
                                <td>{{ number_format($monthly_production->total_quantity) }} pcs</td>
                            </tr>
                            <tr>
                                <th>Cluster</th>
                                <td>{{ $monthly_production->cluster ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status Validasi</th>
                                <td>
                                    @if($monthly_production->validation_status == 'approved')
                                        <span class="badge bg-success">Disetujui</span>
                                    @elseif($monthly_production->validation_status == 'rejected')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Menunggu</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Catatan Produksi</th>
                                <td>{{ $monthly_production->production_notes ?: '-' }}</td>
                            </tr>
                            @if($monthly_production->validator)
                                <tr>
                                    <th>Divalidasi Oleh</th>
                                    <td>{{ $monthly_production->validator->name }}</td>
                                </tr>
                            @endif
                        </table>

                        <div class="mt-4">
                            <a href="{{ route('dashboard.monthly_production.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
