@props([
    'path' => null,
    'label',
    'alt' => null,
    'height' => 60,
    'width' => 120,
    'margin' => 20,
])

@php
    use App\Support\PdfFile;

    /**
     * Blok tanda tangan untuk dokumen PDF.
     *
     * Merender gambar tanda tangan bila berkas tersedia, atau garis tanda
     * tangan kosong bila belum diunggah. Akan melempar galat jelas bila
     * symlink `public/storage` belum dibuat (lihat App\Support\PdfFile).
     *
     * @var string|null $path
     * @var string $label
     * @var string|null $alt
     * @var int|string $height
     * @var int|string $width
     * @var int|string $margin
     */
    $signaturePath = PdfFile::path($path);
@endphp

@if ($signaturePath)
    <div style="margin: {{ $margin }}px 0;">
        <img src="{{ $signaturePath }}"
             alt="{{ $alt ?? 'Tanda Tangan '.$label }}"
             style="max-height: {{ $height }}px; max-width: {{ $width }}px;">
    </div>
@else
    <div class="signature-line"></div>
@endif
