<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_KARYAWAN = 'karyawan';

    public const ROLE_PEMOHON = 'pemohon';

    protected $attributes = [
        'role' => self::ROLE_PEMOHON,
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function karyawan(): HasOne
    {
        return $this->hasOne(Karyawan::class);
    }

    public function pemohon(): HasOne
    {
        return $this->hasOne(Pemohon::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
