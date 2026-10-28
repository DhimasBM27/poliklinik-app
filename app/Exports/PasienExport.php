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

class PasienExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithCustomValueBinder
{
    private int $no = 0;

    public function collection()
    {
        return User::where('role', 'pasien')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Pasien', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'No. Rekam Medis'];
    }

    public function map($pasien): array
    {
        return [
            ++$this->no,
            $pasien->nama,
            $pasien->email,
            $pasien->no_ktp,
            $pasien->no_hp,
            $pasien->alamat,
            $pasien->no_rm ?? '-',
        ];
    }

    /**
     * Kolom D (No. KTP) dan E (No. HP) selalu ditulis sebagai teks.
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