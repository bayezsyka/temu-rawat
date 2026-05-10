<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInitialCheckRequest;
use App\Models\Queue;
use App\Services\MedicalVisitService;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffQueueActionController extends Controller
{
    public function __construct(
        protected QueueService $queueService,
        protected MedicalVisitService $medicalVisitService,
    ) {
    }

    public function call(Request $request, Queue $queue): RedirectResponse
    {
        $this->queueService->updateStatus($request->user(), $queue, 'panggil');

        return back()->with('success', 'Pasien berhasil dipanggil.');
    }

    public function initial(UpdateInitialCheckRequest $request, Queue $queue): RedirectResponse
    {
        $validated = $request->validated();
        $hasMedicalData = collect($validated)->filter(fn ($value) => filled($value))->isNotEmpty();

        if ($hasMedicalData) {
            $this->queueService->saveInitialCheck($request->user(), $queue, $validated);

            return back()->with('success', 'Pemeriksaan awal berhasil disimpan.');
        }

        $this->queueService->updateStatus($request->user(), $queue, 'mulai_awal');

        return back()->with('success', 'Pemeriksaan awal dimulai.');
    }

    public function startDoctorCheck(Request $request, Queue $queue): RedirectResponse
    {
        $this->queueService->updateStatus($request->user(), $queue, 'mulai_periksa');

        return back()->with('success', 'Pemeriksaan dokter dimulai.');
    }

    public function skip(Request $request, Queue $queue): RedirectResponse
    {
        $this->queueService->updateStatus($request->user(), $queue, 'lewati');

        return back()->with('success', 'Antrian berhasil dilewati.');
    }

    public function cancel(Request $request, Queue $queue): RedirectResponse
    {
        $this->queueService->updateStatus($request->user(), $queue, 'batal');

        return back()->with('success', 'Antrian berhasil dibatalkan.');
    }

    public function finish(Request $request, Queue $queue): RedirectResponse
    {
        $this->medicalVisitService->finalize($request->user(), $queue);

        return back()->with('success', 'Pemeriksaan berhasil diselesaikan.');
    }
}
