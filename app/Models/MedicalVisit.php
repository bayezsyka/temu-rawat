<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MedicalVisit extends Model
{
    protected $fillable = [
        'queue_id',
        'patient_id',
        'doctor_id',
        'keluhan_utama',
        'ringkasan_pemeriksaan',
        'diagnosis',
        'tindakan',
        'catatan_dokter',
        'anjuran',
        'kontrol_ulang_pada',
        'patient_visible_until',
        'finalized_at',
        'finalized_by',
    ];

    protected function casts(): array
    {
        return [
            'kontrol_ulang_pada' => 'date',
            'patient_visible_until' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function prescriptions(): HasOne
    {
        return $this->prescription();
    }
}
