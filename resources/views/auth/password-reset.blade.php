@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
        <h4 class="text-center mb-3">Reset Password</h4>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" value="{{ $email ?? old('email') }}" required class="form-control @error('email') is-invalid @enderror">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label>Password Baru</label>
                <input type="password" name="password" required class="form-control @error('password') is-invalid @enderror">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required class="form-control">
            </div>

            <button type="submit" class="btn btn-success w-100">Reset Password</button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('login') }}">← Kembali ke Login</a>
        </div>
    </div>
</div>
@endsection
