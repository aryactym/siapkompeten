<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Asesi;
use App\Models\Institution;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;

class RegisterController extends Controller
{
    public function create(): View
    {
        $institution = $this->defaultInstitution();

        abort_if($institution === null, 503, 'Belum ada lembaga sertifikasi aktif.');

        return view('auth.register', [
            'institution' => $institution,
            'provinces' => Province::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $institution = $this->defaultInstitution();

        abort_if($institution === null, 503, 'Belum ada lembaga sertifikasi aktif.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nik' => [
                'required',
                'regex:/^\d{16}$/',
                function (string $attribute, mixed $value, \Closure $fail) use ($institution): void {
                    $exists = User::withoutGlobalScopes()
                        ->where('institution_id', $institution->id)
                        ->where('nik_hash', User::hashNik((string) $value))
                        ->exists();

                    if ($exists) {
                        $fail('NIK sudah terdaftar pada lembaga ini.');
                    }
                },
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->where('institution_id', $institution->id),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'alamat_rumah' => ['required', 'string', 'max:1000'],
            'provinsi_code' => ['required', 'string', 'max:2'],
            'kota_code' => [
                'required',
                'string',
                'max:4',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $provinsiCode = $request->input('provinsi_code');
                    if (blank($provinsiCode)) {
                        return; // provinsi_code sudah divalidasi required di atasnya
                    }
                    $valid = City::where('code', (string) $value)
                        ->where('province_code', (string) $provinsiCode)
                        ->exists();
                    if (! $valid) {
                        $fail('Kota/kabupaten tidak sesuai dengan provinsi yang dipilih.');
                    }
                },
            ],
            'pendidikan_terakhir' => ['required', 'string', 'max:100'],
            'consent' => ['accepted'],
        ]);

        TenantContext::set($institution);

        $user = DB::transaction(function () use ($validated, $institution): User {
            $user = User::create([
                'institution_id' => $institution->id,
                'name' => $validated['name'],
                'nik' => $validated['nik'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'status' => 'aktif',
            ]);

            $user->assignRole('asesi');
            $user->institutions()->attach($institution->id, ['role' => 'asesi']);

            $asesi = Asesi::create([
                'user_id' => $user->id,
                'no_registrasi' => $this->generateNoRegistrasi($institution->id),
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tempat_lahir' => $validated['tempat_lahir'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'kebangsaan' => 'Indonesia',
                'alamat_rumah' => $validated['alamat_rumah'],
                'provinsi_code' => $validated['provinsi_code'],
                'kota_code' => $validated['kota_code'],
                'pendidikan_terakhir' => $validated['pendidikan_terakhir'],
                'status_verifikasi' => 'belum_terverifikasi',
                'status_kompetensi' => 'belum_asesmen',
                'sumber' => 'mandiri',
            ]);

            activity('asesi')
                ->causedBy($user)
                ->performedOn($asesi)
                ->log('Registrasi mandiri asesi.');

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        TenantContext::set($institution);

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Registrasi berhasil. Lengkapi profil dan pilih skema sertifikasi Anda.');
    }

    private function defaultInstitution(): ?Institution
    {
        return Institution::query()
            ->where('is_active', true)
            ->orderByDesc('is_setup_completed')
            ->orderBy('name')
            ->first();
    }

    private function generateNoRegistrasi(string $institutionId): string
    {
        $prefix = now()->format('Ymd');
        $sequence = Asesi::withoutTenantScope()
            ->where('institution_id', $institutionId)
            ->where('no_registrasi', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (
            Asesi::withoutTenantScope()
                ->where('institution_id', $institutionId)
                ->where('no_registrasi', $number)
                ->exists()
        );

        return $number;
    }
}
