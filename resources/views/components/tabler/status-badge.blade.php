@props([
    'status' => 'draft',
    'label' => null,
    'size' => 'md', // sm, md, lg
])

@php
    $normalized = strtolower(trim((string) $status));

    $colorMap = [
        'aktif' => 'success',
        'kompeten' => 'success',
        'k' => 'success',
        'valid' => 'success',
        'terverifikasi' => 'success',
        'terbit' => 'success',
        'disetujui' => 'success',
        'terjadwal' => 'info',
        'berjalan' => 'primary',
        'asesmen_berjalan' => 'primary',
        'selesai' => 'teal',
        'menunggu' => 'warning',
        'menunggu_pembayaran' => 'warning',
        'pembayaran_diajukan' => 'warning',
        'menunggu_jadwal' => 'azure',
        'draf' => 'secondary',
        'draft' => 'secondary',
        'belum_terverifikasi' => 'secondary',
        'nonaktif' => 'secondary',
        'belum_kompeten' => 'danger',
        'bk' => 'danger',
        'tidak_valid' => 'danger',
        'ditolak' => 'danger',
        'dokumen_ditolak' => 'danger',
        'diblokir' => 'danger',
        'dibatalkan' => 'dark',
        'dicabut' => 'dark',
    ];

    $color = $colorMap[$normalized] ?? 'secondary';
    $displayLabel = $label ?? ucwords(str_replace(['_', '-'], ' ', $normalized));
@endphp

<span {{ $attributes->merge(['class' => "badge bg-{$color}-lt fs-{$size}"]) }}>
    <span class="badge-dot bg-{{ $color }} me-1"></span>
    {{ $displayLabel }}
</span>
