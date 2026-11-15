<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'guru' => redirect()->route('guru.dashboard'),
                'siswa' => redirect()->route('siswa.dashboard'),
                default => redirect('/'),
            };
        }

        $settings = Setting::allKeyValue();
        return view('auth.login', compact('settings'));
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required' => 'Alamat email sekolah wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $throttleKey = Str::transliterate(Str::lower($validated['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->withInput($request->only('email'));
        }

        $profile = Profile::where('email', $validated['email'])->first();

        if (!$profile || !Hash::check($validated['password'], $profile->password)) {
            RateLimiter::hit($throttleKey, 60);

            ActivityLogger::log(
                actionType: 'LOGIN_FAILED',
                target: 'Portal Login',
                metadata: ['email' => $validated['email'], 'reason' => 'Kredensial tidak valid'],
                level: 'WARN',
                actorEmail: $validated['email'],
                actorRole: $profile?->role ?? 'guest'
            );

            return back()->withErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan tidak sesuai.',
            ])->withInput($request->only('email'));
        }

        // Check active status on profile and related guru/siswa record
        $roleActive = true;
        if ($profile->role === 'guru' && $profile->guru && !$profile->guru->is_active) {
            $roleActive = false;
        }
        if ($profile->role === 'siswa' && $profile->siswa && !$profile->siswa->is_active) {
            $roleActive = false;
        }

        if (!$profile->is_active || !$roleActive) {
            ActivityLogger::log(
                actionType: 'LOGIN_FAILED',
                target: 'Portal Login (Akun Nonaktif)',
                metadata: ['email' => $profile->email, 'reason' => 'Akun dinonaktifkan'],
                level: 'WARN',
                actorEmail: $profile->email,
                actorRole: $profile->role
            );

            return back()->withErrors([
                'email' => 'Akun Anda saat ini berstatus Nonaktif dan tidak dapat digunakan untuk masuk. Silakan hubungi Administrator Sekolah.',
            ])->withInput($request->only('email'));
        }

        RateLimiter::clear($throttleKey);

        Auth::login($profile, $request->boolean('remember'));
        $request->session()->regenerate();

        ActivityLogger::log(
            actionType: 'LOGIN_SUCCESS',
            target: 'Session Auth',
            metadata: ['role' => $profile->role, 'nama' => $profile->nama],
            level: 'INFO',
            actorEmail: $profile->email,
            actorRole: $profile->role
        );

        return match ($profile->role) {
            'admin' => redirect()->route('admin.dashboard')->with('toast_success', "Selamat datang kembali, {$profile->nama}!"),
            'guru' => redirect()->route('guru.dashboard')->with('toast_success', "Selamat datang, {$profile->nama}!"),
            'siswa' => redirect()->route('siswa.dashboard')->with('toast_success', "Semangat magang, {$profile->nama}!"),
            default => redirect('/'),
        };
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLogger::log(
                actionType: 'LOGOUT',
                target: 'Session Auth',
                metadata: ['role' => $user->role],
                level: 'INFO',
                actorEmail: $user->email,
                actorRole: $user->role
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('toast_success', 'Anda telah berhasil keluar dari sesi.');
    }
}

