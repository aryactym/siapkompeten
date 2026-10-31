<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ActivationController extends Controller
{
    public function show(Request $request, User $user): View|RedirectResponse
    {
        if ($user->last_login_at !== null) {
            return redirect()
                ->route('login')
                ->with('error', 'Tautan aktivasi sudah pernah digunakan. Silakan masuk atau hubungi admin LSP.');
        }

        return view('auth.activate', [
            'user' => $user,
            'action' => URL::temporarySignedRoute(
                'asesi.activate.store',
                now()->addHour(),
                ['user' => $user->id],
            ),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        if ($user->last_login_at !== null) {
            return redirect()
                ->route('login')
                ->with('error', 'Tautan aktivasi sudah pernah digunakan. Silakan masuk atau hubungi admin LSP.');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->forceFill([
            'password' => $validated['password'],
            'last_login_at' => now(),
        ])->save();

        activity('auth')
            ->causedBy($user)
            ->performedOn($user)
            ->log('Akun asesi hasil impor mengaktifkan kata sandi.');

        return redirect()
            ->route('login')
            ->with('success', 'Kata sandi berhasil dibuat. Silakan masuk dengan NIK atau email Anda.');
    }
}
