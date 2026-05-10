<?php

namespace App\Services;

use App\Events\PrescriptionUpdated;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PrescriptionService
{
    public function __construct(
        protected MedicalVisitService $medicalVisitService,
        protected PracticeSessionService $practiceSessionService,
    ) {
    }

    public function save(User $actor, string $reference, array $data): Prescription
    {
        return DB::transaction(function () use ($actor, $reference, $data) {
            $visit = $this->medicalVisitService->resolveVisitReference($reference);
            $visit->loadMissing('queue.practiceSession.doctor');

            $this->practiceSessionService->authorizeSessionAccess($actor, $visit->queue->practiceSession);

            $prescription = Prescription::query()->updateOrCreate(
                ['medical_visit_id' => $visit->id],
                [
                    'catatan_resep' => $data['catatan_resep'] ?? null,
                    'created_by' => $actor->id,
                ],
            );

            $prescription->items()->delete();

            foreach ($data['items'] ?? [] as $item) {
                if (! filled($item['nama_obat'] ?? null)) {
                    continue;
                }

                $prescription->items()->create([
                    'nama_obat' => $item['nama_obat'],
                    'dosis' => $item['dosis'] ?? null,
                    'aturan_pakai' => $item['aturan_pakai'] ?? null,
                    'jumlah' => $item['jumlah'] ?? null,
                    'satuan' => $item['satuan'] ?? null,
                    'catatan' => $item['catatan'] ?? null,
                ]);
            }

            $prescription->load('visit.queue.practiceSession', 'items');

            event(new PrescriptionUpdated($prescription));

            return $prescription;
        });
    }
}
