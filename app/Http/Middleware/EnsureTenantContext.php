<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Single-tenant: pastikan ada institution aktif di database sebelum melayani request.
 * Tidak perlu resolusi pivot atau session — TenantContext membaca DB langsung.
 */
class EnsureTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null) {
            return $next($request);
        }

        // Super-admin tidak memerlukan institution aktif untuk mengakses aplikasi.
        if ($request->user()->hasRole('super-admin')) {
            return $next($request);
        }

        if (TenantContext::id() === null) {
            abort(503, 'Belum ada lembaga sertifikasi aktif. Hubungi administrator.');
        }

        return $next($request);
    }
}
