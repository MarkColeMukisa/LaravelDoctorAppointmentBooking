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
                'name' => 'Casey Pires',
                'email' => 'Caseypires88@gmail.com',
            ],
            [
                'name' => 'Joe Gapp',
                'email' => 'joegapp256@gmail.com',
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
