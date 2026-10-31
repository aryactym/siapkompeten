@extends('layouts.admin')

@section('title', 'Detail Pembayaran')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', 'Detail Pembayaran')

@section('actions')
    <a href="{{ route('admin.pembayaran.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Daftar Pembayaran
    </a>
@endsection

@section('content')
@php
    $pendaftaran = $pembayaran->pendaftaran;
    $rincianBiaya = $pendaftaran->skema->biayaTagihan();
    $sudahDiputuskan = $pembayaran->status !== \App\Models\Pembayaran::STATUS_MENUNGGU;
@endphp

<div class="row row-cards">
    <div class="col-lg-7">
        <x-tabler.card title="Rincian Pembayaran">
            <div class="mb-3">
                <div class="text-secondary small">Asesi</div>
                <div class="fw-semibold">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
            </div>
            <div class="mb-3">
                <div class="text-secondary small">Nomor Pendaftaran</div>
                <div class="fw-semibold">{{ $pendaftaran->no_pendaftaran }}</div>
            </div>
            <div class="mb-3">
                <div class="text-secondary small">Skema</div>
                <div class="fw-semibold">{{ $pendaftaran->skema->nama_skema ?? '—' }}</div>
            </div>
            <div class="mb-3">
                <div class="text-secondary small">Rekening Tujuan</div>
                <div class="fw-semibold">
                    {{ $pembayaran->rekeningBank->nama_bank ?? '—' }}
                    · {{ $pembayaran->rekeningBank->no_rekening ?? '—' }}
                    · a.n. {{ $pembayaran->rekeningBank->atas_nama ?? '—' }}
                </div>
            </div>
            <div class="mb-3">
                <div class="text-secondary small">Tanggal Bayar</div>
                <div class="fw-semibold">{{ $pembayaran->tanggal_bayar?->translatedFormat('d F Y') ?? '—' }}</div>
            </div>

            <h3 class="h4 mt-4">Rincian Tagihan</h3>
            <div class="table-responsive">
                <table class="table table-sm">
                    <tbody>
                        @forelse ($rincianBiaya as $item)
                            <tr>
                                <td>{{ $item['jenis_biaya'] }}</td>
                                <td class="text-end">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="text-secondary">Belum ada biaya skema.</td>
                                <td></td>
                            </tr>
                        @endforelse
                        <tr>
                            <th class="fw-semibold">Total Tagihan</th>
                            <th class="text-end">Rp {{ number_format($pendaftaran->totalTagihan(), 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <td class="text-secondary">Total Dibayarkan</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($pembayaran->total, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-tabler.card>
    </div>

    <div class="col-lg-5">
        <x-tabler.card title="Bukti Pembayaran">
            @if ($pembayaran->hasBukti())
                <div class="mb-3">
                    <div class="text-secondary small">Berkas</div>
                    <div class="fw-semibold">{{ $pembayaran->bukti_original_name ?? 'bukti-pembayaran' }}</div>
                    <div class="text-secondary small">
                        {{ $pembayaran->bukti_mime ?? '—' }}
                        @if ($pembayaran->bukti_size)
                            · {{ number_format($pembayaran->bukti_size / 1024, 1, ',', '.') }} KB
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.pembayaran.bukti', $pembayaran) }}" class="btn btn-outline-primary w-100">
                    <i class="ti ti-download me-1"></i> Unduh & Periksa Bukti
                </a>
            @else
                <p class="text-secondary mb-0">Asesi belum mengunggah bukti pembayaran.</p>
            @endif
        </x-tabler.card>

        <x-tabler.card title="Keputusan Validasi" class="mt-3">
            @if ($sudahDiputuskan)
                <div class="mb-3">
                    Status:
                    @if ($pembayaran->status === \App\Models\Pembayaran::STATUS_VALID)
                        <span class="badge bg-success-lt text-success">Valid</span>
                    @else
                        <span class="badge bg-danger-lt text-danger">Tidak Valid</span>
                    @endif
                </div>
                @if ($pembayaran->catatan)
                    <div class="mb-3">
                        <div class="text-secondary small">Catatan</div>
                        <div>{{ $pembayaran->catatan }}</div>
                    </div>
                @endif
                <div class="text-secondary small">
                    Divalidasi oleh {{ $pembayaran->divalidasiOleh->name ?? '—' }}
                    {{ $pembayaran->divalidasi_pada?->translatedFormat('d F Y H:i') ?? '' }}
                </div>
            @else
                <form method="POST" action="{{ route('admin.pembayaran.putuskan', $pembayaran) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">Status Validasi</label>
                        <div class="form-selectgroup form-selectgroup-pills">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="status" value="valid" class="form-selectgroup-input" required>
                                <span class="form-selectgroup-label">Valid</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="status" value="tidak_valid" class="form-selectgroup-input">
                                <span class="form-selectgroup-label">Tidak Valid</span>
                            </label>
                        </div>
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="catatan">Catatan <span class="text-danger">*</span></label>
                        <textarea name="catatan" id="catatan" rows="3" class="form-control"
                                  maxlength="1000" placeholder="Wajib diisi jika pembayaran tidak valid.">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Simpan keputusan validasi pembayaran?')">
                        <i class="ti ti-check me-1"></i> Simpan Keputusan
                    </button>
                </form>
            @endif
        </x-tabler.card>
    </div>
</div>
@endsection
