@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h3 class="mb-4">Tambah UMKM Baru</h3>
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
    <form action="{{ route('dashboard.partners.store') }}" method="POST">
        @csrf
        <div class="row">
            @if(auth()->user()->role_id == 1)
            <div class="col-md-6 mb-3">
                <label>Pilih Pemilik (User)</label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Pilih User --</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            @endif

            <div class="col-md-6 mb-3">
                <label>Nama Usaha</label>
                <input type="text" name="business_name" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Nama Pemilik</label>
            <input type="text" name="owner_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Alamat Usaha</label>
            <textarea name="address" class="form-control" rows="2" required></textarea>
        </div>

        <div class="mb-3">
            <label>Nomor Telepon</label>
            <input type="text" name="contact" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Deskripsi Usaha</label>
            <textarea name="description" class="form-control" rows="2" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Pilih Lokasi Usaha di Peta</label>
            <div id="map" style="height: 400px;"></div>
            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

            @error('map')
            <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
{{-- Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const defaultLat = -7.005145;
        const defaultLng = 113.863250;
        const map = L.map('map').setView([defaultLat, defaultLng], 13);

        // Tile Layer (Peta Dasar)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Marker awal
        let marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        // Set nilai awal
        document.getElementById('latitude').value = defaultLat;
        document.getElementById('longitude').value = defaultLng;

        // Event klik pada peta
        map.on('click', function(e) {
            const {
                lat,
                lng
            } = e.latlng;
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        });

        // Event drag marker
        marker.on('dragend', function(e) {
            const {
                lat,
                lng
            } = marker.getLatLng();
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        });
    });
</script>
@endsection
