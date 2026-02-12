<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $patients = [
            ['name' => 'Sophia Ndlovu', 'email' => 'sophia.ndlovu@patients.test'],
            ['name' => 'Ethan Brooks', 'email' => 'ethan.brooks@patients.test'],
            ['name' => 'Ravi Kumar', 'email' => 'ravi.kumar@patients.test'],
            ['name' => 'Melissa Ortega', 'email' => 'melissa.ortega@patients.test'],
            ['name' => 'Chiamaka Obi', 'email' => 'chiamaka.obi@patients.test'],
        ];

        foreach ($patients as $patient) {
            User::updateOrCreate(
                ['email' => $patient['email']],
                [
                    'name' => $patient['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_PATIENT,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
