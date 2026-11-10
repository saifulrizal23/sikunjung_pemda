<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kunjungan extends Model
{
    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'kunjungan';

    protected $fillable = [
        'pemohon_id',
        'karyawan_id',
        'tanggal',
        'jam',
        'keperluan',
        'status',
        'catatan',
    ];

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(Pemohon::class);
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }
}
