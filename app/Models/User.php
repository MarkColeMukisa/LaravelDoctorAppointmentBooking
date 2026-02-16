<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_PATIENT = 0;

    public const ROLE_DOCTOR = 1;

    public const ROLE_ADMIN = 2;

    public const ROLE_GUEST = 3;

    public const PATIENT_STATUS_ACTIVE = 'active';

    public const PATIENT_STATUS_INACTIVE = 'inactive';

    public const PATIENT_STATUS_DECEASED = 'deceased';

    public const PATIENT_STATUS_TRANSFERRED = 'transferred';

    public const ROLE_LABELS = [
        self::ROLE_PATIENT => 'patient',
        self::ROLE_DOCTOR => 'doctor',
        self::ROLE_ADMIN => 'admin',
        self::ROLE_GUEST => 'guest',
    ];

    public const PATIENT_STATUSES = [
        self::PATIENT_STATUS_ACTIVE,
        self::PATIENT_STATUS_INACTIVE,
        self::PATIENT_STATUS_DECEASED,
        self::PATIENT_STATUS_TRANSFERRED,
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
        'contact_number',
        'address',
        'date_of_birth',
        'gender',
        'registration_date',
        'patient_status',
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
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'registration_date' => 'date',
        ];
    }

    public static function roleLabel(int $role): string
    {
        return self::ROLE_LABELS[$role] ?? 'unknown';
    }

    public static function patientStatusLabel(string $status): string
    {
        return str($status)->replace('_', ' ')->title()->toString();
    }

    public function isSuperAdmin(): bool
    {
        if ($this->role !== self::ROLE_ADMIN) {
            return false;
        }

        $superAdminEmails = config('app.super_admin_emails', []);

        return in_array(strtolower((string) $this->email), $superAdminEmails, true);
    }

    public function patientAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function patientStatusAudits(): HasMany
    {
        return $this->hasMany(PatientStatusAudit::class, 'patient_id');
    }

    public function approvedPatientStatusAudits(): HasMany
    {
        return $this->hasMany(PatientStatusAudit::class, 'admin_id');
    }

    public function latestPatientStatusAudit(): HasOne
    {
        return $this->hasOne(PatientStatusAudit::class, 'patient_id')->latestOfMany();
    }

    public function patientStatusChangeRequests(): HasMany
    {
        return $this->hasMany(PatientStatusChangeRequest::class, 'patient_id');
    }

    public function requestedPatientStatusChanges(): HasMany
    {
        return $this->hasMany(PatientStatusChangeRequest::class, 'admin_id');
    }

    public function latestPatientStatusChangeRequest(): HasOne
    {
        return $this->hasOne(PatientStatusChangeRequest::class, 'patient_id')->latestOfMany();
    }
}
