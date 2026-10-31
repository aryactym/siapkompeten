@extends('layouts.admin')

@section('title', 'Impor Data Asesi')
@section('pretitle', 'Asesi & Pendaftaran')
@section('page-title', 'Impor Data Asesi')

@section('actions')
    <a href="{{ route('admin.asesi.import.template') }}" class="btn btn-outline-primary">
        <i class="ti ti-download me-1"></i> Unduh Template
    </a>
    <a href="{{ route('admin.asesi.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Daftar Asesi
    </a>
@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-7">
        <x-tabler.card title="Unggah Berkas Excel">
            <form method="POST" action="{{ route('admin.asesi.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label required" for="file">Berkas (.xlsx / .csv, maks. 5 MB)</label>
                    <input type="file" name="file" id="file"
                           class="form-control @error('file') is-invalid @enderror"
                           accept=".xlsx,.csv" required>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="form-hint">
                        Gunakan template resmi. Berkas diproses sebagai data, maksimal 2.000 baris per unggahan.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-upload me-1"></i> Unggah &amp; Pratinjau
                </button>
            </form>
        </x-tabler.card>

        <x-tabler.card title="Kolom Template" class="mt-3">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Kolom</th><th>Wajib</th><th>Keterangan</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>nama</code></td><td>Ya</td><td>Nama lengkap asesi</td></tr>
                        <tr><td><code>nik</code></td><td>Ya</td><td>Tepat 16 digit, unik per lembaga</td></tr>
                        <tr><td><code>email</code></td><td>Ya</td><td>Unik per lembaga, dipakai untuk aktivasi akun</td></tr>
                        <tr><td><code>telepon</code></td><td>Ya</td><td>Nomor telepon aktif</td></tr>
                        <tr><td><code>jenis_kelamin</code></td><td>Ya</td><td>L atau P</td></tr>
                        <tr><td><code>alamat</code></td><td>Ya</td><td>Alamat rumah</td></tr>
                        <tr><td><code>pendidikan_terakhir</code></td><td>Ya</td><td>Contoh: D3 Teknik Informatika</td></tr>
                        <tr><td><code>tanggal_lahir</code></td><td>Tidak</td><td>Format YYYY-MM-DD</td></tr>
                        <tr><td><code>kode_provinsi</code></td><td>Tidak</td><td>Lihat sheet <em>Kode Wilayah</em></td></tr>
                        <tr><td><code>kode_kota</code></td><td>Tidak</td><td>Harus berada di provinsi yang dipilih</td></tr>
                        <tr><td><code>kode_kecamatan</code></td><td>Tidak</td><td>6 digit, lihat referensi wilayah</td></tr>
                        <tr><td><code>pekerjaan</code>, <code>jabatan</code></td><td>Tidak</td><td>Data pekerjaan saat ini</td></tr>
                    </tbody>
                </table>
            </div>
        </x-tabler.card>
    </div>

    <div class="col-lg-5">
        <x-tabler.card title="Riwayat Impor">
            @if ($batches->isEmpty())
                <p class="text-secondary mb-0">Belum ada berkas impor pada lembaga ini.</p>
            @else
                <div class="list-group list-group-flush">
                    @foreach ($batches as $batch)
                        <a href="{{ route('admin.asesi.import.show', $batch) }}"
                           class="list-group-item list-group-item-action px-0">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold text-truncate" style="max-width: 260px;">
                                        {{ $batch->original_name }}
                                    </div>
                                    <div class="text-secondary small">
                                        {{ $batch->created_at->translatedFormat('d M Y H:i') }}
                                        · {{ $batch->total_rows }} baris
                                    </div>
                                </div>
                                <span class="badge {{ $batch->status === 'selesai' ? 'bg-green-lt' : ($batch->status === 'preview' ? 'bg-blue-lt' : 'bg-secondary-lt') }}">
                                    {{ ucfirst($batch->status) }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-3">{{ $batches->links() }}</div>
            @endif
        </x-tabler.card>
    </div>
</div>
@endsection
