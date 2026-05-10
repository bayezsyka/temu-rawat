<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DoctorAssistantSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Temu Rawat',
                'email' => 'admin@temurawat.test',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Dokter Praktik',
                'email' => 'dokter@temurawat.test',
                'password' => Hash::make('password'),
                'role' => 'dokter',
            ],
            [
                'name' => 'Asisten Dokter',
                'email' => 'asisten@temurawat.test',
                'password' => Hash::make('password'),
                'role' => 'asisten',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}