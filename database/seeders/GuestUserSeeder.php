<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestUserSeeder extends Seeder
{
    public function run(): void
    {
        $guests = [
            ['name' => 'Olivia Guest', 'email' => 'olivia.guest@mediplus.test'],
            ['name' => 'Noah Explorer', 'email' => 'noah.explorer@mediplus.test'],
        ];

        foreach ($guests as $guest) {
            User::updateOrCreate(
                ['email' => $guest['email']],
                [
                    'name' => $guest['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_GUEST,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
