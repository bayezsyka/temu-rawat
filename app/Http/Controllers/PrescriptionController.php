<?php

namespace App\Http\Controllers;

use App\Services\PrescriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function __construct(
        protected PrescriptionService $prescriptionService,
    ) {
    }

    public function store(Request $request, string $visit): RedirectResponse
    {
        $validated = $request->validate([
            'catatan_resep' => ['nullable', 'string', 'max:3000'],
            'items' => ['array'],
            'items.*.nama_obat' => ['nullable', 'string', 'max:255'],
            'items.*.dosis' => ['nullable', 'string', 'max:255'],
            'items.*.aturan_pakai' => ['nullable', 'string', 'max:255'],
            'items.*.jumlah' => ['nullable', 'string', 'max:255'],
            'items.*.satuan' => ['nullable', 'string', 'max:255'],
            'items.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->prescriptionService->save($request->user(), $visit, $validated);

        return back()->with('success', 'Resep berhasil disimpan.');
    }
}
