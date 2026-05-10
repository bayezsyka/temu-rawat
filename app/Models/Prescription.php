<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    protected $fillable = [
        'medical_visit_id',
        'catatan_resep',
        'created_by',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(MedicalVisit::class);
    }

    public function medicalVisit(): BelongsTo
    {
        return $this->visit();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
