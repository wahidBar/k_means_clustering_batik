<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BatikUmkmPartnerController;
use App\Mail\CustomVerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

// ======================================================
// 🏠 Root redirect
// ======================================================
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ======================================================
// 🔐 Authentication Routes
// ======================================================
Route::get('login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('login', [WebAuthController::class, 'login']);
Route::post('logout', [WebAuthController::class, 'logout'])->name('logout');

Route::get('register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('register', [WebAuthController::class, 'register']);

// ======================================================
// Reset Password Routes
// ======================================================
Route::get('forgot-password', [WebAuthController::class, 'showLinkRequestForm'])
    ->name('password.request');

Route::post('forgot-password', [WebAuthController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::get('reset-password/{token}', [WebAuthController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('reset-password', [WebAuthController::class, 'reset'])
    ->name('password.update');


// ======================================================
// 📧 Email Verification Routes
// ======================================================
Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('dashboard')->with('success', 'Email kamu berhasil diverifikasi!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $user = $request->user();

    if ($user->hasVerifiedEmail()) {
        return back()->with('message', 'Email kamu sudah diverifikasi.');
    }

    // Generate verification URL
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    // Kirim email custom
    Mail::to($user->email)->send(new CustomVerifyEmail($user, $verificationUrl));

    return back()->with('message', 'Link verifikasi custom telah dikirim ke email kamu!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

Route::post('/resend-verification', [WebAuthController::class, 'sendVerification'])->name('verification.resend');

// ======================================================
// 📊 Dashboard (User Area)
// ======================================================
Route::middleware(['auth', 'verified'])->group(function () {
    // /dashboard
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // /dashboard/partners
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::resource('partners', BatikUmkmPartnerController::class)->only(['index', 'show', 'create', 'edit', 'store', 'destroy', 'update']);
    });

});

// ======================================================
// ⚙️ Admin Routes
// ======================================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
});
