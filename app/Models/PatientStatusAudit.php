<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PatientStatusAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'admin_id',
        'doctor_id',
        'previous_status',
        'new_status',
        'doctor_approval_note',
        'doctor_approval_granted',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'doctor_approval_granted' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Patient status audits are immutable and cannot be updated.');
        });

        static::deleting(function (): void {
            throw new LogicException('Patient status audits are immutable and cannot be deleted.');
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}
