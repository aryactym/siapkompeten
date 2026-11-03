<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SignatureController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        return view('signature.manage', [
            'user' => $user,
            'hasSignature' => ! blank($user->signature_path),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Validate selalu — source dan file/canvas sesuai source
        $validated = $request->validate([
            'signature_source' => ['required', 'in:upload,canvas'],
            'signature_file' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'signature_canvas' => ['string'],
        ]);

        $source = $validated['signature_source'];
        $user = auth()->user();

        // Delete old signature
        if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
            Storage::disk('public')->delete($user->signature_path);
        }

        try {
            if ($source === 'upload') {
                // Dari validated — middleware-safe
                if (! isset($validated['signature_file']) || blank($validated['signature_file'])) {
                    return back()->withErrors(['signature_file' => 'File harus diunggah.']);
                }

                $file = $validated['signature_file'];

                // Pola stream seperti PendaftaranStorage — reliable di Windows
                $tempPath = $file->getPathname();

                if (blank($tempPath) || ! is_readable($tempPath)) {
                    return back()->withErrors(['signature_file' => 'Berkas sementara tidak dapat dibaca.']);
                }

                $stream = fopen($tempPath, 'rb');

                if ($stream === false) {
                    return back()->withErrors(['signature_file' => 'Berkas sementara tidak dapat dibuka.']);
                }

                $filename = sprintf('%s_%d.png', $user->id, now()->timestamp);
                $path = 'signatures/'.$filename;

                try {
                    $stored = Storage::disk('public')->put($path, $stream);
                } catch (\Exception $e) {
                    $stored = false;
                } finally {
                    fclose($stream);
                }

                if (! $stored) {
                    return back()->withErrors(['signature_file' => 'Berkas gagal disimpan.']);
                }
            } else {
                // Canvas
                $canvas = $validated['signature_canvas'] ?? '';
                if (blank($canvas)) {
                    return back()->withErrors(['signature_canvas' => 'Gambar tidak boleh kosong.']);
                }

                $base64 = str_replace('data:image/png;base64,', '', $canvas);
                $imageData = base64_decode($base64, true);

                if (! $imageData) {
                    return back()->withErrors(['signature_canvas' => 'Format gambar tidak valid.']);
                }

                $filename = sprintf('%s_%d.png', $user->id, now()->timestamp);
                Storage::disk('public')->put('signatures/'.$filename, $imageData);
                $path = 'signatures/'.$filename;
            }

            $user->update(['signature_path' => $path]);

            $routeName = $user->hasRole('asesor') ? 'asesor.signature.show' : 'asesi.signature.show';

            return redirect()
                ->route($routeName)
                ->with('success', 'Tanda tangan berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->withErrors(['signature' => 'Kesalahan: '.$e->getMessage()]);
        }
    }

    public function delete(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
            Storage::disk('public')->delete($user->signature_path);
        }

        $user->update(['signature_path' => null]);

        $routeName = $user->hasRole('asesor') ? 'asesor.signature.show' : 'asesi.signature.show';

        return redirect()
            ->route($routeName)
            ->with('success', 'Tanda tangan berhasil dihapus.');
    }
}
