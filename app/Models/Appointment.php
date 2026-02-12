<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'appointment_date',
        'appointment_time',
        'appointment_type',
        'status',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function scopeSearch($query, $value)
    {
        $query->where('appointment_date', 'like', "%{$value}%")
            ->orWhere('appointment_time', 'like', "%{$value}%")
            ->orWhereHas('doctor.doctorUser', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            })
            ->orWhereHas('patient', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%");
            });
    }
}
