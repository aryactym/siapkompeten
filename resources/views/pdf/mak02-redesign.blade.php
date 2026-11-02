<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>FR.AK.02 · {{ $pendaftaran->no_pendaftaran }}</title>
    <style>
        @page {
            margin: 16mm 14mm;
            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
                font-size: 8px;
                color: #666;
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #1F3A5F;
            font-size: 9pt;
            line-height: 1.4;
        }

        /* ===== HEADER STRIP ===== */
        .page-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3mm;
            background: linear-gradient(90deg, #1F3A5F 0%, #2A7F8E 100%);
            z-index: 1;
        }

        /* ===== FOOTER ===== */
        .page-footer {
            position: fixed;
            bottom: 14mm;
            left: 14mm;
            right: 14mm;
            height: 6mm;
            font-size: 7pt;
            color: #999;
            border-top: 1px solid #E0E6ED;
            padding-top: 2mm;
            display: flex;
            justify-content: space-between;
        }

        .page-footer-left {
            flex: 1;
        }

        .page-footer-right {
            text-align: right;
        }

        /* ===== CONTENT ===== */
        .content {
            margin-top: 5mm;
            margin-bottom: 15mm;
        }

        /* ===== HEADER HALAMAN 1 ===== */
        .doc-header {
            text-align: center;
            margin-bottom: 12mm;
            page-break-after: avoid;
        }

        .doc-header-logos {
            display: table;
            width: 100%;
            margin-bottom: 6mm;
        }

        .doc-header-logos-item {
            display: table-cell;
            width: 20%;
            text-align: center;
            vertical-align: middle;
        }

        .doc-header-logos-item img {
            max-height: 15mm;
        }

        .doc-header-center {
            display: table-cell;
            width: 60%;
            text-align: center;
            vertical-align: middle;
        }

        .doc-code {
            font-size: 7pt;
            color: #2A7F8E;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 2mm;
        }

        .doc-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1F3A5F;
            margin-bottom: 1mm;
        }

        .doc-subtitle {
            font-size: 10pt;
            color: #666;
            font-weight: normal;
        }

        /* ===== BLOK IDENTITAS ===== */
        .identity-block {
            display: table;
            width: 100%;
            margin-bottom: 10mm;
            page-break-after: avoid;
            background: #EEF3F8;
            border: 1px solid #C9D4E0;
            border-radius: 3px;
        }

        .identity-column {
            display: table-cell;
            width: 50%;
            padding: 6mm;
            vertical-align: top;
            border-right: 1px solid #C9D4E0;
        }

        .identity-column:last-child {
            border-right: none;
        }

        .identity-row {
            margin-bottom: 3mm;
            font-size: 8.5pt;
        }

        .identity-row strong {
            color: #1F3A5F;
            font-weight: bold;
            display: inline-block;
            width: 35%;
            vertical-align: top;
        }

        .identity-row span {
            color: #333;
            display: inline-block;
            width: 65%;
        }

        /* ===== RINGKASAN UNIT ===== */
        .summary-section {
            margin-bottom: 8mm;
            page-break-inside: avoid;
        }

        .summary-section-title {
            font-size: 9pt;
            font-weight: bold;
            color: #1F3A5F;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4mm;
            padding-bottom: 2mm;
            border-bottom: 2px solid #2A7F8E;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6mm;
        }

        .table-summary th,
        .table-summary td {
            border: 1px solid #C9D4E0;
            padding: 3mm;
            text-align: left;
            font-size: 8pt;
        }

        .table-summary th {
            background: #EEF3F8;
            color: #1F3A5F;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
        }

        .table-summary tr:nth-child(even) {
            background: #F8FAFC;
        }

        .table-summary td {
            vertical-align: middle;
        }

        .table-summary .col-no {
            width: 6%;
            text-align: center;
        }

        .table-summary .col-code {
            width: 10%;
            color: #666;
        }

        .table-summary .col-title {
            width: 50%;
        }

        .table-summary .col-kuk {
            width: 10%;
            text-align: center;
        }

        .table-summary .col-decision {
            width: 14%;
            text-align: center;
        }

        /* ===== KOTAK HASIL RINGKAS ===== */
        .result-summary-box {
            background: #EEF3F8;
            border: 1px solid #C9D4E0;
            padding: 6mm;
            margin-bottom: 10mm;
            font-size: 8.5pt;
            page-break-inside: avoid;
        }

        .result-summary-row {
            display: table;
            width: 100%;
            margin-bottom: 2mm;
        }

        .result-summary-row:last-child {
            margin-bottom: 0;
        }

        .result-summary-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #1F3A5F;
        }

        .result-summary-value {
            display: table-cell;
            width: 60%;
            color: #333;
        }

        .result-summary-value.kompeten {
            color: #1E7A4A;
            font-weight: bold;
        }

        /* ===== PER UNIT KOMPETENSI ===== */
        .unit-section {
            margin-bottom: 12mm;
            page-break-inside: avoid;
        }

        .unit-section-break {
            page-break-after: auto;
        }

        .unit-title {
            font-size: 9pt;
            font-weight: bold;
            color: #1F3A5F;
            margin-bottom: 3mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #2A7F8E;
        }

        .unit-code {
            font-size: 8pt;
            color: #666;
        }

        /* ===== TABEL METODE ===== */
        .table-metode {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6mm;
            font-size: 7.5pt;
        }

        .table-metode th,
        .table-metode td {
            border: 1px solid #C9D4E0;
            padding: 3mm 2mm;
            text-align: center;
            vertical-align: middle;
        }

        .table-metode th {
            background: #2A7F8E;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 6.5pt;
        }

        .table-metode .metode-name {
            text-align: left;
            font-size: 7pt;
            color: #1F3A5F;
            font-weight: 500;
        }

        .table-metode .metode-used {
            font-weight: bold;
            color: #1E7A4A;
        }

        .table-metode .metode-unused {
            color: #999;
        }

        .table-metode .label-bukti {
            text-align: left;
            font-weight: bold;
            color: #1F3A5F;
        }

        .table-metode .label-keputusan {
            text-align: left;
            font-weight: bold;
            color: #1F3A5F;
        }

        /* ===== TABEL KUK ===== */
        .table-kuk {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8mm;
            font-size: 8pt;
        }

        .table-kuk thead {
            display: table-header-group;
        }

        .table-kuk th,
        .table-kuk td {
            border: 1px solid #C9D4E0;
            padding: 3mm;
            text-align: left;
            vertical-align: top;
        }

        .table-kuk th {
            background: #2A7F8E;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
        }

        .table-kuk tr {
            page-break-inside: avoid;
        }

        .table-kuk tr:nth-child(even) {
            background: #F8FAFC;
        }

        .table-kuk .col-no {
            width: 4%;
            text-align: center;
        }

        .table-kuk .col-elemen {
            width: 6%;
            font-size: 7pt;
            color: #888;
        }

        .table-kuk .col-kuk {
            width: 50%;
        }

        .table-kuk .col-tercapai {
            width: 8%;
            text-align: center;
            font-weight: bold;
        }

        .table-kuk .col-bukti {
            width: 8%;
            text-align: center;
        }

        .table-kuk .col-4c {
            width: 12%;
            text-align: center;
            font-size: 7.5pt;
        }

        .kuk-elemen-name {
            font-size: 7pt;
            color: #999;
            margin-bottom: 1mm;
            font-weight: normal;
        }

        .kuk-statement {
            color: #333;
            line-height: 1.35;
        }

        /* ===== TANDA TANGAN ===== */
        .signatures-section {
            margin-top: 16mm;
            page-break-inside: avoid;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signatures-cell {
            width: 33.33%;
            text-align: center;
            padding: 2mm;
            vertical-align: bottom;
        }

        .signature-label {
            font-size: 8pt;
            font-weight: bold;
            color: #1F3A5F;
            margin-bottom: 8mm;
        }

        .signature-space {
            border-top: 1px solid #333;
            height: 30mm;
            margin-bottom: 2mm;
        }

        .signature-img {
            max-height: 25mm;
            margin-bottom: 2mm;
        }

        .signature-name {
            font-size: 8pt;
            color: #333;
            font-weight: normal;
        }

        /* ===== UTILITY ===== */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .color-kompeten {
            color: #1E7A4A;
        }

        .color-belum {
            color: #666;
        }

        .page-break {
            page-break-after: always;
        }

        .spacer {
            margin-bottom: 4mm;
        }
    </style>
</head>
<body>
    <div class="page-header"></div>
    <div class="page-footer">
        <span class="page-footer-left">FR.AK.02 · {{ $pendaftaran->asesi->user->name ?? '—' }} · No. {{ $pendaftaran->asesi->no_registrasi ?? '—' }}</span>
        <span class="page-footer-right">{{ $pendaftaran->no_pendaftaran }}</span>
    </div>

    <div class="content">
        <!-- ===== HEADER HALAMAN 1 ===== -->
        <div class="doc-header">
            <div class="doc-header-logos">
                <div class="doc-header-logos-item">
                    @if ($pendaftaran->institution?->logo_bnsp_path)
                        <img src="{{ public_path('storage/' . $pendaftaran->institution->logo_bnsp_path) }}" alt="Logo BNSP">
                    @endif
                </div>
                <div class="doc-header-center">
                    <div class="doc-code">FR.AK.02</div>
                    <div class="doc-title">Rekaman Asesmen Kompetensi</div>
                    <div class="doc-subtitle">{{ $pendaftaran->skema->nama_skema ?? '—' }}</div>
                </div>
                <div class="doc-header-logos-item">
                    @if ($pendaftaran->institution?->logo_lsp_path)
                        <img src="{{ public_path('storage/' . $pendaftaran->institution->logo_lsp_path) }}" alt="Logo LSP">
                    @endif
                </div>
            </div>
        </div>

        <!-- ===== BLOK IDENTITAS ===== -->
        <div class="identity-block">
            <div class="identity-column">
                <div class="identity-row"><strong>No. Pendaftaran:</strong> <span>{{ $pendaftaran->no_pendaftaran }}</span></div>
                <div class="identity-row"><strong>Asesi:</strong> <span>{{ $pendaftaran->asesi->user->name ?? '—' }}</span></div>
                <div class="identity-row"><strong>No. Registrasi:</strong> <span>{{ $pendaftaran->asesi->no_registrasi ?? '—' }}</span></div>
                <div class="identity-row"><strong>Nama Skema:</strong> <span>{{ $pendaftaran->skema->nama_skema ?? '—' }}</span></div>
            </div>
            <div class="identity-column">
                <div class="identity-row"><strong>Asesor:</strong> <span>{{ $hasil->asesor->user->name ?? '—' }}</span></div>
                <div class="identity-row"><strong>Status:</strong> <span>{{ strtoupper($hasil->status) }}</span></div>
                <div class="identity-row"><strong>Tanggal Asesmen:</strong> <span>{{ $pendaftaran->jadwal?->tanggal?->translatedFormat('d F Y') ?: '—' }}</span></div>
                <div class="identity-row"><strong>Final:</strong> <span>{{ $hasil->finalized_at?->translatedFormat('d F Y H:i') ?: '—' }}</span></div>
            </div>
        </div>

        <div class="spacer"></div>

        <!-- ===== RINGKASAN UNIT ===== -->
        <div class="summary-section">
            <div class="summary-section-title">Ringkasan Unit Kompetensi</div>
            <table class="table-summary">
                <thead>
                    <tr>
                        <th class="col-no">No.</th>
                        <th class="col-code">Kode</th>
                        <th class="col-title">Unit Kompetensi</th>
                        <th class="col-kuk">KUK</th>
                        <th class="col-decision">Keputusan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 0; @endphp
                    @foreach ($units as $unit)
                        @php
                            $kukUnit = $unit->elemenKompetensis
                                ->flatMap(fn ($e) => $e->kriteriaUnjukKerjas)
                                ->pluck('id');
                            $kukUnitDinilai = $hasilKuks->filter(fn ($k) => in_array($k->kuk_id, $kukUnit->all()))
                                ->filter(fn ($k) => $k->tercapai !== null)->count();
                            $hasilUnit = $hasilUnits->get($unit->id);
                            $no++;
                        @endphp
                        <tr>
                            <td class="col-no">{{ $no }}</td>
                            <td class="col-code">{{ $unit->kode_unit }}</td>
                            <td class="col-title">{{ $unit->judul_unit }}</td>
                            <td class="col-kuk">{{ $kukUnitDinilai }} / {{ $kukUnit->count() }}</td>
                            <td class="col-decision {{ $hasilUnit?->keputusan === 'kompeten' ? 'color-kompeten' : '' }}">{{ $hasilUnit?->keputusan ? ucwords(str_replace('_', ' ', $hasilUnit->keputusan)) : '—' }}</td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background: #EEF3F8;">
                        <td colspan="2">TOTAL</td>
                        <td></td>
                        <td class="col-kuk">{{ $hasilKuks->filter(fn ($k) => $k->tercapai !== null)->count() }} / {{ $hasilKuks->count() }}</td>
                        <td class="col-decision"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ===== HASIL RINGKAS ===== -->
        <div class="result-summary-box">
            <div class="result-summary-row">
                <span class="result-summary-label">Persentase KUK Tercapai:</span>
                <span class="result-summary-value">{{ number_format($hasil->persen_kompeten, 2, ',', '.') }}%</span>
            </div>
            <div class="result-summary-row">
                <span class="result-summary-label">Rekomendasi:</span>
                <span class="result-summary-value {{ $hasil->rekomendasi === \App\Models\HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'kompeten' : '' }}">
                    {{ $hasil->rekomendasi === \App\Models\HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'Kompeten' : 'Belum Kompeten' }}
                </span>
            </div>
            <div class="result-summary-row">
                <span class="result-summary-label">Umpan Balik Diberikan:</span>
                <span class="result-summary-value">{{ $hasil->umpan_balik_diberikan ? 'Ya' : 'Belum' }}</span>
            </div>
        </div>

        <!-- ===== PER UNIT ===== -->
        @foreach ($units as $unitIdx => $unit)
            @php
                $kukUnit = $unit->elemenKompetensis
                    ->flatMap(fn ($e) => $e->kriteriaUnjukKerjas)
                    ->pluck('id');
                $hasilUnit = $hasilUnits->get($unit->id);
            @endphp

            <div class="unit-section {{ $unitIdx > 0 && $unitIdx % 2 === 0 ? 'page-break' : '' }}">
                <div class="unit-title">
                    <span class="unit-code">{{ $unit->kode_unit }}</span> · {{ $unit->judul_unit }}
                </div>

                <!-- TABEL METODE -->
                <table class="table-metode">
                    <tr>
                        <td colspan="6" style="background: #2A7F8E; color: white; font-weight: bold; text-align: left; padding: 2mm;">Metode Asesmen</td>
                    </tr>
                    <tr>
                        @foreach (['observasi', 'demonstrasi_simulasi', 'tes_lisan', 'tes_tertulis', 'wawancara', 'portofolio'] as $metode)
                            <td>
                                <div class="metode-name">{{ \App\Models\HasilAsesmenMetode::METODE_LABELS[$metode] ?? ucfirst(str_replace('_', ' ', $metode)) }}</div>
                                <div class="{{ in_array($metode, $metodeDefault ?? []) ? 'metode-used' : 'metode-unused' }}">
                                    {{ in_array($metode, $metodeDefault ?? []) ? '✓' : '—' }}
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td colspan="6" class="label-bukti">Bukti Pencapaian: Langsung / Tidak Langsung / Tambahan</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="label-keputusan">Keputusan Unit: <strong>{{ $hasilUnit?->keputusan ? ucwords(str_replace('_', ' ', $hasilUnit->keputusan)) : '—' }}</strong></td>
                    </tr>
                </table>

                <!-- TABEL KUK -->
                <table class="table-kuk">
                    <thead>
                        <tr>
                            <th class="col-no">No.</th>
                            <th class="col-elemen">Elemen</th>
                            <th class="col-kuk">Kriteria Unjuk Kerja</th>
                            <th class="col-tercapai">Tercapai</th>
                            <th class="col-bukti">Bukti</th>
                            <th class="col-4c">4C</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $kukNo = 0; @endphp
                        @foreach ($unit->elemenKompetensis as $elemen)
                            @foreach ($elemen->kriteriaUnjukKerjas as $kuk)
                                @php
                                    $hasilKuk = $hasilKuks->get($kuk->id);
                                    $kukNo++;
                                @endphp
                                <tr>
                                    <td class="col-no">{{ $kukNo }}</td>
                                    <td class="col-elemen">{{ $elemen->nama_elemen }}</td>
                                    <td class="col-kuk">
                                        <div class="kuk-statement">{{ $kuk->pernyataan_kuk }}</div>
                                    </td>
                                    <td class="col-tercapai">{{ $hasilKuk?->tercapai === true ? 'Ya' : ($hasilKuk?->tercapai === false ? 'Tidak' : '—') }}</td>
                                    <td class="col-bukti">{{ $hasilKuk ? (($hasilKuk->valid ? '1' : '0') . ($hasilKuk->asli ? '1' : '0') . ($hasilKuk->terkini ? '1' : '0') . ($hasilKuk->memadai ? '1' : '0')) : '—' }}</td>
                                    <td class="col-4c">{{ $hasilKuk ? ($hasilKuk->valid ? 'V' : '-') . ($hasilKuk->asli ? 'A' : '-') . ($hasilKuk->terkini ? 'T' : '-') . ($hasilKuk->memadai ? 'M' : '-') : '—' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach

        <!-- ===== TANDA TANGAN ===== -->
        <div class="signatures-section">
            <table class="signatures-table">
                <tr>
                    <td class="signatures-cell">
                        <div class="signature-label">Asesi,</div>
                        <div class="signature-space">
                            @if ($pendaftaran->asesi->user->signature_path)
                                <img src="{{ public_path('storage/' . $pendaftaran->asesi->user->signature_path) }}" alt="Tanda Tangan Asesi" class="signature-img">
                            @endif
                        </div>
                        <div class="signature-name">{{ $pendaftaran->asesi->user->name ?? '—' }}</div>
                    </td>
                    <td class="signatures-cell">
                        <div class="signature-label">Asesor,</div>
                        <div class="signature-space">
                            @if ($hasil->asesor->user->signature_path)
                                <img src="{{ public_path('storage/' . $hasil->asesor->user->signature_path) }}" alt="Tanda Tangan Asesor" class="signature-img">
                            @endif
                        </div>
                        <div class="signature-name">{{ $hasil->asesor->user->name ?? '—' }}</div>
                    </td>
                    <td class="signatures-cell">
                        <div class="signature-label">Ketua LSP,</div>
                        <div class="signature-space">
                            @if ($pendaftaran->institution?->tanda_tangan_path)
                                <img src="{{ public_path('storage/' . $pendaftaran->institution->tanda_tangan_path) }}" alt="Tanda Tangan Ketua LSP" class="signature-img">
                            @endif
                        </div>
                        <div class="signature-name">{{ $pendaftaran->institution?->nama_ketua ?? '—' }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
