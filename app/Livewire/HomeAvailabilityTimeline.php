<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class HomeAvailabilityTimeline extends Component
{
    public Collection $doctors;

    public string $selectedDoctorId = 'all';

    public string $preferredDay = 'any';

    public array $availableSlots = [];

    public int $horizonDays = 21;

    public function mount(?int $doctorId = null): void
    {
        $this->doctors = Doctor::with(['doctorUser', 'speciality', 'schedules'])
            ->whereHas('schedules')
            ->orderByDesc('is_featured')
            ->orderBy('hospital_name')
            ->get();

        if ($doctorId) {
            $this->selectedDoctorId = (string) $doctorId;
        } else {
            $this->selectedDoctorId = 'all';
        }
        $this->hydrateSlots();
    }

    public function updatedSelectedDoctorId(): void
    {
        $this->hydrateSlots();
    }

    public function updatedPreferredDay(): void
    {
        $this->hydrateSlots();
    }

    protected function hydrateSlots(): void
    {
        if ($this->doctors->isEmpty()) {
            $this->availableSlots = [];

            return;
        }

        if ($this->selectedDoctorId === 'all') {
            $this->availableSlots = $this->buildSlotsForDoctors($this->doctors);

            return;
        }

        $doctorId = (int) $this->selectedDoctorId;
        $doctor = $this->doctors->firstWhere('id', $doctorId);

        if (! $doctor) {
            $doctor = Doctor::with(['doctorUser', 'speciality', 'schedules'])
                ->find($doctorId);

            if (! $doctor) {
                $this->availableSlots = [];

                return;
            }

            $this->doctors->push($doctor);
        }

        $doctor->loadMissing(['doctorUser', 'speciality', 'schedules']);
        $this->availableSlots = $this->buildSlots($doctor);
    }

    /**
     * Build a merged list of slots across multiple doctors.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Doctor>  $doctors
     * @return array<int, array<string, mixed>>
     */
    protected function buildSlotsForDoctors(Collection $doctors): array
    {
        $slots = [];

        foreach ($doctors as $doctor) {
            $doctor->loadMissing(['doctorUser', 'speciality', 'schedules']);
            $slots = array_merge($slots, $this->buildSlots($doctor));
        }

        usort($slots, static function (array $a, array $b): int {
            return $a['from'] <=> $b['from'];
        });

        return array_slice($slots, 0, 12);
    }

    /**
     * Build a list of the next available slots for a doctor.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildSlots(Doctor $doctor): array
    {
        $now = Carbon::now();
        $end = $now->copy()->addDays($this->horizonDays);

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$now->toDateString(), $end->toDateString()])
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', Appointment::STATUS_CANCELLED);
            })
            ->get()
            ->groupBy('appointment_date');

        $slots = [];
        $cursor = $now->copy()->startOfDay();

        while ($cursor->lte($end) && count($slots) < 12) {
            $dayOfWeek = $cursor->dayOfWeek;

            if ($this->preferredDay !== 'any' && (int) $this->preferredDay !== $dayOfWeek) {
                $cursor->addDay();

                continue;
            }

            $schedule = $doctor->schedules->firstWhere('available_day', $dayOfWeek);

            if ($schedule) {
                $from = Carbon::createFromFormat('H:i:s', $schedule->from)
                    ->setDate($cursor->year, $cursor->month, $cursor->day);
                $to = Carbon::createFromFormat('H:i:s', $schedule->to)
                    ->setDate($cursor->year, $cursor->month, $cursor->day);

                while ($from->lt($to) && $from->lte($end) && count($slots) < 12) {
                    if ($from->gt($now)) {
                        $dateKey = $from->toDateString();
                        $timeKey = $from->format('H:i:s');
                        $taken = $appointments->has($dateKey)
                            && $appointments[$dateKey]->firstWhere('appointment_time', $timeKey);

                        if (! $taken) {
                            $slots[] = [
                                'date' => $from->copy(),
                                'from' => $from->copy(),
                                'to' => $from->copy()->addHour(),
                                'day_label' => Doctor::DAYS_OF_WEEK[$dayOfWeek] ?? 'Day',
                                'doctor' => $doctor,
                            ];
                        }
                    }

                    $from->addHour();
                }
            }

            $cursor->addDay();
        }

        return $slots;
    }

    public function render()
    {
        return view('livewire.home-availability-timeline');
    }
}
