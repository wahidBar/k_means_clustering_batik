@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h3 class="mb-4">Detail UMKM: {{ $partner->business_name }}</h3>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($partner->images_partner)
                            <p><strong>Foto/Logo UMKM:</strong></p>
                            <img src="{{ asset('storage/' . $partner->images_partner) }}" alt="Foto UMKM" class="img-thumbnail"
                                width="250">
                        @endif
                    </div>
                    <div class="col-md-8">
                        <p><strong>Pemilik:</strong> {{ $partner->owner_name }}</p>
                        <p><strong>Pemasaran:</strong> {{ implode(', ', (array) $partner->pemasaran ?? []) }}</p>
                        <p><strong>NIB:</strong> {{ $partner->nib ?? 'Belum ada' }}</p>
                        <p><strong>Alamat:</strong> {{ $partner->address }}</p>
                        <p><strong>Kontak:</strong> {{ $partner->contact }}</p>
                        <p><strong>Deskripsi:</strong> {{ $partner->description ?? '-' }}</p>
                        <p><strong>Koordinat:</strong> {{ $partner->latitude }}, {{ $partner->longitude }}</p>
                        <p><strong>Status Validasi Terakhir:</strong>
                    </div>
                </div>
                <span
                    class="badge bg-{{ $latestValidation && $latestValidation->status == 'Terverifikasi' ? 'success' : ($latestValidation && $latestValidation->status == 'Tolak' ? 'danger' : 'warning') }}">
                    {{ $latestValidation->status ?? 'Belum Ada' }}
                </span>
                </p>
            </div>
        </div>

        {{-- ADMIN & PARTNER: tampilkan semua riwayat --}}
        @if(
                (auth()->user()->role_id == 1) ||
                (auth()->user()->role_id == 2 && auth()->user()->id == $partner->user_id)
            )
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Riwayat Validasi</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Validator</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($validationHistories as $history)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($history->updated_at)->format('d M Y H:i') }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{
                                $history->status == 'Terverifikasi' ? 'success' :
                                ($history->status == 'Tolak' ? 'danger' : 'warning')
                                                                                                                                                                            }}">
                                                    {{ $history->status }}
                                                </span>
                                            </td>
                                            <td>{{ $history->note ?? '-' }}</td>
                                            <td>{{ $history->user->name ?? '-' }}</td>
                                        </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">Belum ada riwayat validasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="map" style="height: 350px; border-radius: 10px;"></div>
            </div>
        @else
            {{-- GUEST --}}
            <div class="alert alert-info">
                <strong>Status terakhir:</strong> {{ $latestValidation->status ?? 'Belum diverifikasi' }}<br>
                <small>{{ $latestValidation->note ?? '' }}</small>
            </div>
        @endif

        <a href="{{ route('dashboard.partners.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
@endsection
{{-- Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const lat = parseFloat("{{ $partner->latitude ?? '-7.005145' }}")
        const lng = parseFloat("{{ $partner->longitude ?? '113.863250' }}")
        const map = L.map('map').setView([lat, lng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map)
            .bindPopup("<b>{{ $partner->business_name }}</b><br>{{ $partner->address }}");
    });
</script>
