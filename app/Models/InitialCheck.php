<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InitialCheck extends Model
{
    protected $fillable = [
        'queue_id',
        'tekanan_darah',
        'berat_badan',
        'tinggi_badan',
        'suhu',
        'nadi',
        'saturasi_oksigen',
        'catatan_asisten',
        'checked_by',
    ];

    protected function casts(): array
    {
        return [
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
            'suhu' => 'decimal:1',
        ];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
