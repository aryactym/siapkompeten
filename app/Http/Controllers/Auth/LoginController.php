<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginInput = trim((string) ($request->input('login') ?: $request->input('email')));

        if (blank($loginInput)) {
            throw ValidationException::withMessages([
                'login' => 'Silakan masukkan Email, NIK, atau Username.',
            ]);
        }

        // Resolusi pengguna berdasarkan Email, NIK (16 digit), atau Username (FR-AUTH-01)
        $user = null;

        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginInput)->first();
        } elseif (preg_match('/^\d{16}$/', $loginInput)) {
            $nikHash = User::hashNik($loginInput);
            $user = User::where('nik_hash', $nikHash)->first();
        } else {
            $user = User::where('username', $loginInput)->first();
        }

        // Validasi kata sandi & cegah enumerasi akun dengan pesan generik (OWASP A07)
        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Kredensial yang dimasukkan tidak sesuai.',
            ]);
        }

        // Pengecekan status akun (diblokir / nonaktif)
        if ($user->isBlocked() || ! $user->isActive()) {
            throw ValidationException::withMessages([
                'login' => $user->isBlocked()
                    ? 'Akun Anda telah diblokir. Silakan hubungi administrator.'
                    : 'Akun Anda saat ini tidak aktif.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Update catatan login terakhir (OWASP A09)
        $user->last_login_at = now();
        $user->last_login_ip = $request->ip();
        $user->is_locked = false;
        $user->saveQuietly();

        // Tetapkan konteks tenant aktif
        $accessible = $user->accessibleInstitutions();
        $defaultInst = $accessible->firstWhere('id', $user->institution_id) ?? $accessible->first();

        if (! $user->hasRole('super-admin')) {
            if ($defaultInst === null) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'login' => 'Akun Anda belum terhubung ke lembaga aktif mana pun.',
                ]);
            }

            TenantContext::set($defaultInst);
        } else {
            if ($defaultInst) {
                TenantContext::set($defaultInst);
            }
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Tampilkan lock screen (FR-AUTH-02).
     */
    public function lock(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $user->is_locked = true;
        $user->saveQuietly();

        return view('auth.lock', [
            'user' => $user,
        ]);
    }

    /**
     * Buka kunci layar dengan kata sandi (FR-AUTH-02).
     */
    public function unlock(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Kata sandi tidak sesuai.',
            ]);
        }

        $user->is_locked = false;
        $user->saveQuietly();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        TenantContext::clear();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
