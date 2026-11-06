@extends('layouts.admin')

@section('title', $jadwal->exists ? 'Edit Jadwal Uji' : 'Buat Jadwal Uji')
@section('pretitle', 'Penjadwalan')
@section('page-title', $jadwal->exists ? 'Edit Jadwal Uji Kompetensi' : 'Buat Jadwal Uji Kompetensi')

@section('content')
<form method="POST"
      action="{{ $jadwal->exists ? route('admin.jadwal.update', $jadwal) : route('admin.jadwal.store') }}">
    @csrf
    @if ($jadwal->exists)
        @method('PUT')
    @endif

    <x-tabler.card title="Informasi Jadwal">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label required">Event</label>
                <select name="event_id" class="form-select @error('event_id') is-invalid @enderror" required>
                    <option value="">Pilih event</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected(old('event_id', $jadwal->event_id) === $event->id)>
                            {{ $event->nama }} ({{ $event->tahun }})
                        </option>
                    @endforeach
                </select>
                @error('event_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label required">Skema Sertifikasi</label>
                <select name="skema_id" class="form-select @error('skema_id') is-invalid @enderror" required>
                    <option value="">Pilih skema</option>
                    @foreach ($skemas as $skema)
                        <option value="{{ $skema->id }}" @selected(old('skema_id', $jadwal->skema_id) === $skema->id)>
                            {{ $skema->kode_skema }} — {{ $skema->nama_skema }}
                        </option>
                    @endforeach
                </select>
                @error('skema_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label required">TUK</label>
                <select name="tuk_id" class="form-select @error('tuk_id') is-invalid @enderror" required>
                    <option value="">Pilih TUK</option>
                    @foreach ($tuks as $tuk)
                        <option value="{{ $tuk->id }}" @selected(old('tuk_id', $jadwal->tuk_id) === $tuk->id)>
                            {{ $tuk->nama_tuk }}
                        </option>
                    @endforeach
                </select>
                @error('tuk_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Gelombang / Rombel</label>
                <input type="text" name="gelombang" class="form-control @error('gelombang') is-invalid @enderror"
                       value="{{ old('gelombang', $jadwal->gelombang) }}" maxlength="50" placeholder="Contoh: Gelombang 1">
                @error('gelombang')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label required">Tanggal</label>
                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                       value="{{ old('tanggal', $jadwal->tanggal?->toDateString()) }}" required>
                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label required">Jam Mulai</label>
                <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror"
                       value="{{ old('jam_mulai', $jadwal->jam_mulai ? substr($jadwal->jam_mulai, 0, 5) : '') }}" required>
                @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label required">Jam Selesai</label>
                <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror"
                       value="{{ old('jam_selesai', $jadwal->jam_selesai ? substr($jadwal->jam_selesai, 0, 5) : '') }}" required>
                @error('jam_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label required">Kuota Peserta</label>
                <input type="number" name="kuota" class="form-control @error('kuota') is-invalid @enderror"
                       value="{{ old('kuota', $jadwal->kuota) }}" min="1" max="1000" required>
                @error('kuota')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label required">Lingkup Asesor</label>
                <select name="lingkup_asesor" class="form-select @error('lingkup_asesor') is-invalid @enderror" required>
                    <option value="wilayah" @selected(old('lingkup_asesor', $jadwal->lingkup_asesor) === 'wilayah')>Wilayah</option>
                    <option value="nonwilayah" @selected(old('lingkup_asesor', $jadwal->lingkup_asesor) === 'nonwilayah')>Non-Wilayah</option>
                </select>
                @error('lingkup_asesor')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Nomor Surat Tugas</label>
                <input type="text" name="no_surat_tugas" class="form-control @error('no_surat_tugas') is-invalid @enderror"
                       value="{{ old('no_surat_tugas', $jadwal->no_surat_tugas) }}" maxlength="100">
                @error('no_surat_tugas')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label required">Status</label>
                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                    @foreach (['draf', 'terbuka', 'berjalan', 'selesai', 'dibatalkan'] as $item)
                        <option value="{{ $item }}" @selected(old('status', $jadwal->status ?? 'draf') === $item)>{{ ucfirst($item) }}</option>
                    @endforeach
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        @php
            $allFrIa = [
                'ia02' => 'FR.IA.02 — Tugas Praktik Demonstrasi',
                'ia03' => 'FR.IA.03 — Pertanyaan Mendukung Observasi (PMO)',
                'ia04' => 'FR.IA.04 — Proyek Singkat',
                'ia05' => 'FR.IA.05 — Pilihan Ganda',
                'ia06' => 'FR.IA.06 — Esai',
                'ia07' => 'FR.IA.07 — Pertanyaan Lisan',
                'ia08' => 'FR.IA.08 — Verifikasi Portofolio',
                'ia09' => 'FR.IA.09 — Wawancara',
                'ia10' => 'FR.IA.10 — Verifikasi Pihak Ketiga',
                'ia11' => 'FR.IA.11 — Reviu Produk',
            ];
            $selectedJadwalIa = is_array($jadwal->instrumen_terpilih) && ! empty($jadwal->instrumen_terpilih)
                ? $jadwal->instrumen_terpilih
                : array_keys($allFrIa);
        @endphp

        <x-tabler.card title="Instrumen FR.IA Default (Paket)" class="mt-3">
            <p class="text-secondary small mb-2">
                Tentukan paket instrumen default untuk semua asesi pada jadwal ini. Dapat diubah per asesi di Rencana Asesmen (override).
            </p>
            <div class="row g-2">
                @foreach ($allFrIa as $key => $label)
                    <div class="col-md-6">
                        <label class="form-check">
                            <input type="checkbox" name="instrumen_terpilih[]" value="{{ $key }}"
                                   class="form-check-input"
                                   @checked(in_array($key, old('instrumen_terpilih', $selectedJadwalIa), true))>
                            <span class="form-check-label small">{{ $label }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </x-tabler.card>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ $jadwal->exists ? route('admin.jadwal.show', $jadwal) : route('admin.jadwal.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Simpan
            </button>
        </div>
    </x-tabler.card>
</form>
@endsection
