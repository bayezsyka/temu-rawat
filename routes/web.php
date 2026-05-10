<?php

use App\Http\Controllers\PatientController;
use App\Http\Controllers\PracticeSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\StaffQueueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::controller(PatientController::class)->group(function () {
    Route::get('/daftar', 'create')->name('registration.create');
    Route::post('/daftar', 'store')->name('registration.store');
});

Route::controller(QueueController::class)->group(function () {
    Route::get('/antrian/{kode}', 'show')->name('queues.show');
    Route::get('/display', 'display')->name('display.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        return redirect()->route('panel.index');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin,asisten,dokter'])->group(function () {
    Route::get('/panel', [StaffQueueController::class, 'index'])->name('panel.index');
    Route::patch('/panel/antrian/{queue}/status', [StaffQueueController::class, 'updateStatus'])->name('panel.queues.status');
    Route::patch('/panel/antrian/{queue}/pemeriksaan-awal', [StaffQueueController::class, 'updateInitialCheck'])->name('panel.queues.initial-check');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/sesi', [PracticeSessionController::class, 'index'])->name('practice-sessions.index');
    Route::put('/admin/sesi', [PracticeSessionController::class, 'upsert'])->name('practice-sessions.upsert');
});

require __DIR__.'/auth.php';
