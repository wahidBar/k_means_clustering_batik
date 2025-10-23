@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <div class="card shadow-lg border-0" style="width: 400px; border-radius: 15px;">
        <div class="card-body p-4">
            <h3 class="text-center fw-bold mb-n5 text-primary">Login ke SIG Batik Sumenep</h3>

            {{-- ✅ Tampilkan ALERT pesan dari Session --}}
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-envelope-exclamation-fill me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            {{-- ✅ Tambah tombol kirim ulang verifikasi (optional) --}}
            @if (session('unverified_email'))
            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('unverified_email') }}">
                <button type="submit" class="btn btn-sm btn-warning w-100 mt-2 fw-semibold">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>
            @endif
            @endif

            {{-- ✅ Menangkap error dari ->withErrors() --}}
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif


            {{-- ✅ FORM LOGIN --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email"
                        name="email"
                        id="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required autofocus>
                    @error('email')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                    <input type="password"
                        name="password"
                        id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required>
                    @error('password')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Ingat saya</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-decoration-none">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-semibold">Masuk</button>
            </form>
        </div>

        <div class="text-center text-muted small mb-3">
            © {{ date('Y') }} SIG Batik Sumenep. All Rights Reserved.
        </div>
    </div>
</div>
@endsection
