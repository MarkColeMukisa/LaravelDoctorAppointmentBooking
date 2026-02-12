<?php

namespace Database\Seeders;

use App\Models\Specialities;
use Illuminate\Database\Seeder;

class SpecialitySeeder extends Seeder
{
    /**
     * Seed the application's specialities catalog with realistic domains.
     */
    public function run(): void
    {
        $specialities = [
            'Cardiology',
            'Dermatology',
            'Pediatrics',
            'Neurology',
            'Orthopedics',
            'Family Medicine',
        ];

        foreach ($specialities as $name) {
            Specialities::firstOrCreate(
                ['speciality_name' => $name],
                ['speciality_name' => $name]
            );
        }
    }
}
