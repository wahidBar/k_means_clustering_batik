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
        <form method="POST" action="{{ route('dashboard.partners.update', $partner->partner_id) }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" class="form-control"
                        value="{{ auth()->user()->name }} - ({{ auth()->user()->email }})" readonly>
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                </div>
                <div class="mb-3">
                    <label>Nama Usaha</label>
                    <input type="text" name="business_name" class="form-control" value="{{ $partner->business_name }}"
                        required>
                </div>

                <div class="mb-3">
                    <label>Nama Pemilik</label>
                    <input type="text" name="owner_name" class="form-control" value="{{ $partner->owner_name }}" required>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Pemasaran</label><br>
                    @php
                        $opsi = ['Lokal', 'Nasional', 'Luar Negeri'];
                      @endphp
                    @foreach($opsi as $item)
                        <label class="me-3">
                            <input type="checkbox" name="pemasaran[]" value="{{ $item }}" {{ isset($partner) && in_array($item, (array) $partner->pemasaran ?? []) ? 'checked' : '' }}>
                            {{ $item }}
                        </label>
                    @endforeach
                </div>
                <div class="mb-3">
                    <label for="nib" class="form-label">Nomor Induk Berusaha (NIB)</label>
                    <input type="text" id="nib" name="nib" class="form-control" value="{{ old('nib', $partner->nib) }}"
                        placeholder="Contoh: 1234567890123" maxlength="13"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                </div>

                <div class="mb-3">
                    <label for="images_partner" class="form-label">Foto/Logo UMKM</label><br>
                    @if($partner->images_partner)
                        <img src="{{ asset('storage/' . $partner->images_partner) }}" alt="Foto UMKM" width="120"
                            class="rounded mb-2 border">
                    @endif
                    <input type="file" name="images_partner" class="form-control" accept="image/*">
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
                @if (auth()->user()->role_id == 1)
                    <div class="mb-3 mt-4">
                        <label for="status" class="form-label fw-bold">Status Verifikasi</label>
                        <select name="validation_status" id="validation_status" class="form-select">
                            <option value="Pending" {{ old('validation_status', $partner->validation_status ?? '') == 'Pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="Terverifikasi" {{ old('validation_status', $partner->validation_status ?? '') == 'Terverifikasi' ? 'selected' : '' }}>Terverifikasi</option>
                            <option value="Tolak" {{ old('validation_status', $partner->validation_status ?? '') == 'Tolak' ? 'selected' : '' }}>Tolak
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label fw-bold">Catatan Verifikasi</label>
                        <textarea name="note" id="note" class="form-control" rows="3"
                            placeholder="Tuliskan catatan atau alasan penolakan (opsional)...">{{ old('note', $note) }}</textarea>
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Pilih Lokasi Usaha di Peta</label>
                    <div id="map" style="height: 400px;"></div>

                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $partner->latitude) }}">
                    <input type="hidden" name="longitude" id="longitude"
                        value="{{ old('longitude', $partner->longitude) }}">

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
        document.addEventListener("DOMContentLoaded", function () {
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
            map.on('click', function (e) {
                const {
                    lat,
                    lng
                } = e.latlng;
                marker.setLatLng([lat, lng]);
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
            });

            // Drag marker untuk ubah koordinat
            marker.on('dragend', function (e) {
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
