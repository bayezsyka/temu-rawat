<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Services\QueueService;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function __construct(protected QueueService $queueService)
    {
    }

    public function show(string $kode): Response
    {
        $queue = Queue::query()
            ->with(['patient', 'practiceSession', 'initialCheck'])
            ->where('kode_antrian', $kode)
            ->firstOrFail();

        return Inertia::render('Queue/Show', [
            'queue' => $this->queueService->serializePatientQueue($queue),
            'session' => $this->queueService->serializeSessionOverview($queue->practiceSession),
            'remainingBefore' => $this->queueService->remainingBefore($queue),
            'statusMessage' => $this->queueService->patientStatusMessage($queue),
        ]);
    }

    public function display(): Response
    {
        return Inertia::render('Display/Index', [
            'session' => $this->queueService->serializeSessionOverview(
                $this->queueService->getTodaySession(),
            ),
        ]);
    }
}
