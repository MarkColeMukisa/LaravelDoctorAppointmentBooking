<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Specialities;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $specialities = Specialities::pluck('id', 'speciality_name');

        $doctors = [
            [
                'user' => [
                    'name' => 'Laila Hassan',
                    'email' => 'laila.hassan@mediplus.test',
                ],
                'doctor' => [
                    'hospital_name' => 'Lagos Heart Center',
                    'speciality' => 'Cardiology',
                    'bio' => 'Board-certified cardiologist focused on preventive care and lifestyle medicine.',
                    'experience' => 12,
                    'twitter' => 'https://twitter.com/dr_laila',
                    'instagram' => 'https://instagram.com/dr_laila',
                ],
                'schedules' => [
                    ['available_day' => 1, 'from' => '08:00:00', 'to' => '12:00:00'],
                    ['available_day' => 4, 'from' => '14:00:00', 'to' => '18:00:00'],
                ],
            ],
            [
                'user' => [
                    'name' => 'Mateo Alvarez',
                    'email' => 'mateo.alvarez@mediplus.test',
                ],
                'doctor' => [
                    'hospital_name' => 'Harborview Neurology Clinic',
                    'speciality' => 'Neurology',
                    'bio' => 'Neurologist specializing in migraine management and remote care programs.',
                    'experience' => 15,
                    'twitter' => 'https://twitter.com/dr_mateo',
                    'instagram' => 'https://instagram.com/dr_mateo',
                ],
                'schedules' => [
                    ['available_day' => 2, 'from' => '10:00:00', 'to' => '15:00:00'],
                    ['available_day' => 5, 'from' => '09:00:00', 'to' => '13:00:00'],
                ],
            ],
            [
                'user' => [
                    'name' => 'Chen Wei',
                    'email' => 'chen.wei@mediplus.test',
                ],
                'doctor' => [
                    'hospital_name' => 'Skyline Children Hospital',
                    'speciality' => 'Pediatrics',
                    'bio' => 'Pediatrician helping families track growth milestones with digital check-ins.',
                    'experience' => 9,
                    'twitter' => 'https://twitter.com/dr_chenwei',
                    'instagram' => 'https://instagram.com/dr_chenwei',
                ],
                'schedules' => [
                    ['available_day' => 0, 'from' => '11:00:00', 'to' => '16:00:00'],
                    ['available_day' => 3, 'from' => '09:00:00', 'to' => '12:00:00'],
                ],
            ],
        ];

        foreach ($doctors as $entry) {
            $user = User::updateOrCreate(
                ['email' => $entry['user']['email']],
                [
                    'name' => $entry['user']['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_DOCTOR,
                    'email_verified_at' => now(),
                ]
            );

            $specialityId = $specialities[$entry['doctor']['speciality']] ?? null;

            if (! $specialityId) {
                continue;
            }

            $doctor = Doctor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'speciality_id' => $specialityId,
                    'hospital_name' => $entry['doctor']['hospital_name'],
                    'bio' => $entry['doctor']['bio'],
                    'experience' => $entry['doctor']['experience'],
                    'is_featured' => true,
                    'twitter' => $entry['doctor']['twitter'],
                    'instagram' => $entry['doctor']['instagram'],
                ]
            );

            foreach ($entry['schedules'] as $schedule) {
                DoctorSchedule::updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'available_day' => $schedule['available_day'],
                        'from' => $schedule['from'],
                        'to' => $schedule['to'],
                    ],
                    []
                );
            }
        }
    }
}
