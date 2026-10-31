@extends('layouts.admin')

@section('title', 'Form Konten CMS')
@section('pretitle', 'CMS & Publik')
@section('page-title', $content->exists ? 'Ubah Konten' : 'Tambah Konten')

@section('content')
<form method="POST" action="{{ $content->exists ? route('admin.cms.update', $content) : route('admin.cms.store') }}">
    @csrf
    @if ($content->exists) @method('PUT') @endif

    <x-tabler.card title="Detail Konten">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label required">Kategori</label>
                <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                    @foreach ($kategoris as $key => $label)
                        <option value="{{ $key }}" @selected(old('kategori', $content->kategori) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label required">Judul</label>
                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                       value="{{ old('judul', $content->judul) }}" required maxlength="255">
                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Sub Judul</label>
                <input type="text" name="sub_judul" class="form-control @error('sub_judul') is-invalid @enderror"
                       value="{{ old('sub_judul', $content->sub_judul) }}" maxlength="255">
                @error('sub_judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Terbit</label>
                <input type="date" name="tanggal_terbit" class="form-control @error('tanggal_terbit') is-invalid @enderror"
                       value="{{ old('tanggal_terbit', optional($content->tanggal_terbit)->format('Y-m-d')) }}">
                @error('tanggal_terbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Jam Terbit</label>
                <input type="time" name="jam_terbit" class="form-control @error('jam_terbit') is-invalid @enderror"
                       value="{{ old('jam_terbit', $content->jam_terbit) }}">
                @error('jam_terbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Path Gambar (opsional)</label>
                <input type="text" name="gambar_path" class="form-control @error('gambar_path') is-invalid @enderror"
                       value="{{ old('gambar_path', $content->gambar_path) }}" maxlength="500">
                @error('gambar_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Urutan</label>
                <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror"
                       value="{{ old('urutan', $content->urutan ?? 1) }}" min="1">
                @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-3">
                <label class="form-check mt-4">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                           @checked(old('is_active', $content->is_active ?? true))>
                    <span class="form-check-label">Aktif</span>
                </label>
            </div>
            <div class="col-12">
                <label class="form-label">Isi (HTML)</label>
                <textarea name="isi" class="form-control @error('isi') is-invalid @enderror" rows="12">{{ old('isi', $content->isi) }}</textarea>
                @error('isi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="form-hint">Tag berbahaya (<script>, <iframe>, atribut on*) akan dibersihkan otomatis.</small>
            </div>
        </div>
    </x-tabler.card>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-device-floppy me-1"></i> Simpan
        </button>
        <a href="{{ route('admin.cms.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
@endsection
