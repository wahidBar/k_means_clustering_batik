@component('mail::message')
<div style="text-align:center; margin-bottom:20px;">
    <img src="{{ asset('images/logo_batik.png') }}" alt="Logo Batik Sumenep" style="width:80px;">
</div>

# Selamat Datang di **BATIK SUMENEP** 🌸

Halo, **{{ $user->full_name ?? $user->name }}** 👋
Terima kasih telah mendaftar di platform kami.

Silakan klik tombol di bawah ini untuk **memverifikasi alamat email kamu**.

@component('mail::button', ['url' => $verificationUrl])
✅ Verifikasi Email Sekarang
@endcomponent

Jika kamu tidak merasa membuat akun, abaikan email ini.

Salam hangat,
**Tim BATIK SUMENEP**

<hr>
<p style="font-size:12px; color:#888;">
    Jika tombol tidak berfungsi, salin tautan ini ke browser:
    <br>
    <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
</p>
@endcomponent
