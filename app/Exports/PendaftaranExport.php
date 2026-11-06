<?php

namespace App\Exports;

use App\Models\HasilAsesmen;
use App\Models\Pendaftaran;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PendaftaranExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /**
     * @param  array{
     *     dari?: string,
     *     sampai?: string,
     *     no_pendaftaran?: string,
     *     nama_asesi?: string,
     *     nama_asesor?: string,
     *     skema_id?: string,
     *     status?: string,
     * }  $filters
     */
    public function __construct(private readonly array $filters) {}

    public function title(): string
    {
        return 'Data Pendaftaran';
    }

    public function headings(): array
    {
        return [
            'No. Pendaftaran',
            'Nama Asesi',
            'NIK (Masked)',
            'Email Asesi',
            'Skema',
            'Kode Skema',
            'Status',
            'Jadwal',
            'TUK',
            'Asesor',
            'Tanggal Pendaftaran',
            'Tanggal Disetujui',
            'Status Kompetensi',
        ];
    }

    /**
     * @param  Pendaftaran  $row
     */
    public function map($row): array
    {
        $asesorNames = $row->jadwal?->asesors
            ->map(fn ($a) => $a->nama_lengkap)
            ->implode(', ') ?? '—';

        return [
            $row->no_pendaftaran,
            $row->asesi?->user?->name ?? '—',
            $row->asesi?->user?->masked_nik ?? '—',
            $row->asesi?->user?->email ?? '—',
            $row->skema?->nama_skema ?? '—',
            $row->skema?->kode_skema ?? '—',
            str_replace('_', ' ', $row->status),
            $row->jadwal?->tanggal?->translatedFormat('d M Y') ?? '—',
            $row->jadwal?->tuk?->nama_tuk ?? '—',
            $asesorNames,
            $row->created_at?->format('d/m/Y H:i') ?? '—',
            $row->disetujui_pada?->format('d/m/Y H:i') ?? '—',
            $row->hasilAsesmen?->isFinal()
                ? ($row->hasilAsesmen->rekomendasi === HasilAsesmen::REKOMENDASI_DIREKOMENDASIKAN ? 'Kompeten' : 'Belum Kompeten')
                : ($row->asesi?->status_kompetensi
                    ? str_replace('_', ' ', $row->asesi->status_kompetensi)
                    : '—'),
        ];
    }

    public function query(): Builder
    {
        $q = Pendaftaran::query()
            ->with([
                'asesi.user',
                'skema',
                'jadwal.tuk',
                'jadwal.asesors.user',
                'hasilAsesmen',
            ]);

        $this->applyFilters($q);

        return $q->latest();
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row: bold + biru muda
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a6b8a']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    /** Terapkan semua filter ke query builder. */
    private function applyFilters(Builder $q): void
    {
        $dari = $this->filters['dari'] ?? null;
        $sampai = $this->filters['sampai'] ?? null;

        if ($dari !== null && $dari !== '') {
            $q->where('created_at', '>=', $dari.' 00:00:00');
        }

        if ($sampai !== null && $sampai !== '') {
            $q->where('created_at', '<=', $sampai.' 23:59:59');
        }

        if (filled($this->filters['no_pendaftaran'] ?? null)) {
            $q->where('no_pendaftaran', 'like', '%'.trim($this->filters['no_pendaftaran']).'%');
        }

        if (filled($this->filters['nama_asesi'] ?? null)) {
            $q->whereHas('asesi.user', function (Builder $uq): void {
                $uq->where('name', 'like', '%'.trim($this->filters['nama_asesi']).'%');
            });
        }

        if (filled($this->filters['nama_asesor'] ?? null)) {
            $q->whereHas('jadwal.asesors.user', function (Builder $uq): void {
                $uq->where('name', 'like', '%'.trim($this->filters['nama_asesor']).'%');
            });
        }

        if (filled($this->filters['skema_id'] ?? null)) {
            $q->where('skema_id', $this->filters['skema_id']);
        }

        if (filled($this->filters['status'] ?? null)) {
            $q->where('status', $this->filters['status']);
        }
    }
}
