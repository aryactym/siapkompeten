<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Pastikan pengguna memiliki salah satu dari peran yang diizinkan (OWASP A01).
     * Super admin selalu memiliki akses ke semua peran.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        // Super-admin platform has full administrative privileges
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! $user->hasAnyRole($roles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses area ini.');
        }

        return $next($request);
    }
}
