<?php

namespace App\Exports;

use App\Models\JadwalPeriksa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Jadwal periksa milik SATU dokter (dokter yang sedang login).
 */
class JadwalPeriksaExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private int $no = 0;

    public function __construct(private int $idDokter)
    {
    }

    public function collection()
    {
        return JadwalPeriksa::where('id_dokter', $this->idDokter)->orderBy('id')->get();
    }

    public function headings(): array
    {
        return ['No', 'Hari', 'Jam Mulai', 'Jam Selesai', 'Status'];
    }

    public function map($jadwal): array
    {
        return [
            ++$this->no,
            $jadwal->hari,
            Carbon::parse($jadwal->jam_mulai)->format('H:i'),
            Carbon::parse($jadwal->jam_selesai)->format('H:i'),
            $jadwal->status,
        ];
    }
}
