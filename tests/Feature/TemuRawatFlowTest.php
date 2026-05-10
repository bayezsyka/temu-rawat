<?php

namespace Tests\Feature;

use App\Models\Patient;
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
        PracticeSession::create([
            'tanggal' => today(),
            'status' => PracticeSession::STATUS_BUKA,
            'nomor_terakhir' => 0,
        ]);

        $response = $this->post('/daftar', [
            'nama' => 'Siti Aminah',
            'nomor_whatsapp' => '08123456789',
            'usia' => 29,
            'jenis_kelamin' => 'perempuan',
            'alamat' => 'Makassar',
            'keluhan' => 'Demam dan batuk',
            'status_kunjungan' => 'baru',
            'metode_daftar' => 'online',
        ]);

        $response->assertRedirect('/antrian/A-001');

        $this->assertDatabaseHas('patients', [
            'nama' => 'Siti Aminah',
            'nomor_whatsapp' => '08123456789',
        ]);

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

        $session = PracticeSession::create([
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
            'kode_antrian' => 'A-001',
            'nomor_urut' => 1,
            'keluhan' => 'Pusing',
            'status_kunjungan' => Queue::STATUS_KUNJUNGAN_LAMA,
            'metode_daftar' => Queue::METODE_LANGSUNG,
            'status' => Queue::STATUS_MENUNGGU,
            'waktu_daftar' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('panel.queues.status', $queue), [
                'action' => 'panggil',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->patch(route('panel.queues.initial-check', $queue), [
                'tekanan_darah' => '120/80',
                'suhu' => 37.2,
                'catatan_asisten' => 'Pasien tampak lelah',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => Queue::STATUS_DIPANGGIL,
        ]);

        $this->assertDatabaseHas('initial_checks', [
            'queue_id' => $queue->id,
            'tekanan_darah' => '120/80',
            'catatan_asisten' => 'Pasien tampak lelah',
        ]);
    }
}
