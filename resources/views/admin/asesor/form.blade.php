@extends('layouts.admin')

@section('title', $asesor->exists ? 'Edit Profil Asesor' : 'Tambah Asesor')
@section('pretitle', 'Asesor & Penugasan')
@section('page-title', $asesor->exists ? 'Edit Profil Asesor' : 'Tambah Profil Asesor')

@section('content')
<form method="POST"
      action="{{ $asesor->exists ? route('admin.asesor.update', $asesor) : route('admin.asesor.store') }}">
    @csrf
    @if ($asesor->exists)
        @method('PUT')
    @endif

    <div class="row row-cards">
        <div class="col-lg-8">
            <x-tabler.card title="Identitas Asesor">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Akun Pengguna</label>
                        @if ($asesor->exists)
                            <input type="text" class="form-control" value="{{ $asesor->user?->name }} ({{ $asesor->user?->email }})" disabled>
                        @else
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Pilih akun berperan asesor</option>
                                @foreach ($candidates as $candidate)
                                    <option value="{{ $candidate->id }}" @selected(old('user_id') === $candidate->id)>
                                        {{ $candidate->name }} — {{ $candidate->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if ($candidates->isEmpty())
                                <div class="form-hint text-warning">Tidak ada akun asesor tanpa profil. Buat pengguna berperan asesor terlebih dahulu.</div>
                            @endif
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label required">Nomor Register Asesor</label>
                        <input type="text" name="no_register" class="form-control @error('no_register') is-invalid @enderror"
                               value="{{ old('no_register', $asesor->no_register) }}" required maxlength="100"
                               placeholder="Contoh: MET.000.001234 2026">
                        @error('no_register')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Gelar Depan</label>
                        <input type="text" name="gelar_depan" class="form-control" value="{{ old('gelar_depan', $asesor->gelar_depan) }}" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Lengkap (dari akun)</label>
                        <input type="text" class="form-control" value="{{ $asesor->user?->name }}" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gelar Belakang</label>
                        <input type="text" name="gelar_belakang" class="form-control" value="{{ old('gelar_belakang', $asesor->gelar_belakang) }}" maxlength="50">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="">—</option>
                            <option value="L" @selected(old('jenis_kelamin', $asesor->jenis_kelamin) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', $asesor->jenis_kelamin) === 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $asesor->tempat_lahir) }}" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $asesor->tanggal_lahir?->toDateString()) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Provinsi</label>
                        <select name="provinsi_code" class="form-select">
                            <option value="">—</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->code }}" @selected(old('provinsi_code', $asesor->province_code) === $province->code)>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kota / Kabupaten</label>
                        <select name="kota_code" class="form-select">
                            <option value="">—</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->code }}" @selected(old('kota_code', $asesor->city_code) === $city->code)>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2" maxlength="1000">{{ old('alamat', $asesor->alamat) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kode Pos</label>
                        <input type="text" name="kode_pos" class="form-control" value="{{ old('kode_pos', $asesor->kode_pos) }}" maxlength="10">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="telepon" class="form-control" value="{{ old('telepon', $asesor->telepon) }}" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email Profesional</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $asesor->email ?? $asesor->user?->email) }}" maxlength="150">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </x-tabler.card>
        </div>

        <div class="col-lg-4">
            <x-tabler.card title="Lisensi & Kompetensi">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nomor Lisensi</label>
                        <input type="text" name="no_lisensi" class="form-control" value="{{ old('no_lisensi', $asesor->no_lisensi) }}" maxlength="100">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Lisensi Berlaku Hingga</label>
                        <input type="date" name="lisensi_berlaku_hingga" class="form-control"
                               value="{{ old('lisensi_berlaku_hingga', $asesor->lisensi_berlaku_hingga?->toDateString()) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Pendidikan Terakhir</label>
                        <input type="text" name="pendidikan_terakhir" class="form-control" value="{{ old('pendidikan_terakhir', $asesor->pendidikan_terakhir) }}" maxlength="100">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Bidang Keahlian</label>
                        <input type="text" name="bidang_keahlian" class="form-control" value="{{ old('bidang_keahlian', $asesor->bidang_keahlian) }}" maxlength="150">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Institusi Asal</label>
                        <input type="text" name="institusi_asal" class="form-control" value="{{ old('institusi_asal', $asesor->institusi_asal) }}" maxlength="200">
                    </div>
                    <div class="col-12">
                        <label class="form-label required">Kebangsaan</label>
                        <input type="text" name="kebangsaan" class="form-control" value="{{ old('kebangsaan', $asesor->kebangsaan ?? 'Indonesia') }}" required maxlength="100">
                    </div>
                    <div class="col-12">
                        <label class="form-label required">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="aktif" @selected(old('status', $asesor->status ?? 'aktif') === 'aktif')>Aktif</option>
                            <option value="nonaktif" @selected(old('status', $asesor->status) === 'nonaktif')>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Profil
                    </button>
                    <a href="{{ route('admin.asesor.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </x-tabler.card>
        </div>
    </div>
</form>
@endsection
