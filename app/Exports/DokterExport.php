<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class DokterExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomValueBinder
{
    private int $no = 0;

    public function collection()
    {
        return User::where('role', 'dokter')->with('poli')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Dokter', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'Poli'];
    }

    public function map($dokter): array
    {
        return [
            ++$this->no,
            $dokter->nama,
            $dokter->email,
            $dokter->no_ktp,
            $dokter->no_hp,
            $dokter->alamat,
            $dokter->poli->nama_poli ?? '-',
        ];
    }

    /**
     * Kolom D (No. KTP) dan E (No. HP) selalu ditulis sebagai teks,
     * supaya angka 16 digit tidak berubah jadi notasi ilmiah / kehilangan digit.
     */
    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), ['D', 'E'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}