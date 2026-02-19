<?php

namespace App\Livewire;

use App\Models\Doctor;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditDoctor extends Component
{
    use WithFileUploads;

    public Doctor $doctor;

    public string $name = '';

    public string $email = '';

    public string $bio = '';

    public string $speciality_id = '';

    public string $hospital_name = '';

    public string $twitter = '';

    public string $instagram = '';

    public string $experience = '';

    public $specialities;

    public $image;

    public ?string $current_image = null;

    public function mount(int $doctor_id): void
    {
        $this->doctor = Doctor::query()
            ->with(['doctorUser', 'speciality'])
            ->findOrFail($doctor_id);
        $currentUser = auth()->user();
        if ($currentUser && (int) $currentUser->role === User::ROLE_DOCTOR && $this->doctor->user_id !== $currentUser->id) {
            abort(403);
        }

        $this->specialities = Specialities::query()
            ->select(['id', 'speciality_name'])
            ->orderBy('speciality_name')
            ->get();

        $this->name = $this->doctor->doctorUser?->name ?? '';
        $this->email = $this->doctor->doctorUser?->email ?? '';
        $this->bio = (string) $this->doctor->bio;
        $this->speciality_id = (string) $this->doctor->speciality_id;
        $this->hospital_name = (string) $this->doctor->hospital_name;
        $this->twitter = (string) ($this->doctor->twitter ?? '');
        $this->instagram = (string) ($this->doctor->instagram ?? '');
        $this->experience = (string) ($this->doctor->experience ?? '');
        $this->current_image = $this->doctor->image;
    }

    public function update(): mixed
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($this->doctor->user_id),
            ],
            'bio' => ['required', 'string'],
            'hospital_name' => ['required', 'string', 'max:255'],
            'speciality_id' => ['required', 'integer', 'exists:specialities,id'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'experience' => ['required', 'integer', 'min:0', 'max:80'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $this->doctor->update([
            'bio' => $validated['bio'],
            'hospital_name' => $validated['hospital_name'],
            'speciality_id' => (int) $validated['speciality_id'],
            'twitter' => $validated['twitter'],
            'instagram' => $validated['instagram'],
            'experience' => (int) $validated['experience'],
            'image' => $this->image ? $this->image->store('public/doctors') : $this->current_image,
        ]);

        $cleanName = trim($validated['name']);
        if (stripos($cleanName, 'Dr. ') === 0) {
            $cleanName = substr($cleanName, 4);
        } elseif (stripos($cleanName, 'Dr ') === 0) {
            $cleanName = substr($cleanName, 3);
        }

        User::query()
            ->whereKey($this->doctor->user_id)
            ->update([
                'name' => $cleanName,
                'email' => $validated['email'],
            ]);

        session()->flash('message', 'Doctor updated successfully.');

        if ((int) auth()->user()?->role === User::ROLE_DOCTOR) {
            return $this->redirect('/doctor/profile/edit', navigate: true);
        }

        return $this->redirect('/admin/doctors', navigate: true);
    }

    public function render(): mixed
    {
        return view('livewire.edit-doctor');
    }
}
