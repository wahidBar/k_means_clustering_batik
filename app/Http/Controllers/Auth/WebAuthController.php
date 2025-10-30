<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\CustomVerifyEmail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;


class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }


    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // 🔒 Jika email belum terverifikasi
            if (is_null($user->email_verified_at)) {
                Auth::logout();

                // Simpan email ke session agar tombol "Kirim Ulang" tahu targetnya
                $request->session()->put('unverified_email', $user->email);

                return back()
                    ->withInput($request->only('email'))
                    ->with('warning', 'Email kamu belum terverifikasi. Silakan cek inbox atau klik tombol di bawah untuk mengirim ulang verifikasi.');
            }

            // ✅ Jika sudah diverifikasi, buat JWT
            $token = JWTAuth::fromUser(Auth::user());
            session(['jwt_token' => $token]);

            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Berhasil login sebagai ' . $user->name);
        }

        return back()
            ->withErrors(['email' => 'Email atau password tidak valid'])
            ->withInput($request->only('email'));
    }


    public function register(Request $request)
    {
        // ✅ Validasi input
        $validated = $request->validate([
            // 'role_id' => 'required|exists:roles,id',
            'name' => 'required|string|max:100',
            'full_name' => 'nullable|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'address' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
        ]);

        // ✅ Upload foto jika ada
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('users', 'public');
            $validated['image'] = $path;
        }

        // ✅ Hash password
        $validated['role_id'] = 3;
        $validated['password'] = Hash::make($validated['password']);

        // ✅ Simpan user baru
        $user = User::create($validated);

        // 🔗 Buat URL verifikasi (valid 24 jam)
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 📩 Kirim email verifikasi custom markdown
        Mail::to($user->email)->send(new CustomVerifyEmail($user, $verificationUrl));

        // event(new Registered($user));

        Auth::login($user);

        return redirect('/dashboard')
            ->with('success', 'Pendaftaran berhasil! Silakan verifikasi email Anda sebelum melanjutkan.');
    }

    public function logout(Request $request)
    {
        if ($token = session('jwt_token')) {
            try {
                JWTAuth::invalidate($token);
            } catch (\Exception $e) {
            }
            session()->forget('jwt_token');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function sendVerification(Request $request)
    {
        $email = $request->session()->get('unverified_email');

        if (!$email) {
            return back()->with('warning', 'Tidak ada email yang perlu diverifikasi.');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->with('warning', 'User tidak ditemukan.');
        }

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Mail::to($user->email)->send(new CustomVerifyEmail($user, $verificationUrl));

        return back()->with('success', 'Link verifikasi telah dikirim ulang ke ' . $user->email);
    }

    // 🔹 Tampilkan halaman form lupa password
    public function showLinkRequestForm()
    {
        return view('auth.email');
    }

    // 🔹 Kirim link reset password ke email
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // 🔹 Tampilkan form reset password
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.password-reset', ['token' => $token, 'email' => $request->email]);
    }

    // 🔹 Proses reset password
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
