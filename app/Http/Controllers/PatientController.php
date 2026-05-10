<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRegistrationRequest;
use App\Services\QueueService;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    public function __construct(protected QueueService $queueService)
    {
    }

    public function create(): Response
    {
        return Inertia::render('Registration/Create', [
            'session' => $this->queueService->serializeSessionOverview(
                $this->queueService->getTodaySession(),
            ),
        ]);
    }

    public function store(StorePatientRegistrationRequest $request)
    {
        $queue = $this->queueService->registerPatient($request->validated());

        return to_route('queues.show', $queue->kode_antrian)
            ->with('success', "Pendaftaran berhasil. Nomor antrian Anda {$queue->kode_antrian}.");
    }
}
