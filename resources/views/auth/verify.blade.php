@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-center align-items-center vh-100 bg-light">
    <!-- <div class="card shadow-lg border-0" style="width: 400px; border-radius: 15px;"> -->
        <div class="container text-center" style="max-width: 500px; margin-top: 100px;">
            <div class="card shadow-sm p-4">
                <h4 class="mb-3">Verifikasi Email Kamu</h4>
                <p>
                    Kami sudah mengirimkan link verifikasi ke email kamu.
                    Jika belum menerima, klik tombol di bawah ini untuk mengirim ulang.
                </p>

                @if (session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
                @endif

                <form method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-envelope-check"></i> Kirim Ulang Verifikasi
                    </button>
                </form>
            </div>
        </div>
    <!-- </div> -->
</div>
@endsection

