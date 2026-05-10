<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    public const STATUS_TERDAFTAR = 'terdaftar';
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DIPANGGIL = 'dipanggil';
    public const STATUS_DIPERIKSA = 'diperiksa';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DILEWATI = 'dilewati';
    public const STATUS_BATAL = 'batal';

    public const STATUS_KUNJUNGAN_BARU = 'baru';
    public const STATUS_KUNJUNGAN_LAMA = 'lama';

    public const METODE_ONLINE = 'online';
    public const METODE_LANGSUNG = 'langsung';

    public const STATUS_KUNJUNGAN = [
        self::STATUS_KUNJUNGAN_BARU,
        self::STATUS_KUNJUNGAN_LAMA,
    ];

    public const METODE_DAFTAR = [
        self::METODE_ONLINE,
        self::METODE_LANGSUNG,
    ];

    public const STATUS_AKTIF = [
        self::STATUS_MENUNGGU,
        self::STATUS_DIPANGGIL,
        self::STATUS_DIPERIKSA,
    ];

    public const STATUS_TERMINAL = [
        self::STATUS_SELESAI,
        self::STATUS_BATAL,
    ];

    protected $fillable = [
        'patient_id',
        'practice_session_id',
        'kode_antrian',
        'nomor_urut',
        'keluhan',
        'status_kunjungan',
        'metode_daftar',
        'status',
        'waktu_daftar',
        'waktu_dipanggil',
        'waktu_mulai_periksa',
        'waktu_selesai',
    ];

    protected function casts(): array
    {
        return [
            'waktu_daftar' => 'datetime',
            'waktu_dipanggil' => 'datetime',
            'waktu_mulai_periksa' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function practiceSession(): BelongsTo
    {
        return $this->belongsTo(PracticeSession::class);
    }

    public function initialCheck(): HasOne
    {
        return $this->hasOne(InitialCheck::class);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::STATUS_TERMINAL, true);
    }
}
