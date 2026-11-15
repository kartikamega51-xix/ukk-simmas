<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda telah dinonaktifkan oleh Administrator. Silakan hubungi pihak sekolah.',
            ]);
        }

        if (!in_array($user->role, $roles, true)) {
            return match ($user->role) {
                'admin' => redirect()->route('admin.dashboard')->with('toast_error', 'Anda tidak memiliki akses ke halaman tersebut.'),
                'guru' => redirect()->route('guru.dashboard')->with('toast_error', 'Anda tidak memiliki akses ke halaman tersebut.'),
                'siswa' => redirect()->route('siswa.dashboard')->with('toast_error', 'Anda tidak memiliki akses ke halaman tersebut.'),
                default => abort(403, 'Akses ditolak.'),
            };
        }

        return $next($request);
    }
}

