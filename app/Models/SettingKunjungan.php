<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingKunjungan extends Model
{
    protected $table = 'setting_kunjungan';

    protected $fillable = [
        'karyawan_id',
        'jabatan',
        'status',
        'tanggal',
        'jam_aktif',
        'jam_non_aktif',
    ];

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
