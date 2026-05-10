<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use App\Services\PracticeSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function __construct(
        protected PracticeSessionService $practiceSessionService,
    ) {
    }

    public function index(): Response
    {
        $users = User::query()
            ->where('role', User::ROLE_DOKTER)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('TemuRawat/Admin/Doctors', [
            'doctors' => $this->practiceSessionService->serializeDoctorOptions(),
            'doctorUsers' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'nama' => ['required', 'string', 'max:255'],
            'spesialisasi' => ['nullable', 'string', 'max:255'],
            'nomor_sip' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([Doctor::STATUS_AKTIF, Doctor::STATUS_NONAKTIF])],
        ]);

        Doctor::query()->create($validated);

        return back()->with('success', 'Data dokter berhasil ditambahkan.');
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'nama' => ['required', 'string', 'max:255'],
            'spesialisasi' => ['nullable', 'string', 'max:255'],
            'nomor_sip' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([Doctor::STATUS_AKTIF, Doctor::STATUS_NONAKTIF])],
        ]);

        $doctor->update($validated);

        return back()->with('success', 'Data dokter berhasil diperbarui.');
    }
}
