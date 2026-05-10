<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'nama',
        'nomor_whatsapp',
        'tanggal_lahir',
        'usia',
        'jenis_kelamin',
        'alamat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
