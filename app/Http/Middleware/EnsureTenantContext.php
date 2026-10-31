<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Pastikan session lembaga aktif selalu terisi dan masih boleh diakses user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->hasRole('super-admin')) {
            $activeId = TenantContext::id();
            if ($activeId !== null) {
                $institution = Institution::where('is_active', true)->find($activeId);
                if ($institution) {
                    TenantContext::set($institution);
                }
            }

            return $next($request);
        }

        $institutions = $user->accessibleInstitutions();

        if ($institutions->isEmpty()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Akun Anda belum terhubung ke lembaga aktif mana pun.']);
        }

        $activeId = TenantContext::id();
        $active = $activeId !== null
            ? $institutions->firstWhere('id', $activeId)
            : null;

        if ($active === null) {
            $active = $institutions->firstWhere('id', $user->institution_id)
                ?? $institutions->first();
        }

        TenantContext::set($active);

        return $next($request);
    }
}
