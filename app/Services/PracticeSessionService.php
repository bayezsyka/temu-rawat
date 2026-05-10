<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\PracticeSession;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PracticeSessionService
{
    public function getTodaySessions(?User $viewer = null): Collection
    {
        $query = PracticeSession::query()
            ->with(['doctor.user'])
            ->withCount([
                'queues as waiting_count' => fn ($queue) => $queue->whereIn('status', Queue::STATUS_AKTIF),
            ])
            ->whereDate('tanggal', today())
            ->orderByRaw("case when status = 'buka' then 0 when status = 'istirahat' then 1 else 2 end")
            ->orderBy('created_at')
            ->orderBy('id');

        if ($viewer?->isDokter()) {
            if (! $viewer->doctor?->id) {
                return new Collection();
            }

            $query->where('doctor_id', $viewer->doctor->id);
        }

        return $query->get();
    }

    public function getSelectableSessions(): Collection
    {
        return PracticeSession::query()
            ->with('doctor')
            ->withCount([
                'queues as waiting_count' => fn ($queue) => $queue->whereIn('status', Queue::STATUS_AKTIF),
            ])
            ->whereDate('tanggal', today())
            ->where('status', PracticeSession::STATUS_BUKA)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    public function findSessionForPanel(User $viewer, ?PracticeSession $session = null): ?PracticeSession
    {
        $sessions = $this->getTodaySessions($viewer);

        if (! $sessions->count()) {
            return null;
        }

        if ($session) {
            $this->authorizeSessionAccess($viewer, $session);

            return $sessions->firstWhere('id', $session->id) ?: $sessions->first();
        }

        return $sessions->first();
    }

    public function upsertTodaySession(array $data): PracticeSession
    {
        return DB::transaction(function () use ($data) {
            $session = PracticeSession::query()->firstOrNew([
                'doctor_id' => $data['doctor_id'],
                'tanggal' => $data['tanggal'] ?? today()->toDateString(),
            ]);

            $session->status = $data['status'];

            if ($data['status'] === PracticeSession::STATUS_BUKA) {
                $session->mulai_pada ??= now();
                $session->selesai_pada = null;
            } elseif ($data['status'] === PracticeSession::STATUS_SELESAI) {
                $session->mulai_pada ??= now();
                $session->selesai_pada = now();
            }

            $session->save();

            return $session->fresh(['doctor.user']);
        });
    }

    public function updateStatus(PracticeSession $session, string $status): PracticeSession
    {
        $session->status = $status;

        if ($status === PracticeSession::STATUS_BUKA) {
            $session->mulai_pada ??= now();
            $session->selesai_pada = null;
        }

        if ($status === PracticeSession::STATUS_SELESAI) {
            $session->selesai_pada = now();
        }

        $session->save();

        return $session->fresh(['doctor.user']);
    }

    public function serializeSessionCard(PracticeSession $session): array
    {
        $session->loadMissing([
            'doctor.user',
            'queues.patient',
        ]);

        $currentQueue = $session->queues
            ->whereIn('status', [Queue::STATUS_DIPANGGIL, Queue::STATUS_PEMERIKSAAN_AWAL, Queue::STATUS_DIPERIKSA])
            ->sortBy(fn ($queue) => match ($queue->status) {
                Queue::STATUS_DIPERIKSA => 0,
                Queue::STATUS_PEMERIKSAAN_AWAL => 1,
                default => 2,
            })
            ->first();

        $nextQueues = $session->queues
            ->where('status', Queue::STATUS_MENUNGGU)
            ->sortBy('nomor_urut')
            ->take(3)
            ->values();

        return [
            'id' => $session->id,
            'tanggal' => $session->tanggal?->format('Y-m-d'),
            'status' => $session->status,
            'nomor_terakhir' => $session->nomor_terakhir,
            'mulai_pada' => $session->mulai_pada?->toIso8601String(),
            'selesai_pada' => $session->selesai_pada?->toIso8601String(),
            'doctor' => [
                'id' => $session->doctor?->id,
                'nama' => $session->doctor?->nama,
                'spesialisasi' => $session->doctor?->spesialisasi,
                'status' => $session->doctor?->status,
            ],
            'waiting_count' => $session->queues->whereIn('status', Queue::STATUS_AKTIF)->count(),
            'current_queue' => $currentQueue ? [
                'id' => $currentQueue->id,
                'kode_antrian' => $currentQueue->kode_antrian,
                'status' => $currentQueue->status,
            ] : null,
            'next_queues' => $nextQueues->map(fn ($queue) => [
                'id' => $queue->id,
                'kode_antrian' => $queue->kode_antrian,
            ])->all(),
        ];
    }

    public function serializeSessionCollection(Collection $sessions): array
    {
        return $sessions->map(fn (PracticeSession $session) => $this->serializeSessionCard($session))->all();
    }

    public function serializeDoctorOptions(): array
    {
        return Doctor::query()
            ->with('user')
            ->where('status', Doctor::STATUS_AKTIF)
            ->orderBy('nama')
            ->get()
            ->map(fn (Doctor $doctor) => [
                'id' => $doctor->id,
                'nama' => $doctor->nama,
                'spesialisasi' => $doctor->spesialisasi,
                'nomor_sip' => $doctor->nomor_sip,
                'email' => $doctor->user?->email,
                'status' => $doctor->status,
            ])
            ->all();
    }

    public function authorizeSessionAccess(User $user, PracticeSession $session): void
    {
        if ($user->isDokter() && $session->doctor?->user_id !== $user->id) {
            abort(403);
        }
    }
}
