<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ROLE_PATIENT = 0;

    public const ROLE_DOCTOR = 1;

    public const ROLE_ADMIN = 2;

    public const ROLE_GUEST = 3;

    public const ROLE_LABELS = [
        self::ROLE_PATIENT => 'patient',
        self::ROLE_DOCTOR => 'doctor',
        self::ROLE_ADMIN => 'admin',
        self::ROLE_GUEST => 'guest',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public static function roleLabel(int $role): string
    {
        return self::ROLE_LABELS[$role] ?? 'unknown';
    }
}
