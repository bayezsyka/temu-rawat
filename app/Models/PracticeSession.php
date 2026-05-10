<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

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
        'tanggal',
        'nama_dokter',
        'status',
        'nomor_terakhir',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date:Y-m-d',
        ];
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
