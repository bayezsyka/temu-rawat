<?php

namespace App\Services;

use App\Events\PracticeSessionUpdated;
use App\Events\QueueCalled;
use App\Events\QueueCompleted;
use App\Events\QueueCreated;
use App\Events\QueueUpdated;
use App\Models\InitialCheck;
use App\Models\Patient;
use App\Models\PracticeSession;
use App\Models\Queue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QueueService
{
    public function getTodaySession(): ?PracticeSession
    {
        return PracticeSession::query()
            ->whereDate('tanggal', today())
            ->first();
    }

    public function registerPatient(array $data): Queue
    {
        return DB::transaction(function () use ($data) {
            $session = $this->requireActiveSession();

            $patient = Patient::create([
                'nama' => $data['nama'],
                'nomor_whatsapp' => $data['nomor_whatsapp'],
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'usia' => $data['usia'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'] ?? null,
                'alamat' => $data['alamat'] ?? null,
            ]);

            $nomorUrut = $session->nomor_terakhir + 1;

            $queue = Queue::create([
                'patient_id' => $patient->id,
                'practice_session_id' => $session->id,
                'kode_antrian' => $this->formatQueueCode($nomorUrut),
                'nomor_urut' => $nomorUrut,
                'keluhan' => $data['keluhan'],
                'status_kunjungan' => $data['status_kunjungan'],
                'metode_daftar' => $data['metode_daftar'],
                'status' => Queue::STATUS_MENUNGGU,
                'waktu_daftar' => now(),
            ]);

            $session->update([
                'nomor_terakhir' => $nomorUrut,
            ]);

            $queue->load(['patient', 'practiceSession', 'initialCheck']);
            $session->load('queues');

            event(new QueueCreated($queue));
            event(new PracticeSessionUpdated($session));

            return $queue;
        });
    }

    public function upsertTodaySession(array $data): PracticeSession
    {
        return DB::transaction(function () use ($data) {
            $session = $this->getTodaySession() ?: new PracticeSession([
                'tanggal' => today(),
            ]);

            $nomorAwal = (int) ($data['nomor_awal'] ?? max(($session->nomor_terakhir ?? 0) + 1, 1));
            $nomorTerakhir = max($nomorAwal - 1, $session->nomor_terakhir ?? 0);

            if (! $session->exists) {
                $session->nomor_terakhir = $nomorTerakhir;
            } elseif ($nomorTerakhir > $session->nomor_terakhir) {
                $session->nomor_terakhir = $nomorTerakhir;
            }

            $session->fill([
                'nama_dokter' => $data['nama_dokter'] ?? $session->nama_dokter,
                'status' => $data['status'],
            ]);

            $session->save();
            $session->load('queues');

            event(new PracticeSessionUpdated($session));

            return $session;
        });
    }

    public function updateQueueStatus(Queue $queue, string $action): Queue
    {
        return DB::transaction(function () use ($queue, $action) {
            $queue = Queue::query()
                ->with(['patient', 'practiceSession', 'initialCheck'])
                ->lockForUpdate()
                ->findOrFail($queue->id);

            $now = now();

            match ($action) {
                'panggil' => $this->callQueue($queue, $now),
                'mulai_periksa' => $this->startExamination($queue, $now),
                'selesai' => $this->completeQueue($queue, $now),
                'lewati' => $this->skipQueue($queue),
                'batalkan' => $this->cancelQueue($queue),
                default => throw ValidationException::withMessages([
                    'action' => 'Aksi antrian tidak dikenali.',
                ]),
            };

            $queue->save();
            $queue->refresh()->load(['patient', 'practiceSession', 'initialCheck']);
            $queue->practiceSession->load('queues');

            if ($action === 'panggil') {
                event(new QueueCalled($queue));
            } elseif ($action === 'selesai') {
                event(new QueueCompleted($queue));
            } else {
                event(new QueueUpdated($queue));
            }

            event(new PracticeSessionUpdated($queue->practiceSession));

            return $queue;
        });
    }

    public function updateInitialCheck(Queue $queue, array $data): InitialCheck
    {
        $queue->loadMissing('practiceSession');

        return DB::transaction(function () use ($queue, $data) {
            $initialCheck = InitialCheck::query()->updateOrCreate(
                ['queue_id' => $queue->id],
                $data,
            );

            $queue->refresh()->load(['patient', 'practiceSession', 'initialCheck']);

            event(new QueueUpdated($queue));
            event(new PracticeSessionUpdated($queue->practiceSession));

            return $initialCheck;
        });
    }

    public function serializeSessionOverview(?PracticeSession $session): ?array
    {
        if (! $session) {
            return null;
        }

        $session->loadMissing(['queues.patient']);

        $currentQueue = $this->getCurrentQueue($session);
        $upcomingQueues = $this->getUpcomingQueues($session, 5, $currentQueue?->id);

        return [
            'id' => $session->id,
            'tanggal' => $session->tanggal?->format('Y-m-d'),
            'nama_dokter' => $session->nama_dokter,
            'status' => $session->status,
            'nomor_terakhir' => $session->nomor_terakhir,
            'current_queue' => $currentQueue ? $this->serializeQueueSummary($currentQueue) : null,
            'upcoming_queues' => $upcomingQueues->map(fn (Queue $queue) => $this->serializeQueueSummary($queue))->values()->all(),
            'waiting_count' => $session->queues()
                ->whereIn('status', Queue::STATUS_AKTIF)
                ->count(),
        ];
    }

    public function serializePatientQueue(Queue $queue): array
    {
        $queue->loadMissing(['patient', 'practiceSession', 'initialCheck']);

        return [
            'id' => $queue->id,
            'kode_antrian' => $queue->kode_antrian,
            'nomor_urut' => $queue->nomor_urut,
            'status' => $queue->status,
            'status_kunjungan' => $queue->status_kunjungan,
            'metode_daftar' => $queue->metode_daftar,
            'keluhan' => $queue->keluhan,
            'waktu_daftar' => $queue->waktu_daftar?->toDateTimeString(),
            'patient' => [
                'nama' => $queue->patient->nama,
                'nomor_whatsapp' => $queue->patient->nomor_whatsapp,
            ],
            'initial_check' => $queue->initialCheck ? [
                'tekanan_darah' => $queue->initialCheck->tekanan_darah,
                'berat_badan' => $queue->initialCheck->berat_badan,
                'tinggi_badan' => $queue->initialCheck->tinggi_badan,
                'suhu' => $queue->initialCheck->suhu,
                'nadi' => $queue->initialCheck->nadi,
                'catatan_asisten' => $queue->initialCheck->catatan_asisten,
            ] : null,
        ];
    }

    public function serializePanelQueues(?PracticeSession $session): array
    {
        if (! $session) {
            return [];
        }

        return $session->queues()
            ->with(['patient', 'initialCheck'])
            ->orderBy('nomor_urut')
            ->get()
            ->map(function (Queue $queue) {
                return [
                    ...$this->serializeQueueSummary($queue),
                    'keluhan' => $queue->keluhan,
                    'status_kunjungan' => $queue->status_kunjungan,
                    'metode_daftar' => $queue->metode_daftar,
                    'patient' => [
                        'nama' => $queue->patient->nama,
                        'nomor_whatsapp' => $queue->patient->nomor_whatsapp,
                        'tanggal_lahir' => $queue->patient->tanggal_lahir?->format('Y-m-d'),
                        'usia' => $queue->patient->usia,
                        'jenis_kelamin' => $queue->patient->jenis_kelamin,
                        'alamat' => $queue->patient->alamat,
                    ],
                    'initial_check' => $queue->initialCheck ? [
                        'tekanan_darah' => $queue->initialCheck->tekanan_darah,
                        'berat_badan' => $queue->initialCheck->berat_badan,
                        'tinggi_badan' => $queue->initialCheck->tinggi_badan,
                        'suhu' => $queue->initialCheck->suhu,
                        'nadi' => $queue->initialCheck->nadi,
                        'catatan_asisten' => $queue->initialCheck->catatan_asisten,
                    ] : null,
                ];
            })
            ->values()
            ->all();
    }

    public function serializeQueueSummary(Queue $queue): array
    {
        $queue->loadMissing('patient');

        return [
            'id' => $queue->id,
            'kode_antrian' => $queue->kode_antrian,
            'nomor_urut' => $queue->nomor_urut,
            'status' => $queue->status,
            'nama_samaran' => $this->maskName($queue->patient?->nama),
        ];
    }

    public function remainingBefore(Queue $queue): int
    {
        return Queue::query()
            ->where('practice_session_id', $queue->practice_session_id)
            ->where('nomor_urut', '<', $queue->nomor_urut)
            ->whereIn('status', Queue::STATUS_AKTIF)
            ->count();
    }

    public function patientStatusMessage(Queue $queue): string
    {
        return match ($queue->status) {
            Queue::STATUS_MENUNGGU => 'Silakan menunggu. Nomor Anda akan dipanggil saat giliran tiba.',
            Queue::STATUS_DIPANGGIL => 'Nomor Anda sedang dipanggil. Silakan menuju ruang pemeriksaan.',
            Queue::STATUS_DIPERIKSA => 'Anda sedang dalam proses pemeriksaan.',
            Queue::STATUS_SELESAI => 'Kunjungan selesai. Terima kasih telah menggunakan Temu Rawat.',
            Queue::STATUS_DILEWATI => 'Antrian Anda sempat dilewati. Silakan hubungi petugas bila sudah siap.',
            Queue::STATUS_BATAL => 'Antrian Anda dibatalkan. Silakan daftar ulang bila diperlukan.',
            default => 'Status antrian Anda sedang diperbarui.',
        };
    }

    protected function requireActiveSession(): PracticeSession
    {
        $session = $this->getTodaySession();

        if (! $session || $session->status === PracticeSession::STATUS_SELESAI) {
            throw ValidationException::withMessages([
                'session' => 'Sesi praktik hari ini belum dibuka atau sudah selesai.',
            ]);
        }

        return $session;
    }

    protected function formatQueueCode(int $number): string
    {
        return sprintf('A-%03d', $number);
    }

    protected function getCurrentQueue(PracticeSession $session): ?Queue
    {
        return $session->queues()
            ->with('patient')
            ->whereIn('status', [Queue::STATUS_DIPANGGIL, Queue::STATUS_DIPERIKSA])
            ->orderByRaw("case when status = 'diperiksa' then 0 else 1 end")
            ->orderByDesc('updated_at')
            ->first();
    }

    protected function getUpcomingQueues(PracticeSession $session, int $limit = 5, ?int $excludeId = null)
    {
        return $session->queues()
            ->with('patient')
            ->where('status', Queue::STATUS_MENUNGGU)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->orderBy('nomor_urut')
            ->limit($limit)
            ->get();
    }

    protected function callQueue(Queue $queue, Carbon $now): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_MENUNGGU,
            Queue::STATUS_DILEWATI,
        ]);

        $queue->status = Queue::STATUS_DIPANGGIL;
        $queue->waktu_dipanggil ??= $now;
    }

    protected function startExamination(Queue $queue, Carbon $now): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_MENUNGGU,
            Queue::STATUS_DIPANGGIL,
        ]);

        $queue->status = Queue::STATUS_DIPERIKSA;
        $queue->waktu_dipanggil ??= $now;
        $queue->waktu_mulai_periksa ??= $now;
    }

    protected function completeQueue(Queue $queue, Carbon $now): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_DIPANGGIL,
            Queue::STATUS_DIPERIKSA,
        ]);

        $queue->status = Queue::STATUS_SELESAI;
        $queue->waktu_dipanggil ??= $now;
        $queue->waktu_mulai_periksa ??= $now;
        $queue->waktu_selesai = $now;
    }

    protected function skipQueue(Queue $queue): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_MENUNGGU,
            Queue::STATUS_DIPANGGIL,
        ]);

        $queue->status = Queue::STATUS_DILEWATI;
    }

    protected function cancelQueue(Queue $queue): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_MENUNGGU,
            Queue::STATUS_DIPANGGIL,
            Queue::STATUS_DILEWATI,
        ]);

        $queue->status = Queue::STATUS_BATAL;
    }

    protected function ensureCurrentStatus(Queue $queue, array $allowedStatuses): void
    {
        if (! in_array($queue->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'queue' => 'Perubahan status tidak valid untuk antrian ini.',
            ]);
        }
    }

    protected function maskName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        return mb_substr($name, 0, 1).'***';
    }
}
