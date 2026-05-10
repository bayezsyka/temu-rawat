<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class InitialCheck extends Model
{
    protected $fillable = [
        'queue_id',
        'tekanan_darah',
        'berat_badan',
        'tinggi_badan',
        'suhu',
        'nadi',
        'catatan_asisten',
    ];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }
}
