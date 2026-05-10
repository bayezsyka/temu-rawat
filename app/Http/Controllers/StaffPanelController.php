<?php

namespace App\Http\Controllers;

use App\Models\PracticeSession;
use App\Services\PracticeSessionService;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StaffPanelController extends Controller
{
    public function __construct(
        protected PracticeSessionService $practiceSessionService,
        protected QueueService $queueService,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->renderPanel($request);
    }

    public function show(Request $request, PracticeSession $session): Response
    {
        return $this->renderPanel($request, $session);
    }

    protected function renderPanel(Request $request, ?PracticeSession $selected = null): Response
    {
        $viewer = $request->user();
        $sessions = $this->practiceSessionService->getTodaySessions($viewer);
        $activeSession = $this->practiceSessionService->findSessionForPanel($viewer, $selected);
        $sessionCards = $this->practiceSessionService->serializeSessionCollection($sessions);

        return Inertia::render('TemuRawat/Staff/Panel', [
            'sessions' => $sessionCards,
            'activeSessionId' => $activeSession?->id,
            'queues' => $activeSession ? $this->queueService->serializePanelQueues($activeSession) : [],
            'viewer' => [
                'id' => $viewer->id,
                'role' => $viewer->role,
                'doctor_id' => $viewer->doctor?->id,
            ],
        ]);
    }
}
