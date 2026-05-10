<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertPracticeSessionRequest;
use App\Services\QueueService;
use Inertia\Inertia;
use Inertia\Response;

class PracticeSessionController extends Controller
{
    public function __construct(protected QueueService $queueService)
    {
    }

    public function index(): Response
    {
        return Inertia::render('Admin/Sessions', [
            'session' => $this->queueService->serializeSessionOverview(
                $this->queueService->getTodaySession(),
            ),
        ]);
    }

    public function upsert(UpsertPracticeSessionRequest $request)
    {
        $this->queueService->upsertTodaySession($request->validated());

        return back(303)->with('success', 'Sesi praktik hari ini berhasil diperbarui.');
    }
}
