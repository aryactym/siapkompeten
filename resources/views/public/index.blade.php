@extends('layouts.public')

@section('title', 'Beranda')
@section('meta_description', 'Sistem Informasi Lembaga Sertifikasi Profesi - Uji Kompetensi Berstandar BNSP')

@section('content')
@php
    /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\CmsContent>> $cms */
    $section = fn (string $kategori) => $cms->get($kategori, collect());
    $first = fn (string $kategori) => $section($kategori)->first();
    $banner = $first('slide_banner');
    $welcome = $first('welcome');
    $maps = $first('google_maps');
    $mapsUrl = $maps ? trim(strip_tags((string) ($maps->isi ?: $maps->sub_judul))) : '';
    $isMapsEmbed = $mapsUrl !== '' && (
        preg_match('#^https://(www\.)?google\.com/maps#i', $mapsUrl) === 1
        || preg_match('#^https://maps\.google\.com#i', $mapsUrl) === 1
    );
@endphp

<div class="page-body">
    <div class="container-xl">

        @if ($banner)
            <div class="card card-lg mb-4 overflow-hidden">
                @if ($banner->gambar_path)
                    <img src="{{ asset('storage/'.$banner->gambar_path) }}" class="card-img-top" alt="{{ $banner->judul }}" style="max-height: 320px; object-fit: cover;">
                @endif
                <div class="card-body">
                    <h1 class="h1 mb-1">{{ $banner->judul }}</h1>
                    @if ($banner->sub_judul)
                        <p class="text-secondary fs-3 mb-2">{{ $banner->sub_judul }}</p>
                    @endif
                    @if ($banner->isi)
                        <div class="text-secondary">{!! $banner->isi !!}</div>
                    @endif
                </div>
            </div>
        @else
            <div class="card card-lg mb-4">
                <div class="card-body">
                    <h1 class="h1 mb-1">{{ $institution->name ?? config('app.name') }}</h1>
                    <p class="text-secondary mb-0">{{ $institution->alamat ?? '' }}</p>
                </div>
            </div>
        @endif

        @if ($welcome)
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h2 mb-2">{{ $welcome->judul }}</h2>
                    @if ($welcome->sub_judul)
                        <div class="text-secondary mb-2">{{ $welcome->sub_judul }}</div>
                    @endif
                    <div>{!! $welcome->isi !!}</div>
                </div>
            </div>
        @endif

        <section id="skema" class="mb-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Skema Sertifikasi Aktif</h3></div>
                <div class="card-body">
                    @if ($skemas->isEmpty())
                        <div class="text-secondary">Belum ada skema aktif.</div>
                    @else
                        <div class="row g-3">
                            @foreach ($skemas as $skema)
                                <div class="col-md-6">
                                    <div class="card card-sm h-100">
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
        </section>

        @if ($section('layanan')->isNotEmpty())
            <section id="layanan" class="mb-4">
                <div class="row g-3">
                    @foreach ($section('layanan') as $layanan)
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h3 class="h3 mb-1">{{ $layanan->judul }}</h3>
                                    @if ($layanan->sub_judul)
                                        <div class="text-secondary small mb-2">{{ $layanan->sub_judul }}</div>
                                    @endif
                                    <div>{!! $layanan->isi !!}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section id="jadwal" class="mb-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Jadwal Asesmen</h3></div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr><th>Tanggal</th><th>Skema</th><th>TUK</th><th>Waktu</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($jadwals as $jadwal)
                                <tr>
                                    <td>{{ $jadwal->tanggal?->translatedFormat('d M Y') ?? '—' }}</td>
                                    <td>{{ $jadwal->skema?->nama_skema ?? '—' }}</td>
                                    <td>{{ $jadwal->tuk?->nama_tuk ?? '—' }}</td>
                                    <td>{{ $jadwal->jam_mulai }}–{{ $jadwal->jam_selesai }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-secondary text-center">Belum ada jadwal terbuka.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="asesor" class="mb-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Asesor Kompetensi</h3></div>
                <div class="card-body">
                    @foreach ($section('asesor') as $asesorInfo)
                        <div class="mb-3">
                            <div class="fw-semibold">{{ $asesorInfo->judul }}</div>
                            @if ($asesorInfo->sub_judul)
                                <div class="text-secondary small">{{ $asesorInfo->sub_judul }}</div>
                            @endif
                            @if ($asesorInfo->isi)
                                <div class="small mt-1">{!! $asesorInfo->isi !!}</div>
                            @endif
                        </div>
                    @endforeach

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
        </section>

        <section id="tuk" class="mb-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Tempat Uji Kompetensi</h3></div>
                <div class="card-body">
                    @if ($tuks->isEmpty())
                        <div class="text-secondary">Belum ada TUK terdaftar.</div>
                    @else
                        <div class="row g-3">
                            @foreach ($tuks as $tuk)
                                <div class="col-md-4">
                                    <div class="fw-semibold">{{ $tuk->nama_tuk }}</div>
                                    <div class="text-secondary small">{{ $tuk->kode_tuk }} · {{ ucfirst($tuk->jenis) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @if ($section('portfolio')->isNotEmpty())
            <section id="portfolio" class="mb-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Portfolio</h3></div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($section('portfolio') as $portfolio)
                                <div class="col-md-4">
                                    <div class="card card-sm h-100">
                                        @if ($portfolio->gambar_path)
                                            <img src="{{ asset('storage/'.$portfolio->gambar_path) }}" class="card-img-top" alt="{{ $portfolio->judul }}" style="height: 160px; object-fit: cover;">
                                        @endif
                                        <div class="card-body">
                                            <div class="fw-semibold">{{ $portfolio->judul }}</div>
                                            @if ($portfolio->sub_judul)
                                                <div class="text-secondary small">{{ $portfolio->sub_judul }}</div>
                                            @endif
                                            @if ($portfolio->isi)
                                                <div class="small mt-1">{!! $portfolio->isi !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <section id="berita" class="mb-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Berita & Agenda</h3></div>
                <div class="card-body">
                    @forelse ($section('berita') as $berita)
                        <article class="mb-3 pb-3 {{ $loop->last ? '' : 'border-bottom' }}">
                            <div class="fw-semibold fs-3">{{ $berita->judul }}</div>
                            <div class="text-secondary small">
                                {{ $berita->tanggal_terbit?->translatedFormat('d M Y') }}
                                @if ($berita->jam_terbit) · {{ $berita->jam_terbit }} @endif
                            </div>
                            @if ($berita->sub_judul)
                                <div class="mt-1">{{ $berita->sub_judul }}</div>
                            @endif
                            @if ($berita->isi)
                                <div class="small mt-1">{!! $berita->isi !!}</div>
                            @endif
                        </article>
                    @empty
                        <div class="text-secondary">Belum ada berita.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="verifikasi-sertifikat" class="mb-4">
            <div class="card">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h3 class="h3 mb-1">Verifikasi Keaslian Sertifikat</h3>
                        <p class="text-secondary mb-0">Cek keaslian sertifikat dengan nomor registrasi, nomor seri blangko, atau pindai QR pada sertifikat.</p>
                    </div>
                    <a href="{{ route('sertifikat.form') }}" class="btn btn-primary">
                        <i class="ti ti-shield-check me-1"></i> Verifikasi Sertifikat
                    </a>
                </div>
            </div>
        </section>

        <section id="daftar" class="mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h3 class="h3 mb-1 text-white">Siap mengikuti uji kompetensi?</h3>
                        <p class="mb-0 text-white-50">Daftar sebagai asesi, pilih skema, lalu ikuti proses asesmen sesuai jadwal.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('register') }}" class="btn btn-white">Daftar Asesi</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-white">Masuk</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="kontak" class="mb-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Hubungi Kami</h3></div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-2"><strong>{{ $institution->name ?? config('app.name') }}</strong></div>
                            <div><strong>Alamat:</strong> {{ $institution->alamat ?? '—' }}</div>
                            <div><strong>Email:</strong> {{ $institution->email ?? '—' }}</div>
                            <div><strong>Telepon:</strong> {{ $institution->telepon ?? '—' }}</div>
                            @if ($institution?->website)
                                <div><strong>Website:</strong> <a href="{{ $institution->website }}" target="_blank" rel="noopener noreferrer">{{ $institution->website }}</a></div>
                            @endif

                            @foreach ($section('hubungi_kami') as $kontak)
                                <div class="mt-3">
                                    <div class="fw-semibold">{{ $kontak->judul }}</div>
                                    @if ($kontak->sub_judul)
                                        <div class="text-secondary small">{{ $kontak->sub_judul }}</div>
                                    @endif
                                    @if ($kontak->isi)
                                        <div class="small">{!! $kontak->isi !!}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-6">
                            @if ($isMapsEmbed)
                                <div class="ratio ratio-4x3 rounded overflow-hidden">
                                    <iframe src="{{ $mapsUrl }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi {{ $institution->name ?? config('app.name') }}"></iframe>
                                </div>
                            @elseif ($mapsUrl !== '')
                                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary">
                                    <i class="ti ti-map-pin me-1"></i> Lihat Lokasi
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
