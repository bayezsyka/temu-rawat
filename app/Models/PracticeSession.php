<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeSession extends Model
{
    public const STATUS_BUKA = 'buka';

    public const STATUS_ISTIRAHAT = 'istirahat';

    public const STATUS_SELESAI = 'selesai';

    public const STATUSES = [
        self::STATUS_BUKA,
        self::STATUS_ISTIRAHAT,
        self::STATUS_SELESAI,
    ];

    protected $fillable = [
        'doctor_id',
        'tanggal',
        'nama_dokter',
        'status',
        'nomor_terakhir',
        'mulai_pada',
        'selesai_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'mulai_pada' => 'datetime',
            'selesai_pada' => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
