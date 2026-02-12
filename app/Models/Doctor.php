<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Doctor extends Model
{
    use HasFactory;

    public const DAYS_OF_WEEK = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    protected $fillable = [
        'bio',
        'hospital_name',
        'speciality_id',
        'user_id',
        'twitter',
        'instagram',
        'image',
        'experience',
        'is_featured',
    ];

    protected $appends = [
        'image_url',
    ];

    protected $casts = [
        'experience' => 'integer',
        'is_featured' => 'boolean',
    ];

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Specialities::class, 'speciality_id');
    }

    public function doctorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class, 'doctor_id');
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return Storage::url($this->image);
        }

        if ($this->doctorUser && $this->doctorUser->profile_image) {
            return Storage::url($this->doctorUser->profile_image);
        }

        return asset('doc.jpg');
    }

    public function nextAvailability(): ?array
    {
        $schedules = $this->relationLoaded('schedules')
            ? $this->schedules
            : $this->schedules()->get();

        if ($schedules->isEmpty()) {
            return null;
        }

        $now = Carbon::now();
        $nextSlot = null;
        $slotMeta = null;

        foreach ($schedules as $schedule) {
            $daysUntil = ($schedule->available_day - $now->dayOfWeek + 7) % 7;
            $candidateDate = $now->copy()->startOfDay()->addDays($daysUntil)
                ->setTimeFromTimeString($schedule->from);

            if ($daysUntil === 0 && $candidateDate->lessThanOrEqualTo($now)) {
                $candidateDate->addWeek();
            }

            if (is_null($nextSlot) || $candidateDate->lessThan($nextSlot)) {
                $nextSlot = $candidateDate;
                $slotMeta = $schedule;
            }
        }

        if (is_null($nextSlot) || is_null($slotMeta)) {
            return null;
        }

        return [
            'date' => $nextSlot,
            'from' => Carbon::createFromFormat('H:i:s', $slotMeta->from),
            'to' => Carbon::createFromFormat('H:i:s', $slotMeta->to),
            'day_label' => self::DAYS_OF_WEEK[$slotMeta->available_day] ?? 'N/A',
        ];
    }
}
