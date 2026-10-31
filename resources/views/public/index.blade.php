@extends('layouts.public')

@section('title', 'Beranda')
@section('meta_description', 'Sistem Informasi Lembaga Sertifikasi Profesi - Uji Kompetensi Berstandar BNSP')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h1 class="h2 mb-1">{{ $institution->name ?? config('app.name') }}</h1>
                        <p class="text-secondary mb-0">{{ $institution->alamat ?? '' }}</p>
                        @if ($institution?->website)
                            <a href="{{ $institution->website }}" class="btn btn-sm btn-outline-primary mt-2" target="_blank">Website</a>
                        @endif
                        <div class="mt-3">
                            <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary">Daftar</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Skema Sertifikasi Aktif</h3></div>
                    <div class="card-body">
                        @if ($skemas->isEmpty())
                            <div class="text-secondary">Belum ada skema aktif.</div>
                        @else
                            <div class="row g-3">
                                @foreach ($skemas as $skema)
                                    <div class="col-md-6">
                                        <div class="card card-sm">
                                            <div class="card-body">
                                                <div class="fw-semibold">{{ $skema->nama_skema }}</div>
                                                <div class="text-secondary small">{{ $skema->kode_skema }} · {{ strtoupper($skema->jenis) }}</div>
                                                @if ($skema->deskripsi)
                                                    <div class="small mt-1">{{ $skema->deskripsi }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Asesor Kompetensi</h3></div>
                    <div class="card-body">
                        @if ($asesors->isEmpty())
                            <div class="text-secondary">Belum ada asesor.</div>
                        @else
                            <div class="row g-3">
                                @foreach ($asesors as $asesor)
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="avatar avatar-sm bg-primary-lt text-primary fw-bold">{{ strtoupper(substr($asesor->user->name ?? 'A', 0, 2)) }}</span>
                                            <div>
                                                <div class="fw-semibold">{{ $asesor->user->name ?? '—' }}</div>
                                                <div class="text-secondary small">{{ $asesor->bidang_keahlian ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($beritas->isNotEmpty())
                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Berita</h3></div>
                        <div class="card-body">
                            @foreach ($beritas as $berita)
                                <div class="mb-3">
                                    <div class="fw-semibold">{{ $berita->judul }}</div>
                                    <div class="text-secondary small">{{ $berita->tanggal_terbit?->translatedFormat('d M Y') }}</div>
                                    <div class="small mt-1">{!! $berita->isi !!}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Kontak</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div><strong>Alamat:</strong> {{ $institution->alamat ?? '—' }}</div>
                                <div><strong>Email:</strong> {{ $institution->email ?? '—' }}</div>
                                <div><strong>Telepon:</strong> {{ $institution->telepon ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div><strong>Website:</strong> {{ $institution->website ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
