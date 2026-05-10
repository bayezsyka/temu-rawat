<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Database\Seeders\DoctorAssistantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageOneFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_staff_cannot_access_staff_panel(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get('/panel')
            ->assertForbidden();
    }

    public function test_doctor_assistant_seeder_is_idempotent_and_creates_doctor_profiles(): void
    {
        $this->seed(DoctorAssistantSeeder::class);
        $this->seed(DoctorAssistantSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('doctors', 3);

        $this->assertDatabaseHas('users', [
            'email' => 'dokter2@temurawat.test',
            'role' => User::ROLE_DOKTER,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('doctors', [
            'nama' => 'dr. Dokter Ketiga',
            'status' => Doctor::STATUS_AKTIF,
        ]);
    }
}
