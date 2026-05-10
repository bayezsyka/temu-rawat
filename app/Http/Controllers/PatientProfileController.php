<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientProfileRequest;
use App\Models\Patient;
use App\Models\PatientAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientProfileController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $account = $this->resolveAccount($request);

        if (! $account) {
            return to_route('patient.login');
        }

        $profiles = $account->patients()->latest()->get();

        $props = [
            'account' => [
                'id' => $account->id,
                'nomor_whatsapp' => $account->nomor_whatsapp,
            ],
            'profiles' => $profiles->map(fn (Patient $patient) => [
                'id' => $patient->id,
                'nama' => $patient->nama,
                'masked_nik' => $this->maskNik($patient->nik),
                'tanggal_lahir' => $patient->tanggal_lahir?->format('Y-m-d'),
                'usia' => $patient->usia,
                'jenis_kelamin' => $patient->jenis_kelamin,
                'alamat' => $patient->alamat,
                'hubungan' => $patient->hubungan,
                'selected' => (int) $request->session()->get('selected_patient_id') === $patient->id,
            ])->values()->all(),
        ];

        if ($request->boolean('create')) {
            return Inertia::render('TemuRawat/Patient/ProfileForm', $props);
        }

        return Inertia::render('TemuRawat/Patient/ProfileIndex', $props);
    }

    public function store(StorePatientProfileRequest $request): RedirectResponse
    {
        $account = $this->resolveAccount($request);

        if (! $account) {
            return to_route('patient.login');
        }

        $validated = $request->validated();

        if (! empty($validated['patient_id'])) {
            $patient = $account->patients()->findOrFail($validated['patient_id']);
            $request->session()->put('selected_patient_id', $patient->id);

            return to_route('registration.create')
                ->with('success', "Profil {$patient->nama} dipilih.");
        }

        $patient = $account->patients()->create([
            'nama' => $validated['nama'],
            'nik' => $validated['nik'] ?? null,
            'nomor_whatsapp' => $account->nomor_whatsapp,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'usia' => $validated['usia'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'hubungan' => $validated['hubungan'] ?? Patient::HUBUNGAN_DIRI_SENDIRI,
        ]);

        $request->session()->put('selected_patient_id', $patient->id);

        return to_route('patient.profile.index')
            ->with('success', 'Profil pasien berhasil disimpan.');
    }

    protected function resolveAccount(Request $request): ?PatientAccount
    {
        $accountId = $request->session()->get('patient_account_id');

        return $accountId ? PatientAccount::query()->find($accountId) : null;
    }

    protected function maskNik(?string $nik): ?string
    {
        if (! $nik) {
            return null;
        }

        $visibleStart = substr($nik, 0, 4);
        $visibleEnd = substr($nik, -4);

        return $visibleStart.str_repeat('*', max(strlen($nik) - 8, 4)).$visibleEnd;
    }
}
