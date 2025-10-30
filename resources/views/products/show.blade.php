@extends('layouts.app')
@section('content')
<div class="container py-5 vh-100">
    <div class="card shadow-sm p-4">
        <div class="row">
            <div class="col-md-4 text-center">
                @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" class="img-fluid rounded mb-3">
                @else
                <div class="bg-light p-5 rounded">Tidak ada gambar</div>
                @endif
            </div>
            <div class="col-md-8">
                <h3>{{ $product->product_name }}</h3>
                <p class="text-muted mb-1">Jenis: {{ $product->type->type_name ?? '-' }}</p>
                <p class="text-muted">UMKM: {{ $product->partner->business_name ?? '-' }}</p>
                <p class="mt-3">{{ $product->description ?? '-' }}</p>
                <h5 class="mt-3">Harga: Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}</h5>
            </div>
        </div>
    </div>
</div>
@endsection
