@props([
    'model' => null,
    'label',
    'alt' => null,
    'height' => 60,
    'width' => 120,
    'margin' => 20,
])

@php
    $source = \App\Support\SignatureImage::for($model);
@endphp

@if ($source)
    <div style="margin: {{ $margin }}px 0;">
        <img src="{{ $source }}"
             alt="{{ $alt ?? 'Tanda Tangan '.$label }}"
             style="max-height: {{ $height }}px; max-width: {{ $width }}px;">
    </div>
@else
    <div class="signature-line"></div>
    <div style="font-size: 7px; color: #777777; margin-top: 2px;">Tanda tangan belum tersimpan.</div>
@endif