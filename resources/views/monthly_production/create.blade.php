@extends('layouts.app')
@section('content')
    <div class="container vh-100">
        <h4 class="mb-4">Tambah Produksi Bulanan</h4>
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
        <form action="{{ route('dashboard.monthly_production.store') }}" method="POST">
            @csrf
            {{-- ADMIN: pilih partner --}}
            @if($user->role_id == 1)
                <div class="mb-3">
                    <label class="form-label">Pilih Partner</label>
                    <select name="partner_id" id="partnerSelect" class="form-select" required>
                        <option value="">-- Pilih Partner --</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->partner_id }}">{{ $partner->business_name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- PARTNER --}}
            @if($user->role_id == 2)
                <div class="mb-3">
                    <label class="form-label">Pilih Partner</label>
                    <select class="form-select" disabled>
                        <option value="{{ $partners->first()?->partner_id }}">{{ $partners->first()?->business_name }}</option>
                    </select>
                    {{-- hidden agar value tetap terkirim --}}
                    <input type="hidden" name="partner_id" value="{{ $partners->first()?->partner_id }}">
                </div>
            @endif

            {{-- PRODUCT --}}
            <div class="mb-3">
                <label class="form-label">Pilih Produk</label>
                <select name="product_id" id="productSelect" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->product_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Bulan Produksi</label>
                <input type="month" name="month" class="form-control"
                    value="{{ old('month', isset($monthly_production) ? date('Y-m', strtotime($monthly_production->month)) : '') }}"
                    required>
            </div>


            <div class="mb-3">
                <label class="form-label">Total Produksi (pcs)</label>
                <input type="number" name="total_quantity" class="form-control" min="1" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan Produksi</label>
                <textarea name="production_notes" class="form-control" rows="3"></textarea>
            </div>

            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('dashboard.monthly_production.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    {{-- AJAX Script untuk Admin --}}
    @if($user->role_id == 1)
        <script>
            document.getElementById('partnerSelect').addEventListener('change', function () {
                const partnerId = this.value;
                const productSelect = document.getElementById('productSelect');
                productSelect.innerHTML = '<option value="">Memuat produk...</option>';

                if (partnerId) {
                    fetch(`/dashboard/monthly-production/partner/${partnerId}/products`)
                        .then(res => res.json())
                        .then(data => {
                            productSelect.innerHTML = '<option value="">-- Pilih Produk --</option>';
                            data.forEach(prod => {
                                const option = document.createElement('option');
                                option.value = prod.id;
                                option.textContent = prod.product_name;
                                productSelect.appendChild(option);
                            });
                        })
                        .catch(() => {
                            productSelect.innerHTML = '<option value="">Gagal memuat produk</option>';
                        });
                } else {
                    productSelect.innerHTML = '<option value="">-- Pilih Produk --</option>';
                }
            });
        </script>
    @endif
@endsection
