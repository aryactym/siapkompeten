@extends('layouts.admin')

@section('title', 'Kirim Undangan Asesor')
@section('pretitle', 'Asesor / Undang Keanggotaan')
@section('page-title', 'Kirim Undangan Asesor')

@section('actions')
    <a href="{{ route('admin.asesor.invite.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<div class="row row-cards justify-content-center">
    <div class="col-lg-7">
        <x-tabler.card title="Data Calon Asesor">
            <p class="text-secondary small">
                Sistem akan membuat akun pengguna berperan <strong>asesor</strong> dan mengirim
                tautan aktivasi (penetapan kata sandi) ke email yang didaftarkan. Tautan berlaku 7 hari.
            </p>

            <form method="POST" action="{{ route('admin.asesor.invite.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label required">Nama Lengkap</label>
                        <input type="text" name="nama"
                               class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama') }}" required maxlength="150">
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">NIK (opsional)</label>
                        <input type="text" name="nik"
                               class="form-control @error('nik') is-invalid @enderror"
                               value="{{ old('nik') }}" maxlength="16" inputmode="numeric"
                               placeholder="16 digit">
                        @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required maxlength="150">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Nomor Register Asesor</label>
                        <input type="text" name="no_register"
                               class="form-control @error('no_register') is-invalid @enderror"
                               value="{{ old('no_register') }}" required maxlength="100"
                               placeholder="Contoh: MET.000.001234 2026">
                        @error('no_register')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Bidang Keahlian</label>
                        <input type="text" name="bidang_keahlian"
                               class="form-control"
                               value="{{ old('bidang_keahlian') }}" maxlength="150"
                               placeholder="Contoh: Teknologi Informasi">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-send me-1"></i> Kirim Undangan
                    </button>
                    <a href="{{ route('admin.asesor.invite.index') }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </x-tabler.card>
    </div>
</div>
@endsection
