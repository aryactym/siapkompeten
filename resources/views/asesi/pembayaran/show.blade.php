@extends('layouts.asesi')

@section('title', 'Biaya & Pembayaran')
@section('pretitle', 'Pembayaran')
@section('page-title', 'Tagihan & Bukti Pembayaran')

@section('actions')
    <a href="{{ route('asesi.pendaftaran.show', $pendaftaran) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali ke Pendaftaran
    </a>
@endsection

@section('content')
<div class="row row-cards">
    <div class="col-lg-6">
        <x-tabler.card title="Rincian Tagihan" subtitle="{{ $pendaftaran->no_pendaftaran }}">
            @if ($rincianBiaya === [])
                <p class="text-secondary mb-0">Skema ini belum memiliki biaya. Silakan hubungi administrator lembaga.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            @foreach ($rincianBiaya as $item)
                                <tr>
                                    <td>{{ $item['jenis_biaya'] }}</td>
                                    <td class="text-end">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <th>Total Tagihan</th>
                                <th class="text-end">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            <h3 class="h4 mt-4">Rekening Tujuan</h3>
            @if ($rekeningBanks->isEmpty())
                <p class="text-secondary mb-0">Belum ada rekening tujuan. Hubungi administrator lembaga.</p>
            @else
                <div class="list-group list-group-flush">
                    @foreach ($rekeningBanks as $rekening)
                        <div class="list-group-item px-0">
                            <div class="fw-semibold">{{ $rekening->nama_bank }}</div>
                            <div>{{ $rekening->no_rekening }}</div>
                            <div class="text-secondary small">a.n. {{ $rekening->atas_nama }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-tabler.card>
    </div>

    <div class="col-lg-6">
        @php
            $statusPembayaran = $pembayaran?->status;
        @endphp

        @if ($statusPembayaran === \App\Models\Pembayaran::STATUS_VALID)
            <div class="alert alert-success">
                <div class="d-flex">
                    <div><i class="ti ti-circle-check alert-icon fs-2 me-2"></i></div>
                    <div>
                        <div class="alert-title fw-semibold">Pembayaran Terverifikasi</div>
                        <div class="text-secondary">Pembayaran Anda sudah divalidasi. Pendaftaran masuk ke tahap penjadwalan.</div>
                    </div>
                </div>
            </div>
        @elseif ($statusPembayaran === \App\Models\Pembayaran::STATUS_TIDAK_VALID)
            <div class="alert alert-danger">
                <div class="d-flex">
                    <div><i class="ti ti-alert-circle alert-icon fs-2 me-2"></i></div>
                    <div>
                        <div class="alert-title fw-semibold">Pembayaran Belum Valid</div>
                        <div class="text-secondary">Catatan admin: {{ $pembayaran->catatan ?: '—' }}</div>
                        <div class="text-secondary">Silakan unggah ulang bukti pembayaran Anda.</div>
                    </div>
                </div>
            </div>
        @elseif ($statusPembayaran === \App\Models\Pembayaran::STATUS_MENUNGGU && $pembayaran->hasBukti())
            <div class="alert alert-warning">
                <div class="d-flex">
                    <div><i class="ti ti-clock alert-icon fs-2 me-2"></i></div>
                    <div>
                        <div class="alert-title fw-semibold">Menunggu Validasi Admin</div>
                        <div class="text-secondary">Bukti pembayaran sudah diunggah dan sedang diperiksa oleh administrator.</div>
                    </div>
                </div>
            </div>
        @endif

        <x-tabler.card title="Unggah Bukti Pembayaran">
            @if ($pembayaran && $pembayaran->hasBukti())
                <div class="mb-3">
                    <div class="text-secondary small">Berkas terakhir</div>
                    <div class="fw-semibold">{{ $pembayaran->bukti_original_name ?? 'bukti-pembayaran' }}</div>
                    <div class="text-secondary small">
                        Dibayar pada {{ $pembayaran->tanggal_bayar?->translatedFormat('d F Y') ?? '—' }}
                        · Rp {{ number_format($pembayaran->total, 0, ',', '.') }}
                    </div>
                    <a href="{{ route('asesi.pembayaran.bukti', $pendaftaran) }}" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="ti ti-download me-1"></i> Lihat Bukti
                    </a>
                </div>
                <hr>
            @endif

            @if ($dapatMengunggah)
                <form method="POST" action="{{ route('asesi.pembayaran.store', $pendaftaran) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required" for="rekening_bank_id">Rekening Tujuan</label>
                        <select name="rekening_bank_id" id="rekening_bank_id" class="form-select" required>
                            <option value="">— Pilih rekening —</option>
                            @foreach ($rekeningBanks as $rekening)
                                <option value="{{ $rekening->id }}"
                                        @selected((string) old('rekening_bank_id', $pembayaran?->rekening_bank_id) === (string) $rekening->id)>
                                    {{ $rekening->nama_bank }} · {{ $rekening->no_rekening }} · a.n. {{ $rekening->atas_nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('rekening_bank_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required" for="tanggal_bayar">Tanggal Bayar</label>
                        <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="form-control"
                               value="{{ old('tanggal_bayar', $pembayaran?->tanggal_bayar?->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}" required>
                        @error('tanggal_bayar')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="catatan">Catatan Transfer</label>
                        <textarea name="catatan" id="catatan" rows="2" class="form-control"
                                  maxlength="500" placeholder="Opsional, contoh: transfer via mobile banking">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label required" for="bukti">Bukti Pembayaran (PDF/JPG/PNG)</label>
                        <input type="file" name="bukti" id="bukti" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="form-hint">Maksimal 5 MB. Berkas disimpan pada penyimpanan privat lembaga.</small>
                        @error('bukti')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-upload me-1"></i> Unggah Bukti Pembayaran
                    </button>
                </form>
            @else
                <p class="text-secondary mb-0">
                    Tahap pembayaran untuk pendaftaran ini sudah ditutup.
                </p>
            @endif
        </x-tabler.card>
    </div>
</div>
@endsection
