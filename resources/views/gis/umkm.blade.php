@extends('layouts.app')

@section('title', 'GIS UMKM Batik Sumenep')

@section('content')
<div class="container mt-5 vh-100">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-geo-alt-fill me-1"></i> GIS UMKM Batik Sumenep
            </div>
            <form method="GET" action="{{ route('dashboard.gis.umkm') }}" class="d-flex align-items-center">
                <select name="cluster" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    <option value="">Semua Cluster</option>
                    @foreach($clusters as $c)
                        <option value="{{ $c }}" {{ $clusterFilter == $c ? 'selected' : '' }}>Cluster {{ $c }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-light btn-sm">Filter</button>
            </form>
        </div>

        <div class="card-body">
            @if($partners_with_coords->isEmpty())
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Tidak ada data UMKM dengan koordinat valid untuk ditampilkan.
                </div>
            @else
                <div id="map" style="height: 550px; border-radius: 10px;"></div>
            @endif

            @if($partners_no_coords->count() > 0)
                <div class="alert alert-warning mt-4">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>{{ $partners_no_coords->count() }}</strong> UMKM belum memiliki koordinat lokasi:
                </div>
                <ul class="list-group">
                    @foreach($partners_no_coords as $p)
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
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
<style>
    #map { width: 100%; height: 550px; }
    .leaflet-popup-content { font-size: 0.9rem; }
    .legend {
        background: white;
        padding: 8px;
        line-height: 1.4;
        border-radius: 8px;
        box-shadow: 0 0 6px rgba(0,0,0,0.2);
    }
    .legend span {
        display: inline-block;
        width: 12px;
        height: 12px;
        margin-right: 6px;
        border-radius: 2px;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const partners = @json($partners_with_coords);
    if (partners.length === 0) return;

    const map = L.map('map').setView([-7.0405, 113.8605], 10);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>'
    }).addTo(map);

    // Warna berdasarkan cluster
    const clusterColors = {
        1: '#FF0000',
        2: '#007BFF',
        3: '#28A745',
        4: '#FFC107',
        5: '#6F42C1',
        6: '#20C997',
        7: '#FD7E14'
    };

    const markers = L.markerClusterGroup();

    partners.forEach(p => {
        const color = clusterColors[p.cluster] || '#999999';
        const icon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="background:${color};color:#fff;border-radius:50%;width:28px;height:28px;line-height:28px;text-align:center;font-weight:bold;">${p.cluster}</div>`,
            iconSize: [28, 28]
        });

        const popup = `
            <div>
                <h6 class="fw-bold mb-1">${p.business_name}</h6>
                <small class="text-muted">${p.address}</small><br>
                <b>Pemilik:</b> ${p.owner_name || '-'}<br>
                <b>Pemasaran:</b> ${p.pemasaran || '-'}<br>
                <b>Produk:</b> ${p.product_names || '-'}<br>
                <b>Total Produksi:</b> ${p.total_quantity || 0} pcs<br>
                <b>Cluster:</b> ${p.cluster || '-'}
            </div>
        `;

        const marker = L.marker([p.latitude, p.longitude], { icon }).bindPopup(popup);
        markers.addLayer(marker);
    });

    map.addLayer(markers);
    map.fitBounds(markers.getBounds(), { padding: [50, 50] });

    // 🧭 Tambahkan legenda warna cluster
    const legend = L.control({ position: "bottomright" });
    legend.onAdd = function () {
        const div = L.DomUtil.create("div", "legend");
        let html = "<strong>Legenda Cluster</strong><br>";
        for (const [c, color] of Object.entries(clusterColors)) {
            html += `<span style="background:${color}"></span> Cluster ${c}<br>`;
        }
        div.innerHTML = html;
        return div;
    };
    legend.addTo(map);
});
</script>
@endpush
