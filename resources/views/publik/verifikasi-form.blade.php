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

                <div class="card">
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('sertifikat.lookup') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="nomor">Nomor Registrasi / Nomor Seri Blangko</label>
                                <input type="text" name="nomor" id="nomor" class="form-control" maxlength="100"
                                       value="{{ old('nomor') }}" placeholder="Contoh: LSP-2026-000123" required autofocus>
                                <div class="form-text">Nomor tercetak pada sertifikat. Anda juga dapat memindai QR pada sertifikat.</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i> Cari Sertifikat
                                </button>
                                <a href="{{ route('beranda') }}" class="btn btn-outline-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
