<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorAssistantSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Temu Rawat',
                'email' => 'admin@temurawat.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ],
            [
                'name' => 'Dokter Praktik',
                'email' => 'dokter@temurawat.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_DOKTER,
                'is_active' => true,
                'doctor' => [
                    'nama' => 'dr. Dokter Utama',
                    'status' => Doctor::STATUS_AKTIF,
                ],
            ],
            [
                'name' => 'Dokter Kedua',
                'email' => 'dokter2@temurawat.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_DOKTER,
                'is_active' => true,
                'doctor' => [
                    'nama' => 'dr. Dokter Kedua',
                    'status' => Doctor::STATUS_AKTIF,
                ],
            ],
            [
                'name' => 'Dokter Ketiga',
                'email' => 'dokter3@temurawat.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_DOKTER,
                'is_active' => true,
                'doctor' => [
                    'nama' => 'dr. Dokter Ketiga',
                    'status' => Doctor::STATUS_AKTIF,
                ],
            ],
            [
                'name' => 'Asisten Dokter',
                'email' => 'asisten@temurawat.test',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ASISTEN,
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            $doctorData = $user['doctor'] ?? null;
            unset($user['doctor']);

            $account = User::updateOrCreate(
                ['email' => $user['email']],
                $user,
            );

            if ($doctorData) {
                Doctor::updateOrCreate(
                    ['user_id' => $account->id],
                    $doctorData + ['user_id' => $account->id],
                );
            }
        }
    }
}
