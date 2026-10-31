<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotLocked
{
    /**
     * Pastikan pengguna yang sedang mengunci layar dialihkan ke halaman lock screen (FR-AUTH-02).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->is_locked) {
            if (! $request->routeIs('lock', 'lock.unlock', 'logout')) {
                return redirect()->route('lock');
            }
        }

        return $next($request);
    }
}
