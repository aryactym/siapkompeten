@extends('layouts.admin')

@section('title', $event->exists ? 'Edit Event Uji' : 'Buat Event Uji')
@section('pretitle', 'Penjadwalan')
@section('page-title', $event->exists ? 'Edit Event Uji Kompetensi' : 'Buat Event Uji Kompetensi')

@section('content')
<form method="POST"
      action="{{ $event->exists ? route('admin.event.update', $event) : route('admin.event.store') }}">
    @csrf
    @if ($event->exists)
        @method('PUT')
    @endif

    <x-tabler.card title="Informasi Event">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label required">Nama Event</label>
                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                       value="{{ old('nama', $event->nama) }}" required maxlength="255"
                       placeholder="Contoh: Uji Kompetensi Gelombang I 2026">
                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label required">Tahun</label>
                <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror"
                       value="{{ old('tahun', $event->tahun) }}" min="2000" max="2100" required>
                @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Periode (Bulan)</label>
                <select name="periode" class="form-select @error('periode') is-invalid @enderror">
                    <option value="">—</option>
                    @foreach (range(1, 12) as $bulan)
                        <option value="{{ $bulan }}" @selected((int) old('periode', $event->periode) === $bulan)>
                            {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
                @error('periode')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Tempat Ringkas</label>
                <input type="text" name="tempat_ringkas" class="form-control @error('tempat_ringkas') is-invalid @enderror"
                       value="{{ old('tempat_ringkas', $event->tempat_ringkas) }}" maxlength="200"
                       placeholder="Contoh: TUK Mandiri Jakarta">
                @error('tempat_ringkas')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                       value="{{ old('tanggal_mulai', $event->tanggal_mulai?->toDateString()) }}">
                @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                       value="{{ old('tanggal_selesai', $event->tanggal_selesai?->toDateString()) }}">
                @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.event.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Simpan
            </button>
        </div>
    </x-tabler.card>
</form>
@endsection
