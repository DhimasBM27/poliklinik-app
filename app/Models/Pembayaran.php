<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    public const BELUM_BAYAR = 'belum_bayar';
    public const MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const LUNAS = 'lunas';

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_periksa',
        'jumlah',
        'bukti_bayar',
        'status',
        'tgl_upload',
        'tgl_verifikasi',
        'diverifikasi_oleh',
    ];

    protected function casts(): array
    {
        return [
            'tgl_upload'     => 'datetime',
            'tgl_verifikasi' => 'datetime',
        ];
    }

    public function periksa()
    {
        return $this->belongsTo(Periksa::class, 'id_periksa');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Label status yang ditampilkan ke pengguna.
     */
    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            self::LUNAS => 'Lunas',
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            default => 'Belum Dibayar',
        };
    }
}
