<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\PracticeSession;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemuRawatFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_registration_creates_a_queue_for_today_session(): void
    {
        $doctorUser = User::factory()->create([
            'role' => User::ROLE_DOKTER,
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'nama' => 'dr. Tes Utama',
            'status' => Doctor::STATUS_AKTIF,
        ]);

        $account = PatientAccount::create([
            'nomor_whatsapp' => '08123456789',
            'verified_at' => now(),
        ]);

        $patient = Patient::create([
            'patient_account_id' => $account->id,
            'nama' => 'Siti Aminah',
            'nomor_whatsapp' => '08123456789',
            'usia' => 29,
            'jenis_kelamin' => 'perempuan',
            'alamat' => 'Makassar',
        ]);

        PracticeSession::create([
            'doctor_id' => $doctor->id,
            'tanggal' => today(),
            'status' => PracticeSession::STATUS_BUKA,
            'nomor_terakhir' => 0,
        ]);

        $session = PracticeSession::query()->firstOrFail();

        $response = $this
            ->withSession([
                'patient_account_id' => $account->id,
                'selected_patient_id' => $patient->id,
            ])
            ->post('/daftar', [
            'patient_id' => $patient->id,
            'practice_session_id' => $session->id,
            'keluhan' => 'Demam dan batuk',
            'status_kunjungan' => 'baru',
            'metode_daftar' => 'online',
        ]);

        $queue = Queue::query()->firstOrFail();

        $response->assertRedirect("/antrian/{$queue->public_code}");

        $this->assertDatabaseHas('queues', [
            'kode_antrian' => 'A-001',
            'status' => Queue::STATUS_MENUNGGU,
            'metode_daftar' => Queue::METODE_ONLINE,
        ]);

        $this->assertDatabaseHas('practice_sessions', [
            'nomor_terakhir' => 1,
        ]);

        $this->assertSame(
            today()->toDateString(),
            PracticeSession::query()->firstOrFail()->tanggal->toDateString(),
        );
    }

    public function test_staff_can_update_queue_status_and_initial_check(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $doctor = Doctor::create([
            'nama' => 'dr. Panel',
            'status' => Doctor::STATUS_AKTIF,
        ]);

        $session = PracticeSession::create([
            'doctor_id' => $doctor->id,
            'tanggal' => today(),
            'status' => PracticeSession::STATUS_BUKA,
            'nomor_terakhir' => 1,
        ]);

        $patient = Patient::create([
            'nama' => 'Budi',
            'nomor_whatsapp' => '089900112233',
        ]);

        $queue = Queue::create([
            'patient_id' => $patient->id,
            'practice_session_id' => $session->id,
            'public_code' => 'test-public-code',
            'kode_antrian' => 'A-001',
            'nomor_urut' => 1,
            'keluhan' => 'Pusing',
            'status_kunjungan' => Queue::STATUS_KUNJUNGAN_LAMA,
            'metode_daftar' => Queue::METODE_LANGSUNG,
            'status' => Queue::STATUS_MENUNGGU,
            'waktu_daftar' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('panel.queues.call', $queue))
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('panel.queues.initial', $queue), [
                'tekanan_darah' => '120/80',
                'suhu' => 37.2,
                'catatan_asisten' => 'Pasien tampak lelah',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_PEMERIKSAAN_AWAL,
        ]);

        $this->assertDatabaseHas('initial_checks', [
            'queue_id' => $queue->id,
            'tekanan_darah' => '120/80',
            'catatan_asisten' => 'Pasien tampak lelah',
        ]);
    }
}
