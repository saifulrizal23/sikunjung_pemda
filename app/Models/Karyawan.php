<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    protected $table = 'karyawan';

    protected $fillable = [
        'user_id',
        'nik',
        'nama',
        'jabatan',
        'no_telepon',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(SettingKunjungan::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Kunjungan::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }
}
