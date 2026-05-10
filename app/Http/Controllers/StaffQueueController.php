<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInitialCheckRequest;
use App\Http\Requests\UpdateQueueStatusRequest;
use App\Models\Queue;
use App\Services\QueueService;
use Inertia\Inertia;
use Inertia\Response;

class StaffQueueController extends Controller
{
    public function __construct(protected QueueService $queueService)
    {
    }

    public function index(): Response
    {
        $session = $this->queueService->getTodaySession();

        return Inertia::render('Panel/Index', [
            'session' => $this->queueService->serializeSessionOverview($session),
            'queues' => $this->queueService->serializePanelQueues($session),
        ]);
    }

    public function updateStatus(UpdateQueueStatusRequest $request, Queue $queue)
    {
        $this->queueService->updateQueueStatus($queue, $request->validated('action'));

        return back(303)->with('success', 'Status antrian berhasil diperbarui.');
    }

    public function updateInitialCheck(UpdateInitialCheckRequest $request, Queue $queue)
    {
        $this->queueService->updateInitialCheck($queue, $request->validated());

        return back(303)->with('success', 'Pemeriksaan awal berhasil disimpan.');
    }
}
