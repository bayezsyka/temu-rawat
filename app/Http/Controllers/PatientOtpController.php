<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\PatientOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientOtpController extends Controller
{
    public function __construct(
        protected PatientOtpService $patientOtpService,
    ) {
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->session()->has('patient_account_id')) {
            return to_route('patient.profile.index');
        }

        $pendingWhatsapp = $request->session()->get('pending_patient_whatsapp');

        if ($request->query('step') === 'verifikasi' && $pendingWhatsapp) {
            return Inertia::render('TemuRawat/Patient/OtpVerify', [
                'nomorWhatsapp' => $pendingWhatsapp,
            ]);
        }

        return Inertia::render('TemuRawat/Patient/OtpLogin', [
            'nomorWhatsapp' => $pendingWhatsapp,
        ]);
    }

    public function send(SendOtpRequest $request): RedirectResponse
    {
        $account = $this->patientOtpService->sendOtp($request->validated('nomor_whatsapp'));

        $request->session()->put('pending_patient_whatsapp', $account->nomor_whatsapp);

        return redirect()->route('patient.login', ['step' => 'verifikasi'])
            ->with('success', 'OTP dikirim ke WhatsApp. Cek log Laravel untuk mode development.');
    }

    public function verify(VerifyOtpRequest $request): RedirectResponse
    {
        $account = $this->patientOtpService->verifyOtp(
            $request->validated('nomor_whatsapp'),
            $request->validated('otp'),
        );

        $request->session()->forget('pending_patient_whatsapp');
        $request->session()->put('patient_account_id', $account->id);

        return to_route('patient.profile.index')
            ->with('success', 'Nomor WhatsApp berhasil diverifikasi.');
    }
}
