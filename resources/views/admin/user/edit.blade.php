@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('pretitle', 'Sistem')
@section('page-title', 'Edit Pengguna: ' . $user->name)

@section('actions')
    <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Daftar Pengguna
    </a>
@endsection

@section('content')
<x-tabler.card title="Edit Data Pengguna">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.user.update', $user) }}" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label required" for="name">Nama Lengkap</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required maxlength="150">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" required maxlength="150">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username', $user->username) }}" maxlength="50">
                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="phone">Nomor HP</label>
                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $user->phone) }}" maxlength="20">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="nik">NIK (16 digit)</label>
                <input type="text" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror"
                       value="{{ old('nik', $user->nik) }}" inputmode="numeric" pattern="\d{16}" maxlength="16"
                       placeholder="3201xxxxxxxxxxxx">
                <div class="form-hint">Disimpan terenkripsi. Kosongkan jika tidak ada.</div>
                @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required" for="role">Peran</label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', $currentRole) === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required" for="status">Status</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="aktif" @selected(old('status', $user->status) === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(old('status', $user->status) === 'nonaktif')>Nonaktif</option>
                    <option value="diblokir" @selected(old('status', $user->status) === 'diblokir')>Diblokir</option>
                </select>
                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <hr>
                <div class="text-secondary small mb-2">Ganti Kata Sandi (opsional)</div>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password">Kata Sandi Baru</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                       minlength="8" autocomplete="new-password" placeholder="Kosongkan jika tidak diganti">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control @error('password') is-invalid @enderror"
                       minlength="8" autocomplete="new-password">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-footer mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-check me-1"></i> Simpan Perubahan
            </button>
            <a href="{{ route('admin.user.index') }}" class="btn btn-outline-secondary ms-2">Batal</a>
        </div>
    </form>
</x-tabler.card>
@endsection
