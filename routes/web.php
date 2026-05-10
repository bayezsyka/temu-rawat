<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MedicalVisitController;
use App\Http\Controllers\PatientOtpController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\PracticeSessionController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicDisplayController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\QueueRegistrationController;
use App\Http\Controllers\StaffPanelController;
use App\Http\Controllers\StaffQueueActionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Welcome'))->name('home');

Route::get('/masuk', [PatientOtpController::class, 'create'])->name('patient.login');
Route::post('/otp/kirim', [PatientOtpController::class, 'send'])->name('patient.otp.send');
Route::post('/otp/verifikasi', [PatientOtpController::class, 'verify'])->name('patient.otp.verify');

Route::get('/pasien/profil', [PatientProfileController::class, 'index'])->name('patient.profile.index');
Route::post('/pasien/profil', [PatientProfileController::class, 'store'])->name('patient.profile.store');

Route::get('/daftar', [QueueRegistrationController::class, 'create'])->name('registration.create');
Route::post('/daftar', [QueueRegistrationController::class, 'store'])->name('registration.store');

Route::get('/antrian/{queue:public_code}', [QueueController::class, 'show'])->name('queues.show');
Route::get('/display', [PublicDisplayController::class, 'index'])->name('display.index');
Route::get('/pasien/kunjungan/{visit}/ringkasan', [MedicalVisitController::class, 'summary'])->name('patient.visits.summary');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('panel.index'))->name('dashboard');
    Route::get('/logout', function (Request $request) {
        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin,dokter,asisten'])->group(function () {
    Route::get('/panel', [StaffPanelController::class, 'index'])->name('panel.index');
    Route::get('/panel/sesi/{session}', [StaffPanelController::class, 'show'])->name('panel.sessions.show');
    Route::post('/panel/antrian/{queue}/panggil', [StaffQueueActionController::class, 'call'])->name('panel.queues.call');
    Route::post('/panel/antrian/{queue}/awal', [StaffQueueActionController::class, 'initial'])->name('panel.queues.initial');
    Route::post('/panel/antrian/{queue}/mulai-periksa', [StaffQueueActionController::class, 'startDoctorCheck'])->name('panel.queues.start-doctor');
    Route::post('/panel/antrian/{queue}/lewati', [StaffQueueActionController::class, 'skip'])->name('panel.queues.skip');
    Route::post('/panel/antrian/{queue}/batal', [StaffQueueActionController::class, 'cancel'])->name('panel.queues.cancel');
    Route::post('/panel/antrian/{queue}/selesai', [StaffQueueActionController::class, 'finish'])->name('panel.queues.finish');
    Route::post('/panel/kunjungan/{visit}', [MedicalVisitController::class, 'store'])->name('panel.visits.store');
    Route::post('/panel/kunjungan/{visit}/resep', [PrescriptionController::class, 'store'])->name('panel.visits.prescription');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/sesi', [PracticeSessionController::class, 'index'])->name('practice-sessions.index');
    Route::post('/admin/sesi', [PracticeSessionController::class, 'store'])->name('practice-sessions.store');
    Route::patch('/admin/sesi/{session}', [PracticeSessionController::class, 'update'])->name('practice-sessions.update');
    Route::get('/admin/dokter', [DoctorController::class, 'index'])->name('doctors.index');
    Route::post('/admin/dokter', [DoctorController::class, 'store'])->name('doctors.store');
    Route::patch('/admin/dokter/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
});

require __DIR__.'/auth.php';
