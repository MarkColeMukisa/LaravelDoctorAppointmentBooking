<?php

namespace App\Livewire;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Support\Collection;
use Livewire\Component;

class DoctorAvailabilityPanel extends Component
{
    public ?Doctor $doctor = null;

    public Collection $schedules;

    public array $daysOfWeek = Doctor::DAYS_OF_WEEK;

    public array $form = [
        'available_day' => '',
        'from' => '',
        'to' => '',
    ];

    public ?int $editingScheduleId = null;

    protected $rules = [
        'form.available_day' => 'required|integer|between:0,6',
        'form.from' => 'required|date_format:H:i',
        'form.to' => 'required|date_format:H:i|after:form.from',
    ];

    public function mount(): void
    {
        $this->doctor = Doctor::with('schedules')
            ->where('user_id', auth()->id())
            ->first();

        $this->schedules = collect();
        $this->refreshSchedules();
    }

    public function refreshSchedules(): void
    {
        if (! $this->doctor) {
            $this->schedules = collect();

            return;
        }

        $this->schedules = $this->doctor->schedules()
            ->orderBy('available_day')
            ->orderBy('from')
            ->get();
    }

    public function save(): void
    {
        $this->validate();

        if (! $this->doctor) {
            $this->addError('form.available_day', 'Doctor profile not found.');

            return;
        }

        $from = $this->normalizeTime($this->form['from']);
        $to = $this->normalizeTime($this->form['to']);

        $overlapExists = $this->doctor->schedules()
            ->when($this->editingScheduleId, function ($query) {
                return $query->where('id', '!=', $this->editingScheduleId);
            })
            ->where('available_day', $this->form['available_day'])
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('from', [$from, $to])
                    ->orWhereBetween('to', [$from, $to])
                    ->orWhere(function ($subQuery) use ($from, $to) {
                        $subQuery->where('from', '<', $from)
                            ->where('to', '>', $to);
                    });
            })
            ->exists();

        if ($overlapExists) {
            $this->addError('form.from', 'This day already has an overlapping slot.');

            return;
        }

        DoctorSchedule::updateOrCreate(
            ['id' => $this->editingScheduleId],
            [
                'doctor_id' => $this->doctor->id,
                'available_day' => $this->form['available_day'],
                'from' => $from,
                'to' => $to,
            ]
        );

        $message = $this->editingScheduleId
            ? 'Schedule updated successfully.'
            : 'Schedule created successfully.';

        $this->resetForm();
        $this->refreshSchedules();
        session()->flash('availability_message', $message);
    }

    public function edit(int $scheduleId): void
    {
        $schedule = $this->schedules->firstWhere('id', $scheduleId);

        if (! $schedule) {
            return;
        }

        $this->editingScheduleId = $scheduleId;
        $this->form['available_day'] = (string) $schedule->available_day;
        $this->form['from'] = substr($schedule->from, 0, 5);
        $this->form['to'] = substr($schedule->to, 0, 5);
    }

    public function delete(int $scheduleId): void
    {
        if (! $this->doctor) {
            return;
        }

        DoctorSchedule::where('id', $scheduleId)
            ->where('doctor_id', $this->doctor->id)
            ->delete();

        if ($this->editingScheduleId === $scheduleId) {
            $this->resetForm();
        }

        $this->refreshSchedules();
        session()->flash('availability_message', 'Schedule removed.');
    }

    public function resetForm(): void
    {
        $this->editingScheduleId = null;
        $this->form = [
            'available_day' => '',
            'from' => '',
            'to' => '',
        ];
        $this->resetErrorBag();
    }

    protected function normalizeTime(string $value): string
    {
        return strlen($value) === 5 ? "{$value}:00" : $value;
    }

    public function render()
    {
        return view('livewire.doctor-availability-panel');
    }
}
