@extends('layouts.admin')

@section('title', 'Persyaratan Skema')
@section('pretitle', 'Master Standar')
@section('page-title', 'Persyaratan: ' . $skema->nama_skema)

@section('actions')
    <a href="{{ route('admin.skema.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<x-tabler.card title="Dokumen Persyaratan Skema">
    <div class="mb-3 p-3 bg-light rounded">
        <strong>Skema:</strong> {{ $skema->kode_skema }} - {{ $skema->nama_skema }}
    </div>

    @if ($skema->persyaratans->isEmpty())
        <x-tabler.empty title="Belum ada persyaratan" description="Tambahkan dokumen persyaratan yang diperlukan untuk skema ini." icon="ti ti-file-text" />
    @else
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Dokumen</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Wajib</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($skema->persyaratans as $idx => $persyaratan)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td class="fw-semibold">{{ $persyaratan->nama_dokumen }}</td>
                            <td>{{ $persyaratan->keterangan ?? '-' }}</td>
                            <td class="text-center">
                                @if ($persyaratan->is_required)
                                    <span class="badge bg-red-lt text-red">Ya</span>
                                @else
                                    <span class="badge bg-secondary-lt">Tidak</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('admin.skema.persyaratan.edit', [$skema, $persyaratan]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.skema.persyaratan.destroy', [$skema, $persyaratan]) }}"
                                          onsubmit="return confirm('Hapus persyaratan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-tambah-persyaratan">
            <i class="ti ti-plus me-1"></i> Tambah Persyaratan
        </button>
    </div>
</x-tabler.card>

{{-- Modal Tambah Persyaratan --}}
<div class="modal modal-blur fade" id="modal-tambah-persyaratan" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form method="POST" action="{{ route('admin.skema.persyaratan.store', $skema) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Persyaratan Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Nama Dokumen</label>
                    <input type="text" name="nama_dokumen" class="form-control @error('nama_dokumen') is-invalid @enderror"
                           value="{{ old('nama_dokumen') }}" required maxlength="200"
                           placeholder="Contoh: KTP, Ijazah, Sertifikat">
                    @error('nama_dokumen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
                              rows="3" maxlength="500">{{ old('keterangan') }}</textarea>
                    @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-check">
                        <input type="checkbox" name="is_required" value="1" class="form-check-input"
                               @checked(old('is_required'))>
                        <span class="form-check-label">Dokumen wajib diunggah</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary ms-auto">
                    <i class="ti ti-device-floppy me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
