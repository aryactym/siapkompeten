@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('pretitle', 'Sistem')
@section('page-title', 'Tambah Pengguna')

@section('actions')
    <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Daftar Pengguna
    </a>
@endsection

@section('content')
<x-tabler.card title="Formulir Pengguna Baru">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.user.store') }}" autocomplete="off">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label required" for="name">Nama Lengkap</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required maxlength="150">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required maxlength="150">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}" maxlength="50">
                <div class="form-hint">Kosongkan jika login dengan email atau NIK.</div>
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="phone">Nomor HP</label>
                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}" maxlength="20" placeholder="08xxxxxxxxxx">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="nik">NIK (16 digit)</label>
                <input type="text" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror"
                       value="{{ old('nik') }}" inputmode="numeric" pattern="\d{16}" maxlength="16"
                       placeholder="3201xxxxxxxxxxxx">
                <div class="form-hint">Wajib untuk asesor dan asesi. Disimpan terenkripsi.</div>
                @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required" for="role">Peran</label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">— Pilih peran —</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required" for="password">Kata Sandi</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                       required minlength="8" autocomplete="new-password">
                <div class="form-hint">Minimal 8 karakter, kombinasi huruf dan angka.</div>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required" for="password_confirmation">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control @error('password') is-invalid @enderror"
                       required minlength="8" autocomplete="new-password">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required" for="status">Status</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
                    <option value="diblokir" @selected(old('status') === 'diblokir')>Diblokir</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-footer mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-check me-1"></i> Simpan Pengguna
            </button>
            <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
        </div>
    </form>
</x-tabler.card>
@endsection
