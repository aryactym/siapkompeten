<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotBlocked
{
    /**
     * Pastikan akun pengguna tidak berstatus 'diblokir' atau 'nonaktif'.
     * Bila terblokir/nonaktif, sesi aktif langsung diputus (fail closed - OWASP A07).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            if ($user->isBlocked() || ! $user->isActive()) {
                $status = $user->status;
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = $status === 'diblokir'
                    ? 'Akun Anda telah diblokir. Silakan hubungi administrator.'
                    : 'Akun Anda saat ini tidak aktif.';

                return redirect()->route('login')->withErrors([
                    'login' => $message,
                ]);
            }
        }

        return $next($request);
    }
}
