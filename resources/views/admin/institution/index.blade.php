@extends('layouts.admin')

@section('title', 'Profil Lembaga')
@section('pretitle', 'Pengaturan')
@section('page-title', 'Profil Lembaga (LSP)')

@section('content')
@php
    $tab = request()->query('tab', 'info');
@endphp

<section>
@if ($tab === 'logo')
    <x-tabler.card title="Logo dan Identitas Visual" subtitle="Upload logo BNSP dan LSP untuk ditampilkan di laporan PDF.">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Logo BNSP</label>
                <div class="mb-2">
                    @if ($institution->logo_bnsp_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $institution->logo_bnsp_path) }}" alt="Logo BNSP" style="max-height: 80px;">
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.institution.upload-logo', $institution) }}" enctype="multipart/form-data" class="d-inline">
                        @csrf
                        <input type="hidden" name="logo_type" value="logo_bnsp_path">
                        <input type="file" name="logo_file" class="form-control form-control-sm mt-2" accept="image/*">
                        <button type="submit" class="btn btn-outline-primary btn-sm mt-2">Simpan Logo BNSP</button>
                    </form>
                </div>
                <small class="form-text text-muted">Format: JPG, PNG. Max 2MB.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Logo LSP</label>
                <div class="mb-2">
                    @if ($institution->logo_lsp_path)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $institution->logo_lsp_path) }}" alt="Logo LSP" style="max-height: 80px;">
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.institution.upload-logo', $institution) }}" enctype="multipart/form-data" class="d-inline">
                        @csrf
                        <input type="hidden" name="logo_type" value="logo_lsp_path">
                        <input type="file" name="logo_file" class="form-control form-control-sm mt-2" accept="image/*">
                        <button type="submit" class="btn btn-outline-primary btn-sm mt-2">Simpan Logo LSP</button>
                    </form>
                </div>
                <small class="form-text text-muted">Format: JPG, PNG. Max 2MB.</small>
            </div>
        </div>
    </x-tabler.card>
@else
    <form method="POST" action="{{ route('admin.institution.update', $institution) }}">
        @csrf
        @method('PUT')

        <x-tabler.card title="Identitas Lembaga" subtitle="Data resmi LSP sesuai lisensi BNSP.">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Nama Lembaga</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $institution->name) }}" required maxlength="255">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nomor Lisensi BNSP</label>
                    <input type="text" name="nomor_lisensi" class="form-control @error('nomor_lisensi') is-invalid @enderror"
                           value="{{ old('nomor_lisensi', $institution->nomor_lisensi) }}" maxlength="100">
                    @error('nomor_lisensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jenis LSP</label>
                    <select name="jenis_lsp" class="form-select @error('jenis_lsp') is-invalid @enderror">
                        @foreach (['p1' => 'P1', 'p2' => 'P2', 'p3' => 'P3'] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('jenis_lsp', $institution->jenis_lsp) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('jenis_lsp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Masa Berlaku Lisensi</label>
                    <input type="date" name="masa_berlaku_lisensi" class="form-control @error('masa_berlaku_lisensi') is-invalid @enderror"
                           value="{{ old('masa_berlaku_lisensi', optional($institution->masa_berlaku_lisensi)->format('Y-m-d')) }}">
                    @error('masa_berlaku_lisensi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama Ketua</label>
                    <input type="text" name="nama_ketua" class="form-control @error('nama_ketua') is-invalid @enderror"
                           value="{{ old('nama_ketua', $institution->nama_ketua) }}" maxlength="150">
                    @error('nama_ketua') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $institution->email) }}" maxlength="150">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror"
                           value="{{ old('telepon', $institution->telepon) }}" maxlength="50">
                    @error('telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                           value="{{ old('website', $institution->website) }}" maxlength="200">
                    @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2">{{ old('alamat', $institution->alamat) }}</textarea>
                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input"
                               @checked(old('is_active', $institution->is_active))>
                        <span class="form-check-label">Lembaga aktif</span>
                    </label>
                </div>
            </div>
        </x-tabler.card>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Simpan Profil
            </button>
        </div>
    </form>
@endif
</section>
@endsection
