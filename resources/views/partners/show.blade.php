@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">Detail UMKM Partner</h3>

    <div class="card p-4">
        <h5>{{ $partner->business_name }}</h5>
        <p><strong>Pemilik:</strong> {{ $partner->owner_name }}</p>
        <p><strong>Alamat:</strong> {{ $partner->address }}</p>
        <p><strong>Kontak:</strong> {{ $partner->contact ?? '-' }}</p>
        <p><strong>Koordinat:</strong> {{ $partner->latitude ?? '-' }}, {{ $partner->longitude ?? '-' }}</p>
        <p><strong>Status:</strong>
            <span class="badge bg-{{ $partner->validation_status == 'validated' ? 'success' : 'secondary' }}">
                {{ ucfirst($partner->validation_status) }}
            </span>
        </p>
        <div id="map" style="height: 350px; border-radius: 10px;"></div>
    </div>

    <a href="{{ route('dashboard.partners.index') }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
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
@endsection
