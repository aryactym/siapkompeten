@extends('layouts.admin')

@section('title', 'Edit Persyaratan Skema')
@section('pretitle', 'Master Standar')
@section('page-title', 'Edit Persyaratan: ' . $persyaratan->nama_dokumen)

@section('actions')
    <a href="{{ route('admin.skema.persyaratan', $skema) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-6">
                <x-tabler.card title="Edit Persyaratan Dokumen">
                    <form method="POST" action="{{ route('admin.skema.persyaratan.update', [$skema, $persyaratan]) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label required">Nama Dokumen</label>
                            <input type="text" name="nama_dokumen" class="form-control @error('nama_dokumen') is-invalid @enderror"
                                   value="{{ old('nama_dokumen', $persyaratan->nama_dokumen) }}" required maxlength="200"
                                   placeholder="Contoh: KTP, Ijazah, Sertifikat">
                            @error('nama_dokumen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                                      rows="3" maxlength="500" placeholder="Masukkan keterangan tambahan (opsional)">{{ old('keterangan', $persyaratan->keterangan) }}</textarea>
                            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-check">
                                <input type="checkbox" name="is_required" value="1" class="form-check-input"
                                       @checked(old('is_required', $persyaratan->is_required))>
                                <span class="form-check-label">Dokumen wajib diunggah</span>
                            </label>
                        </div>
                        
                        <div class="form-footer">
                            <a href="{{ route('admin.skema.persyaratan', $skema) }}" class="btn btn-link link-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary ms-auto">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </x-tabler.card>
            </div>
        </div>
    </div>
</div>
@endsection
