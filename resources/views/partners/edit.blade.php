@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="mb-4">Edit UMKM </h3>
    @if(session('error'))
    <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-warning mt-2">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form method="POST" action="{{ route('dashboard.partners.update', $partner->partner_id) }}">
        @csrf
        @method('PUT')

        @if(auth()->user()->role_id == 1)
        <div class="row">
            <div class="mb-3">
                <label>Pemilik (User)</label>
                <select name="user_id" class="form-select">
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ $partner->user_id == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="user_id" value="{{ $partner->user_id }}">
            @endif
        </div>
        <div class="mb-3">
            <label>Nama Usaha</label>
            <input type="text" name="business_name" class="form-control" value="{{ $partner->business_name }}" required>
        </div>

        <div class="mb-3">
            <label>Nama Pemilik</label>
            <input type="text" name="owner_name" class="form-control" value="{{ $partner->owner_name }}" required>
        </div>

        <div class="mb-3">
            <label>Alamat Usaha</label>
            <input type="text" name="address" class="form-control" value="{{ $partner->address }}" required>
        </div>

        <div class="mb-3">
            <label>Deskripsi Usaha</label>
            <textarea name="description" class="form-control" rows="3">{{ $partner->description }}</textarea>
        </div>
        <div class="mb-3">
            <label>Kontak</label>
            <input type="text" name="contact" class="form-control" value="{{ $partner->contact }}" required>
        </div>


        <div class="mb-3">
            <label class="form-label">Pilih Lokasi Usaha di Peta</label>
            <div id="map" style="height: 400px;"></div>

            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $partner->latitude) }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $partner->longitude) }}">

            @error('map')
            <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update</button>
    </form>
</div>

{{-- Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Gunakan nilai dari database jika ada, jika tidak gunakan default
        const defaultLat = parseFloat("{{ $partner->latitude ?? '-7.005145' }}");
        const defaultLng = parseFloat("{{ $partner->longitude ?? '113.863250' }}");

        const map = L.map('map').setView([defaultLat, defaultLng], 13);

        // Tile layer (peta dasar)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Marker awal (bisa digeser)
        let marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        // Pastikan input hidden diisi
        document.getElementById('latitude').value = defaultLat;
        document.getElementById('longitude').value = defaultLng;

        // Klik peta untuk ubah marker
        map.on('click', function(e) {
            const {
                lat,
                lng
            } = e.latlng;
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
        });

        // Drag marker untuk ubah koordinat
        marker.on('dragend', function(e) {
            const {
                lat,
                lng
            } = marker.getLatLng();
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
        });
    });
</script>
@endsection
