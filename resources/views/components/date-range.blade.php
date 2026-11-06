@props([
    'name' => 'date_range',
    'value' => null,
    'label' => 'Tanggal',
])

@php
    $start = $value['start'] ?? old($name.'[start]') ?? null;
    $end = $value['end'] ?? old($name.'[end]') ?? null;
    $nameStart = $name.'[start]';
    $nameEnd = $name.'[end]';
@endphp

<div class="date-range d-flex align-items-center border border-subtle rounded px-2 py-1" style="width: auto; max-width: 280px;">
    <label class="visually-hidden">{{ $label }}</label>
    <input type="date" name="{{ $nameStart }}" class="form-control form-control-sm border-0 ps-0 bg-transparent"
           value="{{ $start }}" style="width: 110px;">
    <span class="text-secondary small mx-1">s.d.</span>
    <input type="date" name="{{ $nameEnd }}" class="form-control form-control-sm border-0 pe-0 bg-transparent"
           value="{{ $end }}" style="width: 110px;">
</div>
