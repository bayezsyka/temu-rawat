<?php

namespace App\Services;

use App\Events\InitialCheckUpdated;
use App\Events\PracticeSessionUpdated;
use App\Events\QueueCalled;
use App\Events\QueueCompleted;
use App\Events\QueueCreated;
use App\Events\QueueSkipped;
use App\Events\QueueUpdated;
use App\Models\InitialCheck;
use App\Models\Patient;
use App\Models\PracticeSession;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QueueService
{
    public function __construct(
        protected PracticeSessionService $practiceSessionService,
    ) {
    }

    public function registerPatient(Patient $patient, PracticeSession $session, array $data): Queue
    {
        return DB::transaction(function () use ($patient, $session, $data) {
            $session = PracticeSession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->with('doctor')
                ->firstOrFail();

            if ($session->status !== PracticeSession::STATUS_BUKA) {
                throw ValidationException::withMessages([
                    'practice_session_id' => 'Sesi praktik yang dipilih tidak sedang dibuka.',
                ]);
            }

            $nomorUrut = $session->nomor_terakhir + 1;

            $queue = Queue::create([
                'patient_id' => $patient->id,
                'practice_session_id' => $session->id,
                'public_code' => (string) Str::ulid(),
                'kode_antrian' => $this->formatQueueCode($session, $nomorUrut),
                'nomor_urut' => $nomorUrut,
                'keluhan' => $data['keluhan'] ?? null,
                'status_kunjungan' => $data['status_kunjungan'],
                'metode_daftar' => $data['metode_daftar'],
                'status' => Queue::STATUS_MENUNGGU,
                'waktu_daftar' => now(),
            ]);

            $session->update([
                'nomor_terakhir' => $nomorUrut,
                'mulai_pada' => $session->mulai_pada ?: now(),
            ]);

            $queue->loadMissing($this->queueRelations());
            $session->loadMissing('doctor');

            event(new QueueCreated($queue));
            event(new PracticeSessionUpdated($session));

            return $queue;
        });
    }

    public function updateStatus(User $actor, Queue $queue, string $action): Queue
    {
        return DB::transaction(function () use ($actor, $queue, $action) {
            $queue = Queue::query()
                ->with(['practiceSession.doctor', 'patient'])
                ->lockForUpdate()
                ->findOrFail($queue->id);

            $this->practiceSessionService->authorizeSessionAccess($actor, $queue->practiceSession);

            $now = now();

            match ($action) {
                'panggil' => $this->callQueue($queue, $now),
                'mulai_awal' => $this->startInitialCheck($queue, $now),
                'mulai_periksa' => $this->startDoctorCheck($queue, $now),
                'lewati' => $this->skipQueue($queue),
                'batal' => $this->cancelQueue($queue, $now),
                default => throw ValidationException::withMessages([
                    'action' => 'Aksi antrian tidak dikenali.',
                ]),
            };

            $queue->save();
            $queue->refresh()->loadMissing($this->queueRelations());
            $queue->practiceSession->refresh();

            match ($action) {
                'panggil' => event(new QueueCalled($queue)),
                'lewati' => event(new QueueSkipped($queue)),
                default => event(new QueueUpdated($queue)),
            };

            event(new PracticeSessionUpdated($queue->practiceSession));

            return $queue;
        });
    }

    public function saveInitialCheck(User $actor, Queue $queue, array $data): InitialCheck
    {
        return DB::transaction(function () use ($actor, $queue, $data) {
            $queue = Queue::query()
                ->with('practiceSession.doctor')
                ->lockForUpdate()
                ->findOrFail($queue->id);

            $this->practiceSessionService->authorizeSessionAccess($actor, $queue->practiceSession);

            $initialCheck = InitialCheck::query()->updateOrCreate(
                ['queue_id' => $queue->id],
                $data + ['checked_by' => $actor->id],
            );

            if (in_array($queue->status, [Queue::STATUS_MENUNGGU, Queue::STATUS_DIPANGGIL], true)) {
                $queue->status = Queue::STATUS_PEMERIKSAAN_AWAL;
                $queue->waktu_dipanggil ??= now();
                $queue->waktu_mulai_awal ??= now();
                $queue->save();
            }

            $queue->refresh()->loadMissing($this->queueRelations());
            $queue->practiceSession->refresh();

            event(new InitialCheckUpdated($queue));
            event(new QueueUpdated($queue));
            event(new PracticeSessionUpdated($queue->practiceSession));

            return $initialCheck;
        });
    }

    public function markCompleted(Queue $queue): Queue
    {
        $queue->refresh()->loadMissing($this->queueRelations());
        $queue->practiceSession->refresh();

        event(new QueueCompleted($queue));
        event(new PracticeSessionUpdated($queue->practiceSession));

        return $queue;
    }

    public function remainingBefore(Queue $queue): int
    {
        return Queue::query()
            ->where('practice_session_id', $queue->practice_session_id)
            ->where('nomor_urut', '<', $queue->nomor_urut)
            ->whereIn('status', Queue::STATUS_AKTIF)
            ->count();
    }

    public function serializePatientQueue(Queue $queue): array
    {
        $queue->loadMissing($this->queueRelations());

        return [
            'id' => $queue->id,
            'public_code' => $queue->public_code,
            'kode_antrian' => $queue->kode_antrian,
            'status' => $queue->status,
            'status_kunjungan' => $queue->status_kunjungan,
            'metode_daftar' => $queue->metode_daftar,
            'keluhan' => $queue->keluhan,
            'waktu_daftar' => $queue->waktu_daftar?->toDateTimeString(),
            'patient' => [
                'nama' => $queue->patient->nama,
                'usia' => $queue->patient->usia,
                'jenis_kelamin' => $queue->patient->jenis_kelamin,
            ],
            'doctor' => [
                'nama' => $queue->practiceSession->doctor?->nama,
                'spesialisasi' => $queue->practiceSession->doctor?->spesialisasi,
            ],
            'initial_check' => $queue->initialCheck ? $this->serializeInitialCheck($queue->initialCheck) : null,
            'medical_visit' => $queue->medicalVisit ? [
                'id' => $queue->medicalVisit->id,
                'patient_visible_until' => $queue->medicalVisit->patient_visible_until?->toIso8601String(),
                'summary_url' => $queue->medicalVisit->patient_visible_until
                    ? route('patient.visits.summary', $queue->medicalVisit)
                    : null,
            ] : null,
        ];
    }

    public function serializeStaffQueue(Queue $queue): array
    {
        $queue->loadMissing($this->queueRelations());

        return [
            'id' => $queue->id,
            'public_code' => $queue->public_code,
            'kode_antrian' => $queue->kode_antrian,
            'nomor_urut' => $queue->nomor_urut,
            'status' => $queue->status,
            'keluhan' => $queue->keluhan,
            'status_kunjungan' => $queue->status_kunjungan,
            'metode_daftar' => $queue->metode_daftar,
            'waktu_daftar' => $queue->waktu_daftar?->toIso8601String(),
            'patient' => [
                'id' => $queue->patient->id,
                'nama' => $queue->patient->nama,
                'nik' => $queue->patient->nik,
                'usia' => $queue->patient->usia,
                'jenis_kelamin' => $queue->patient->jenis_kelamin,
                'alamat' => $queue->patient->alamat,
                'hubungan' => $queue->patient->hubungan,
                'tanggal_lahir' => $queue->patient->tanggal_lahir?->format('Y-m-d'),
                'nomor_whatsapp' => $queue->patient->account?->nomor_whatsapp ?: $queue->patient->nomor_whatsapp,
            ],
            'doctor' => [
                'id' => $queue->practiceSession->doctor?->id,
                'nama' => $queue->practiceSession->doctor?->nama,
                'spesialisasi' => $queue->practiceSession->doctor?->spesialisasi,
            ],
            'initial_check' => $queue->initialCheck ? $this->serializeInitialCheck($queue->initialCheck) : null,
            'medical_visit' => $queue->medicalVisit ? [
                'id' => $queue->medicalVisit->id,
                'keluhan_utama' => $queue->medicalVisit->keluhan_utama,
                'ringkasan_pemeriksaan' => $queue->medicalVisit->ringkasan_pemeriksaan,
                'diagnosis' => $queue->medicalVisit->diagnosis,
                'tindakan' => $queue->medicalVisit->tindakan,
                'catatan_dokter' => $queue->medicalVisit->catatan_dokter,
                'anjuran' => $queue->medicalVisit->anjuran,
                'kontrol_ulang_pada' => $queue->medicalVisit->kontrol_ulang_pada?->format('Y-m-d'),
                'finalized_at' => $queue->medicalVisit->finalized_at?->toIso8601String(),
                'patient_visible_until' => $queue->medicalVisit->patient_visible_until?->toIso8601String(),
                'reference' => (string) $queue->medicalVisit->id,
            ] : [
                'reference' => "queue-{$queue->id}",
            ],
            'prescription' => $queue->medicalVisit?->prescription ? [
                'id' => $queue->medicalVisit->prescription->id,
                'catatan_resep' => $queue->medicalVisit->prescription->catatan_resep,
                'items' => $queue->medicalVisit->prescription->items->map(fn ($item) => [
                    'id' => $item->id,
                    'nama_obat' => $item->nama_obat,
                    'dosis' => $item->dosis,
                    'aturan_pakai' => $item->aturan_pakai,
                    'jumlah' => $item->jumlah,
                    'satuan' => $item->satuan,
                    'catatan' => $item->catatan,
                ])->values()->all(),
            ] : [
                'items' => [],
            ],
            'patient_history' => $queue->patient->medicalVisits
                ->where('id', '!=', $queue->medicalVisit?->id)
                ->sortByDesc('created_at')
                ->take(5)
                ->map(fn ($visit) => [
                    'id' => $visit->id,
                    'tanggal' => $visit->created_at?->format('d M Y'),
                    'doctor' => $visit->doctor?->nama,
                    'diagnosis' => $visit->diagnosis,
                    'prescription' => $visit->prescription?->items
                        ? $visit->prescription->items->pluck('nama_obat')->filter()->take(3)->join(', ')
                        : null,
                    'anjuran' => $visit->anjuran,
                    'summary_url' => route('patient.visits.summary', $visit),
                ])
                ->values()
                ->all(),
        ];
    }

    public function serializePanelQueues(PracticeSession $session): array
    {
        return $session->queues()
            ->with($this->queueRelations())
            ->orderBy('nomor_urut')
            ->get()
            ->map(fn (Queue $queue) => $this->serializeStaffQueue($queue))
            ->all();
    }

    public function queueRelations(): array
    {
        return [
            'patient.account',
            'patient.medicalVisits.doctor',
            'patient.medicalVisits.prescription.items',
            'practiceSession.doctor',
            'initialCheck.checkedBy',
            'medicalVisit.prescription.items',
        ];
    }

    public function patientStatusMessage(Queue $queue): string
    {
        return match ($queue->status) {
            Queue::STATUS_MENUNGGU => 'Silakan menunggu. Status antrian akan bergerak sesuai dokter yang dipilih.',
            Queue::STATUS_DIPANGGIL => 'Nomor Anda sedang dipanggil. Silakan menuju area pemeriksaan.',
            Queue::STATUS_PEMERIKSAAN_AWAL => 'Pemeriksaan awal sedang berjalan bersama asisten.',
            Queue::STATUS_DIPERIKSA => 'Pasien sedang diperiksa dokter.',
            Queue::STATUS_SELESAI => 'Kunjungan selesai. Ringkasan hasil tersedia selama 7 hari.',
            Queue::STATUS_DILEWATI => 'Antrian sempat dilewati. Silakan hubungi petugas bila sudah siap.',
            Queue::STATUS_BATAL => 'Antrian dibatalkan.',
            default => 'Status antrian sedang diperbarui.',
        };
    }

    protected function formatQueueCode(PracticeSession $session, int $number): string
    {
        $prefixes = range('A', 'Z');
        $sessionIds = PracticeSession::query()
            ->whereDate('tanggal', $session->tanggal)
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id')
            ->values();

        $index = max($sessionIds->search($session->id), 0);
        $prefix = $prefixes[$index] ?? 'Z';

        return sprintf('%s-%03d', $prefix, $number);
    }

    protected function serializeInitialCheck(InitialCheck $initialCheck): array
    {
        return [
            'tekanan_darah' => $initialCheck->tekanan_darah,
            'berat_badan' => $initialCheck->berat_badan,
            'tinggi_badan' => $initialCheck->tinggi_badan,
            'suhu' => $initialCheck->suhu,
            'nadi' => $initialCheck->nadi,
            'saturasi_oksigen' => $initialCheck->saturasi_oksigen,
            'catatan_asisten' => $initialCheck->catatan_asisten,
            'checked_by' => $initialCheck->checkedBy?->name,
        ];
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

    protected function startInitialCheck(Queue $queue, Carbon $now): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_MENUNGGU,
            Queue::STATUS_DIPANGGIL,
            Queue::STATUS_DILEWATI,
        ]);

        $queue->status = Queue::STATUS_PEMERIKSAAN_AWAL;
        $queue->waktu_dipanggil ??= $now;
        $queue->waktu_mulai_awal ??= $now;
    }

    protected function startDoctorCheck(Queue $queue, Carbon $now): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_MENUNGGU,
            Queue::STATUS_DIPANGGIL,
            Queue::STATUS_PEMERIKSAAN_AWAL,
            Queue::STATUS_DILEWATI,
        ]);

        $queue->status = Queue::STATUS_DIPERIKSA;
        $queue->waktu_dipanggil ??= $now;
        $queue->waktu_mulai_awal ??= $now;
        $queue->waktu_mulai_periksa ??= $now;
    }

    protected function skipQueue(Queue $queue): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_MENUNGGU,
            Queue::STATUS_DIPANGGIL,
            Queue::STATUS_PEMERIKSAAN_AWAL,
        ]);

        $queue->status = Queue::STATUS_DILEWATI;
    }

    protected function cancelQueue(Queue $queue, Carbon $now): void
    {
        $this->ensureCurrentStatus($queue, [
            Queue::STATUS_MENUNGGU,
            Queue::STATUS_DIPANGGIL,
            Queue::STATUS_PEMERIKSAAN_AWAL,
            Queue::STATUS_DILEWATI,
        ]);

        $queue->status = Queue::STATUS_BATAL;
        $queue->waktu_selesai = $now;
    }

    protected function ensureCurrentStatus(Queue $queue, array $allowedStatuses): void
    {
        if (! in_array($queue->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'queue' => 'Perubahan status tidak valid untuk antrian ini.',
            ]);
        }
    }
}
