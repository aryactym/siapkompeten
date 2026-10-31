<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureSetupCompleted
{
    /**
     * Memastikan konfigurasi awal LSP (wizard) telah diselesaikan (FR-SET-01).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika tabel app_settings belum ada, lewati
        if (! Schema::hasTable('app_settings')) {
            return $next($request);
        }

        // Rute yang dikecualikan dari pengecekan setup
        $excludedRoutes = [
            'setup',
            'setup.*',
            'logout',
        ];

        foreach ($excludedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Pada fase 0 / sebelum wizard diwajibkan secara ketat, izinkan request berjalan
        return $next($request);
    }
}
