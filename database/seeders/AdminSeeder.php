<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Amina Patel',
                'email' => 'amina.patel@mediplus.test',
            ],
            [
                'name' => 'Kelvin Smith',
                'email' => 'kelvin.smith@mediplus.test',
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_ADMIN,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
