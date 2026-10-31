<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Sertifikat · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">{{ config('app.name') }}</h2>
                    <p class="text-secondary">Verifikasi Keaslian Sertifikat Kompetensi</p>
                </div>

                @if ($sertifikat && $sertifikat->isActive())
                    <div class="card border-success">
                        <div class="card-body text-center py-4">
                            <span class="avatar avatar-xl bg-green-lt text-success mb-3">
                                <i class="ti ti-shield-check" style="font-size:2rem;"></i>
                            </span>
                            <h3 class="text-success fw-bold">Sertifikat Valid</h3>
                            <p class="text-secondary">Sertifikat ini asli dan terdaftar di sistem kami.</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-light text-secondary small" width="40%">Pemegang</th>
                                        <td class="fw-semibold">{{ $sertifikat->asesi?->user?->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary small">Skema</th>
                                        <td>{{ $sertifikat->skema?->nama_skema ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary small">No. Registrasi</th>
                                        <td class="font-monospace text-primary fw-semibold">{{ $sertifikat->no_registrasi }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary small">Diterbitkan</th>
                                        <td>{{ $sertifikat->tanggal_terbit?->translatedFormat('d F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary small">Berlaku Hingga</th>
                                        <td>{{ $sertifikat->berlaku_hingga?->translatedFormat('d F Y') ?? 'Seumur Hidup' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light text-secondary small">Lembaga</th>
                                        <td>{{ $sertifikat->institution?->name ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif ($sertifikat)
                    <div class="card border-warning">
                        <div class="card-body text-center py-4">
                            <span class="avatar avatar-xl bg-yellow-lt text-yellow mb-3">
                                <i class="ti ti-alert-triangle" style="font-size:2rem;"></i>
                            </span>
                            <h3 class="text-warning fw-bold">Sertifikat Tidak Aktif</h3>
                            <p class="text-secondary">
                                Sertifikat ditemukan namun berstatus
                                <strong>{{ $sertifikat->status }}</strong>.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="card border-danger">
                        <div class="card-body text-center py-4">
                            <span class="avatar avatar-xl bg-red-lt text-danger mb-3">
                                <i class="ti ti-shield-x" style="font-size:2rem;"></i>
                            </span>
                            <h3 class="text-danger fw-bold">Sertifikat Tidak Ditemukan</h3>
                            <p class="text-secondary">
                                Kode <code>{{ $token }}</code> tidak cocok dengan sertifikat mana pun.
                                Pastikan QR code atau tautan yang digunakan benar.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="text-center mt-4">
                    <a href="{{ route('beranda') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-home me-1"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
