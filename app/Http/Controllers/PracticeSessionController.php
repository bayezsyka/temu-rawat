<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpsertPracticeSessionRequest;
use App\Events\PracticeSessionUpdated;
use App\Models\PracticeSession;
use App\Services\PracticeSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PracticeSessionController extends Controller
{
    public function __construct(protected PracticeSessionService $practiceSessionService) {}

    public function index(): Response
    {
        return Inertia::render('TemuRawat/Admin/PracticeSessions', [
            'sessions' => $this->practiceSessionService->serializeSessionCollection(
                $this->practiceSessionService->getTodaySessions(),
            ),
            'doctors' => $this->practiceSessionService->serializeDoctorOptions(),
        ]);
    }

    public function store(UpsertPracticeSessionRequest $request): RedirectResponse
    {
        $session = $this->practiceSessionService->upsertTodaySession($request->validated());
        event(new PracticeSessionUpdated($session));

        return back()->with('success', 'Sesi praktik berhasil dibuka atau diperbarui.');
    }

    public function update(Request $request, PracticeSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:buka,istirahat,selesai'],
        ]);

        $session = $this->practiceSessionService->updateStatus($session, $validated['status']);
        event(new PracticeSessionUpdated($session));

        return back()->with('success', 'Status sesi praktik berhasil diperbarui.');
    }
}
