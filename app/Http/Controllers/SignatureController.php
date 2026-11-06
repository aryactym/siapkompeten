<?php

namespace App\Http\Controllers;

use App\Support\SignatureImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SignatureController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        return view('signature.manage', [
            'user' => $user,
            'hasSignature' => filled($user->signature_data),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'signature_source' => ['required', 'in:upload,canvas'],
            'signature_file' => ['required_if:signature_source,upload', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'signature_canvas' => ['required_if:signature_source,canvas', 'string', 'max:4000000'],
        ]);

        $user = auth()->user();
        $binary = $validated['signature_source'] === 'upload'
            ? file_get_contents($validated['signature_file']->getRealPath())
            : base64_decode(str_replace('data:image/png;base64,', '', $validated['signature_canvas'] ?? ''), true);

        if (! is_string($binary) || $binary === '') {
            throw ValidationException::withMessages([
                'signature' => 'Gambar tidak valid.',
            ]);
        }

        try {
            $payload = SignatureImage::encodePng($binary);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'signature' => $exception->getMessage(),
            ]);
        }

        $user->forceFill([
            'signature_data' => $payload['data'],
            'signature_mime' => 'image/png',
            'signature_hash' => $payload['hash'],
            'signature_uploaded_at' => now(),
            'signature_path' => null,
        ])->save();

        activity('signature')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['action' => 'store', 'hash' => $payload['hash']])
            ->log('Pengguna memperbarui tanda tangan.');

        return $this->redirectToSignaturePage()
            ->with('success', 'Tanda tangan berhasil disimpan.');
    }

    public function delete(): RedirectResponse
    {
        $user = auth()->user();

        $user->forceFill([
            'signature_data' => null,
            'signature_mime' => null,
            'signature_hash' => null,
            'signature_uploaded_at' => null,
            'signature_path' => null,
        ])->save();

        activity('signature')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['action' => 'delete'])
            ->log('Pengguna menghapus tanda tangan.');

        return $this->redirectToSignaturePage()
            ->with('success', 'Tanda tangan berhasil dihapus.');
    }

    private function redirectToSignaturePage(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('asesor')) {
            return redirect()->route('asesor.signature.show');
        }

        if ($user->hasRole('admin') || $user->hasRole('super-admin') || $user->hasRole('manajer-sertifikasi')) {
            return redirect()->route('admin.signature.show');
        }

        return redirect()->route('asesi.signature.show');
    }
}
