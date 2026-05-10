<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    public const HUBUNGAN_DIRI_SENDIRI = 'diri_sendiri';

    public const HUBUNGAN_ANAK = 'anak';

    public const HUBUNGAN_ORANG_TUA = 'orang_tua';

    public const HUBUNGAN_KELUARGA = 'keluarga';

    public const HUBUNGAN_LAINNYA = 'lainnya';

    public const HUBUNGAN = [
        self::HUBUNGAN_DIRI_SENDIRI,
        self::HUBUNGAN_ANAK,
        self::HUBUNGAN_ORANG_TUA,
        self::HUBUNGAN_KELUARGA,
        self::HUBUNGAN_LAINNYA,
    ];

    protected $fillable = [
        'patient_account_id',
        'nama',
        'nik',
        'nomor_whatsapp',
        'tanggal_lahir',
        'usia',
        'jenis_kelamin',
        'alamat',
        'hubungan',
    ];

    protected function casts(): array
    {
        return [
            'nik' => 'encrypted',
            'tanggal_lahir' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(PatientAccount::class);
    }

    public function patientAccount(): BelongsTo
    {
        return $this->account();
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function medicalVisits(): HasMany
    {
        return $this->hasMany(MedicalVisit::class);
    }
}
