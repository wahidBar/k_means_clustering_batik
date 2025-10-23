<?php

?>
@extends('layouts.app')

@section('content')
<div class="dashboard-title text-center">
    <h2 class="fw-bold text-primary mb-0">Dashboard Admin</h2>
    <p class="text-muted">Selamat datang di Sistem Informasi Geografis Batik Sumenep</p>
</div>

{{-- Statistik Cards --}}
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-secondary">Jumlah UMKM Terdaftar</h6>
                <h2 class="fw-bold text-primary">{{ $jumlah_umkm ?? 150 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-secondary">Jumlah Produk Batik</h6>
                <h2 class="fw-bold text-success">{{ $jumlah_batik ?? 80 }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-center border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-secondary">Jumlah Cluster Terbentuk</h6>
                <h2 class="fw-bold text-warning">{{ $jumlah_cluster ?? 3 }}</h2>
            </div>
        </div>
    </div>
</div>

{{-- Grafik Statistik --}}
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Grafik Jumlah UMKM per Cluster</h6>
                <canvas id="clusterChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Distribusi Produk Batik</h6>
                <canvas id="produkChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Tabel UMKM Terbaru --}}
<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="fw-bold mb-3"><i class="bi bi-list"></i> Daftar UMKM Terbaru</h5>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama UMKM</th>
                        <th>Alamat</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($umkm_terbaru ?? [
                    ['nama' => 'Batik Sari', 'alamat' => 'Jl. Pahlawan No.12', 'status' => 'Aktif'],
                    ['nama' => 'Batik Lestari', 'alamat' => 'Jl. Merdeka No.8', 'status' => 'Aktif'],
                    ['nama' => 'Batik Muda', 'alamat' => 'Jl. Veteran No.5', 'status' => 'Nonaktif']
                    ] as $i => $umkm)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $umkm['nama'] }}</td>
                        <td>{{ $umkm['alamat'] }}</td>
                        <td>
                            <span class="badge {{ $umkm['status'] == 'Aktif' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $umkm['status'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada data UMKM.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const ctxCluster = document.getElementById('clusterChart');
    const clusterChart = new Chart(ctxCluster, {
        type: 'bar',
        data: {
            labels: ['Cluster 1', 'Cluster 2', 'Cluster 3'],
            datasets: [{
                label: 'Jumlah UMKM',
                data: [45, 70, 35],
                backgroundColor: ['#0d6efd', '#198754', '#ffc107']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Distribusi UMKM Berdasarkan Cluster'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctxProduk = document.getElementById('produkChart');
    const produkChart = new Chart(ctxProduk, {
        type: 'pie',
        data: {
            labels: ['Batik Tulis', 'Batik Cap', 'Batik Kombinasi'],
            datasets: [{
                data: [40, 30, 30],
                backgroundColor: ['#6610f2', '#0d6efd', '#20c997']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Distribusi Jenis Produk Batik'
                }
            }
        }
    });
</script>
@endsection
