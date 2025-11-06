@extends('layouts.app')
@section('content')
<div class="container vh-100">
    <h4 class="mb-4">Perbarui Produksi Bulanan</h4>

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


    <form action="{{ route('dashboard.monthly_production.update', $production->production_id) }}" method="POST">
        @csrf
        @method('PUT')

        @if (Auth::user()->role_id == 2)
            <input type="hidden" name="partner_id" value="{{ $production->partner_id }}">
        @else
            <div class="mb-3">
                <label class="form-label">UMKM Partner</label>
                <select name="partner_id" class="form-select">
                    @foreach($partners as $partner)
                        <option value="{{ $partner->partner_id }}"
                            {{ $partner->partner_id == $production->partner_id ? 'selected' : '' }}>
                            {{ $partner->partner_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label">Produk</label>
            {{-- 👇 gunakan readonly + hidden agar tetap terkirim --}}
            <select name="product_id" class="form-select" disabled
                onfocus="this.blur()">
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" {{ $prod->id == $production->product_id ? 'selected' : '' }}>
                        {{ $prod->product_name }}
                    </option>
                @endforeach
            </select>
            <input type="hidden" name="product_id" value="{{ $production->product_id }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Bulan Produksi</label>
            <input type="month" name="month" class="form-control"
                value="{{ old('month', date('Y-m', strtotime($production->month))) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Total Produksi</label>
            <input type="number" name="total_quantity" class="form-control"
                value="{{ old('total_quantity', $production->total_quantity) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Catatan Produksi</label>
            <textarea name="production_notes" class="form-control">{{ old('production_notes', $production->production_notes) }}</textarea>
        </div>

        <button class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('dashboard.monthly_production.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
