@extends('layouts.app')

@section('title', 'GIS UMKM Batik Sumenep')

@section('content')
    <div class="container mt-5 vh-100">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-geo-alt-fill me-1"></i> GIS UMKM Batik Sumenep
            </div>
            <div class="card-body">
                {{-- Peta --}}
                <div id="map" style="height: 500px; border-radius: 12px;"></div>

                {{-- Daftar UMKM tanpa koordinat --}}
                @if($partners_no_coords->count() > 0)
                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>{{ $partners_no_coords->count() }}</strong> UMKM belum memiliki koordinat lokasi.
                    </div>
                    <ul class="list-group">
                        @foreach($partners_no_coords->toArray() as $p)
                            <li class="list-group-item">
                                <strong>{{ $p->business_name }}</strong> — {{ $p->address }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            width: 100%;
            height: 500px;
        }

        .leaflet-popup-content {
            font-size: 0.9rem;
        }

        .cluster-marker {
            background-color: rgba(0, 123, 255, 0.8);
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-weight: bold;
        }
    </style>
@endpush

@push('scripts')
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Data dari controller Laravel
            const partners = @json($partners_with_coords);

            // Inisialisasi peta
            const map = L.map('map').setView([-7.0405, 113.8605], 10); // Default Sumenep
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>'
            }).addTo(map);

            // Warna ikon berdasarkan cluster
            const clusterColors = {
                1: 'red',
                2: 'blue',
                3: 'green',
                4: 'orange',
                5: 'purple'
            };

            const markers = L.featureGroup();

            // Tambahkan marker ke peta
            partners.forEach(p => {
                const color = clusterColors[p.cluster] || 'gray';
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="cluster-marker" style="background-color:${color}">${p.cluster}</div>`,
                    iconSize: [30, 30]
                });

                const popupContent = `
                        <div>
                            <h6 class="fw-bold mb-1">${p.business_name}</h6>
                            <small class="text-muted">${p.address}</small><br>
                            <b>Pemilik:</b> ${p.owner_name || '-'}<br>
                            <b>Pemasaran:</b> ${p.pemasaran || '-'}<br>
                            <b>Total Produksi:</b> ${p.total_quantity || 0} pcs<br>
                            <b>Produk:</b> ${p.product_names || '-'}<br>
                            <b>Cluster:</b> ${p.cluster || '-'}
                        </div>
                    `;

                const marker = L.marker([p.latitude, p.longitude], { icon: customIcon })
                    .bindPopup(popupContent)
                    .addTo(map);

                markers.addLayer(marker);
            });

            // Auto zoom ke semua marker
            if (markers.getLayers().length > 0) {
                map.fitBounds(markers.getBounds(), { padding: [50, 50] });
            }
        });
    </script>
@endpush
