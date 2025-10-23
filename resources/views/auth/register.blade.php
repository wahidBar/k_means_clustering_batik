@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-sm p-4" style="width: 420px;">
        <h4 class="text-center fw-bold mb-3 text-primary">Daftar Akun</h4>
        <p class="text-center text-muted mb-4">Buat akun baru untuk melanjutkan.</p>

        {{-- Alert --}}
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Alamat</label>
                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Kontak</label>
                <input type="text" name="contact" class="form-control" value="{{ old('contact') }}">
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold">Daftar</button>

            <div class="text-center mt-3">
                <p class="mb-0">Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none">Masuk di sini</a></p>
            </div>
        </form>
    </div>
</div>
@endsection
