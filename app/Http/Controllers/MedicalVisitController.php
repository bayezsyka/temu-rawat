<?php

namespace App\Http\Controllers;

use App\Models\MedicalVisit;
use App\Services\MedicalVisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicalVisitController extends Controller
{
    public function __construct(
        protected MedicalVisitService $medicalVisitService,
    ) {
    }

    public function store(Request $request, string $visit): RedirectResponse
    {
        $validated = $request->validate([
            'keluhan_utama' => ['nullable', 'string', 'max:1000'],
            'ringkasan_pemeriksaan' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['nullable', 'string', 'max:2000'],
            'tindakan' => ['nullable', 'string', 'max:2000'],
            'catatan_dokter' => ['nullable', 'string', 'max:5000'],
            'anjuran' => ['nullable', 'string', 'max:3000'],
            'kontrol_ulang_pada' => ['nullable', 'date'],
        ]);

        $this->medicalVisitService->save($request->user(), $visit, $validated);

        return back()->with('success', 'Pemeriksaan dokter berhasil disimpan.');
    }

    public function summary(Request $request, MedicalVisit $visit): Response
    {
        $visit->loadMissing('doctor', 'prescription.items', 'patient');

        $patientAccountId = $request->session()->get('patient_account_id');
        $available = $patientAccountId
            ? $this->medicalVisitService->patientCanViewSummary($visit, (int) $patientAccountId)
            : false;

        return Inertia::render('TemuRawat/Patient/VisitSummary', [
            'available' => $available,
            'visit' => $available ? [
                'tanggal' => $visit->created_at?->format('d M Y H:i'),
                'doctor' => $visit->doctor?->nama,
                'diagnosis' => $visit->diagnosis,
                'anjuran' => $visit->anjuran,
                'kontrol_ulang_pada' => $visit->kontrol_ulang_pada?->format('d M Y'),
                'patient_visible_until' => $visit->patient_visible_until?->format('d M Y H:i'),
                'prescription' => $visit->prescription ? [
                    'catatan_resep' => $visit->prescription->catatan_resep,
                    'items' => $visit->prescription->items->map(fn ($item) => [
                        'nama_obat' => $item->nama_obat,
                        'dosis' => $item->dosis,
                        'aturan_pakai' => $item->aturan_pakai,
                        'jumlah' => $item->jumlah,
                        'satuan' => $item->satuan,
                        'catatan' => $item->catatan,
                    ])->values()->all(),
                ] : null,
            ] : null,
        ]);
    }
}
