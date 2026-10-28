<?php

namespace App\Exports;

use App\Models\Periksa;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Riwayat pasien yang pernah diperiksa SATU dokter (dokter yang sedang login).
 */
class RiwayatPasienExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private int $no = 0;

    public function __construct(private int $idDokter)
    {
    }

    public function collection()
    {
        return Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($query) {
            $query->where('id_dokter', $this->idDokter);
        })
            ->with(['daftarPoli.pasien', 'detailPeriksas.obat'])
            ->orderByDesc('tgl_periksa')
            ->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Pasien', 'Keluhan', 'Tanggal Periksa', 'Catatan Dokter', 'Obat', 'Biaya Periksa (Rp)'];
    }

    public function map($periksa): array
    {
        return [
            ++$this->no,
            $periksa->daftarPoli->pasien->nama ?? '-',
            $periksa->daftarPoli->keluhan,
            Carbon::parse($periksa->tgl_periksa)->format('d/m/Y H:i'),
            $periksa->catatan ?? '-',
            $periksa->detailPeriksas->map(fn ($detail) => $detail->obat->nama_obat ?? '-')->implode(', '),
            (int) $periksa->biaya_periksa,
        ];
    }
}
