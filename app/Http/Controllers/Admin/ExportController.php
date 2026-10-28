<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DokterExport;
use App\Exports\ObatExport;
use App\Exports\PasienExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function dokter()
    {
        return Excel::download(new DokterExport, 'data-dokter.xlsx');
    }

    public function pasien()
    {
        return Excel::download(new PasienExport, 'data-pasien.xlsx');
    }

    public function obat()
    {
        return Excel::download(new ObatExport, 'data-obat.xlsx');
    }
}
