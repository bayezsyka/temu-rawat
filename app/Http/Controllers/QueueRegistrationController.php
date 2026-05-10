<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRegistrationRequest;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\PracticeSession;
use App\Services\PracticeSessionService;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueRegistrationController extends Controller
{
    public function __construct(
        protected QueueService $queueService,
        protected PracticeSessionService $practiceSessionService,
    ) {
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $account = $this->resolveAccount($request);

        if (! $account) {
            return to_route('patient.login');
        }

        $profiles = $account->patients()->orderBy('nama')->get();

        if ($profiles->isEmpty()) {
            return to_route('patient.profile.index', ['create' => 1])
                ->with('error', 'Buat profil pasien terlebih dahulu.');
        }

        $selectedPatient = $profiles->firstWhere('id', (int) $request->session()->get('selected_patient_id')) ?: $profiles->first();
        $sessions = $this->practiceSessionService->serializeSessionCollection($this->practiceSessionService->getSelectableSessions());

        return Inertia::render('TemuRawat/Patient/QueueRegistration', [
            'account' => [
                'nomor_whatsapp' => $account->nomor_whatsapp,
            ],
            'profiles' => $profiles->map(fn (Patient $patient) => [
                'id' => $patient->id,
                'nama' => $patient->nama,
                'usia' => $patient->usia,
                'jenis_kelamin' => $patient->jenis_kelamin,
                'hubungan' => $patient->hubungan,
                'masked_nik' => $patient->nik ? substr($patient->nik, 0, 4).str_repeat('*', max(strlen($patient->nik) - 8, 4)).substr($patient->nik, -4) : null,
            ])->values()->all(),
            'selectedPatientId' => $selectedPatient?->id,
            'sessions' => $sessions,
        ]);
    }

    public function store(StorePatientRegistrationRequest $request): RedirectResponse
    {
        $account = $this->resolveAccount($request);

        if (! $account) {
            return to_route('patient.login');
        }

        $validated = $request->validated();
        $patient = $account->patients()->findOrFail($validated['patient_id']);
        $session = PracticeSession::query()->findOrFail($validated['practice_session_id']);

        $queue = $this->queueService->registerPatient($patient, $session, $validated);

        $request->session()->put('selected_patient_id', $patient->id);

        return to_route('queues.show', ['queue' => $queue->public_code])
            ->with('success', "Pendaftaran berhasil. Nomor antrian {$queue->kode_antrian}.");
    }

    protected function resolveAccount(Request $request): ?PatientAccount
    {
        $accountId = $request->session()->get('patient_account_id');

        return $accountId ? PatientAccount::query()->find($accountId) : null;
    }
}
