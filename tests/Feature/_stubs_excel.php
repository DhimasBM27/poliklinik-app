<?php

// Stub antarmuka Maatwebsite/Excel agar test export tetap bisa berjalan
// walau paket maatwebsite/excel belum terpasang. Jika paket asli sudah ada, stub dilewati.
namespace Maatwebsite\Excel\Concerns {
    if (!interface_exists(FromCollection::class)) {
        interface FromCollection {}
        interface WithHeadings {}
        interface WithMapping {}
        interface ShouldAutoSize {}
    }
}
