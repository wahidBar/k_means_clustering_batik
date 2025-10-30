@extends('layouts.app')
@section('content')
    <div class="container py-5">
        <h3 class="mb-4">{{ isset($product) ? 'Edit Produk' : 'Tambah Produk' }}</h3>
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
        <form method="POST" enctype="multipart/form-data"
            action="{{ isset($product) ? route('dashboard.products.update', $product) : route('dashboard.products.store') }}">
            @csrf
            @if(isset($product))
                @method('PUT')
            @endif

            @if(Auth::user()->role_id == 1)
                <div class="mb-3">
                    <label class="form-label">Pilih UMKM</label>
                    <select name="partner_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        @foreach($partners as $p)
                            <option value="{{ $p->partner_id }}" {{ (old('partner_id', $product->partner_id ?? '') == $p->partner_id) ? 'selected' : '' }}>
                                {{ $p->business_name }} ({{ $p->user->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="partner_id" value="{{ $partners->first()->partner_id ?? '' }}">
            @endif

            <div class="mb-3">
                <label class="form-label">Jenis Batik</label>
                <select name="type_id" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" {{ (old('type_id', $product->type_id ?? '') == $t->id) ? 'selected' : '' }}>
                            {{ $t->type_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Produk</label>
                <input type="text" name="product_name" class="form-control"
                    value="{{ old('product_name', $product->product_name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control"
                    rows="3">{{ old('description', $product->description ?? '') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Harga</label>

                <!-- Input tampilan -->
                <input type="text" id="price_display" class="form-control" autocomplete="off" inputmode="numeric"
                    placeholder="Rp. 0"
                    value="{{ old('price', isset($product) && $product->price ? 'Rp. ' . number_format($product->price, 0, ',', '.') : '') }}">

                <!-- Input tersembunyi untuk nilai asli -->
                <input type="hidden" name="price" id="price_raw"
                    value="{{ old('price', isset($product) ? $product->price : '') }}">
            </div>


            <div class="mb-3">
                <label class="form-label">Gambar Produk</label>
                <input type="file" name="image" class="form-control">
                @if(isset($product) && $product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" width="120" class="mt-2 rounded">
                @endif
            </div>

            <button class="btn btn-primary">{{ isset($product) ? 'Update' : 'Simpan' }}</button>
            <a href="{{ route('dashboard.products.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const display = document.getElementById('price_display');
        const raw = document.getElementById('price_raw');

        function onlyDigits(val) {
            return val.replace(/\D/g, '');
        }

        function formatRupiah(val) {
            return 'Rp. ' + new Intl.NumberFormat('id-ID').format(val);
        }

        display.addEventListener('input', function (e) {
            const digits = onlyDigits(e.target.value);
            raw.value = digits;
            display.value = digits ? formatRupiah(digits) : '';
        });

        display.addEventListener('keydown', function (e) {
            const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
            if (!/[0-9]/.test(e.key) && !allowed.includes(e.key)) {
                e.preventDefault();
            }
        });

        // initialize display dari hidden
        if (raw.value) display.value = formatRupiah(raw.value);
    });
</script>
