<?php

namespace App\Services;

use App\Events\MedicalVisitUpdated;
use App\Models\MedicalVisit;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MedicalVisitService
{
    public function __construct(
        protected PracticeSessionService $practiceSessionService,
        protected QueueService $queueService,
    ) {
    }

    public function save(User $actor, string $reference, array $data): MedicalVisit
    {
        return DB::transaction(function () use ($actor, $reference, $data) {
            $queue = $this->resolveQueueFromReference($reference);

            $this->practiceSessionService->authorizeSessionAccess($actor, $queue->practiceSession);

            $visit = MedicalVisit::query()->updateOrCreate(
                ['queue_id' => $queue->id],
                [
                    'patient_id' => $queue->patient_id,
                    'doctor_id' => $queue->practiceSession->doctor_id,
                    'keluhan_utama' => $data['keluhan_utama'] ?? null,
                    'ringkasan_pemeriksaan' => $data['ringkasan_pemeriksaan'] ?? null,
                    'diagnosis' => $data['diagnosis'] ?? null,
                    'tindakan' => $data['tindakan'] ?? null,
                    'catatan_dokter' => $data['catatan_dokter'] ?? null,
                    'anjuran' => $data['anjuran'] ?? null,
                    'kontrol_ulang_pada' => $data['kontrol_ulang_pada'] ?? null,
                ],
            );

            if (in_array($queue->status, [Queue::STATUS_MENUNGGU, Queue::STATUS_DIPANGGIL, Queue::STATUS_PEMERIKSAAN_AWAL], true)) {
                $queue->status = Queue::STATUS_DIPERIKSA;
                $queue->waktu_dipanggil ??= now();
                $queue->waktu_mulai_awal ??= now();
                $queue->waktu_mulai_periksa ??= now();
                $queue->save();
            }

            $visit->loadMissing('queue.practiceSession');

            event(new MedicalVisitUpdated($visit));

            return $visit;
        });
    }

    public function finalize(User $actor, Queue $queue): MedicalVisit
    {
        return DB::transaction(function () use ($actor, $queue) {
            $queue = Queue::query()
                ->with(['practiceSession.doctor', 'medicalVisit'])
                ->lockForUpdate()
                ->findOrFail($queue->id);

            $this->practiceSessionService->authorizeSessionAccess($actor, $queue->practiceSession);

            $visit = MedicalVisit::query()->firstOrCreate(
                ['queue_id' => $queue->id],
                [
                    'patient_id' => $queue->patient_id,
                    'doctor_id' => $queue->practiceSession->doctor_id,
                ],
            );

            $visit->forceFill([
                'patient_visible_until' => now()->addDays(7),
                'finalized_at' => now(),
                'finalized_by' => $actor->id,
            ])->save();

            $queue->forceFill([
                'status' => Queue::STATUS_SELESAI,
                'waktu_dipanggil' => $queue->waktu_dipanggil ?: now(),
                'waktu_mulai_awal' => $queue->waktu_mulai_awal ?: now(),
                'waktu_mulai_periksa' => $queue->waktu_mulai_periksa ?: now(),
                'waktu_selesai' => now(),
            ])->save();

            $visit->loadMissing('queue.practiceSession');

            event(new MedicalVisitUpdated($visit));
            $this->queueService->markCompleted($queue);

            return $visit;
        });
    }

    public function resolveQueueFromReference(string $reference): Queue
    {
        if (str_starts_with($reference, 'queue-')) {
            $queueId = (int) ((string) str($reference)->after('queue-'));

            return Queue::query()
                ->with(['practiceSession.doctor'])
                ->findOrFail($queueId);
        }

        $visit = MedicalVisit::query()
            ->with('queue.practiceSession.doctor')
            ->findOrFail((int) $reference);

        return $visit->queue;
    }

    public function resolveVisitReference(string $reference): MedicalVisit
    {
        if (str_starts_with($reference, 'queue-')) {
            $queue = $this->resolveQueueFromReference($reference);

            return MedicalVisit::query()->firstOrCreate([
                'queue_id' => $queue->id,
            ], [
                'patient_id' => $queue->patient_id,
                'doctor_id' => $queue->practiceSession->doctor_id,
            ]);
        }

        return MedicalVisit::query()->findOrFail((int) $reference);
    }

    public function patientCanViewSummary(MedicalVisit $visit, int $patientAccountId): bool
    {
        return $visit->patient?->patient_account_id === $patientAccountId
            && $visit->patient_visible_until
            && now()->lte($visit->patient_visible_until);
    }
}
