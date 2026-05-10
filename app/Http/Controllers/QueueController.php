<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Services\PracticeSessionService;
use App\Services\QueueService;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function __construct(
        protected QueueService $queueService,
        protected PracticeSessionService $practiceSessionService,
    ) {
    }

    public function show(Queue $queue): Response
    {
        $queue->loadMissing($this->queueService->queueRelations());

        return Inertia::render('Queue/Show', [
            'queue' => $this->queueService->serializePatientQueue($queue),
            'session' => $this->practiceSessionService->serializeSessionCard($queue->practiceSession),
            'remainingBefore' => $this->queueService->remainingBefore($queue),
            'statusMessage' => $this->queueService->patientStatusMessage($queue),
        ]);
    }
}
