<?php

namespace App\Http\Controllers\Asesi;

use App\Http\Controllers\Controller;
use App\Models\Asesi;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class ProfilController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $asesi = $this->asesi($request);

        if ($asesi === null) {
            return redirect()->route('dashboard')->with('error', 'Profil asesi tidak ditemukan.');
        }

        return view('asesi.profil', [
            'asesi' => $asesi->load(['user', 'province', 'city']),
            'provinces' => Province::orderBy('name')->get(),
            'cities' => City::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $asesi = $this->asesi($request);

        if ($asesi === null) {
            return redirect()->route('dashboard')->with('error', 'Profil asesi tidak ditemukan.');
        }

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->where('institution_id', TenantContext::id())
                    ->ignore($user->id),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat_rumah' => ['required', 'string', 'max:1000'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'provinsi_code' => ['required', 'string', 'max:2'],
            'kota_code' => ['required', 'string', 'max:4'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'telp_rumah' => ['nullable', 'string', 'max:50'],
            'telp_kantor' => ['nullable', 'string', 'max:50'],
            'pendidikan_terakhir' => ['required', 'string', 'max:100'],
            'pekerjaan_instansi' => ['nullable', 'string', 'max:200'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'alamat_kantor' => ['nullable', 'string', 'max:1000'],
            'kebangsaan' => ['required', 'string', 'max:100'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        $asesi->update(collect($validated)->except(['name', 'email', 'phone'])->all());

        activity('asesi')
            ->causedBy($user)
            ->performedOn($asesi)
            ->log('Asesi memperbarui profil pokok.');

        return redirect()
            ->route('asesi.profil.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    private function asesi(Request $request): ?Asesi
    {
        return Asesi::with('user')->where('user_id', $request->user()->id)->first();
    }
}
