@extends('layouts.asesor')

@section('title', 'Buat Rencana Asesmen')
@section('pretitle', 'Rencana & MAPA')
@section('page-title', 'Buat Rencana Asesmen')

@section('actions')
    <a href="{{ route('asesor.rencana-asesmen.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Kembali
    </a>
@endsection

@section('content')
<x-tabler.card title="Buat Rencana Asesmen untuk Pendaftaran {{ $pendaftaran->no_pendaftaran }}" class="mb-3">
    <div class="mb-3 text-secondary small">
        Pendaftaran: <strong>{{ $pendaftaran->no_pendaftaran }}</strong> ·
        Skema: <strong>{{ $skema->nama_skema }}</strong> ·
        Asesi: <strong>{{ $pendaftaran->asesi->user->name }}</strong>
    </div>

    <form method="POST" action="{{ route('asesor.rencana-asesmen.store', $pendaftaran) }}">
        @csrf

        <x-tabler.card title="Identitas Rencana" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Pendekatan Asesmen</label>
                    <select name="pendekatan" class="form-select form-select-sm" required>
                        <option value="observasi">Observasi</option>
                        <option value="demonstrasi_simulasi">Demonstrasi / Simulasi</option>
                        <option value="tes_lisan">Tes Lisan</option>
                        <option value="tes_tertulis">Tes Tertulis</option>
                        <option value="wawancara">Wawancara</option>
                        <option value="portofolio">Portofolio</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">Tanggal Asesmen</label>
                    <input type="date" name="tanggal_asesmen" class="form-control form-control-sm" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted">Tujuan Asesmen</label>
                <textarea name="tujuan" class="form-control form-control-sm" rows="3" required
                          placeholder="Contoh: Mengevaluasi keterampilan asesi dalam mengoperasikan mesin jahit sesuai standar SKKNI"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted">Ruang Lingkup</label>
                <textarea name="ruang_lingkup" class="form-control form-control-sm" rows="3" required
                          placeholder="Contoh: Semua unit kompetensi dalam skema Pemasaran Digital"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small text-muted">Catatan (opsional)</label>
                <textarea name="catatan" class="form-control form-control-sm" rows="2"></textarea>
            </div>
        </x-tabler.card>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('asesor.rencana-asesmen.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-save me-1"></i> Simpan Rencana
            </button>
        </div>
    </form>
</x-tabler.card>
</div>
@endsection