<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_ASISTEN = 'asisten';

    public const ROLE_DOKTER = 'dokter';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_ASISTEN,
        self::ROLE_DOKTER,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function doctorProfile(): HasOne
    {
        return $this->doctor();
    }

    public function practiceSessions(): HasManyThrough
    {
        return $this->hasManyThrough(
            PracticeSession::class,
            Doctor::class,
            'user_id',
            'doctor_id',
        );
    }

    public function checkedInitialChecks(): HasMany
    {
        return $this->hasMany(InitialCheck::class, 'checked_by');
    }

    public function finalizedMedicalVisits(): HasMany
    {
        return $this->hasMany(MedicalVisit::class, 'finalized_by');
    }

    public function createdPrescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'created_by');
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDokter(): bool
    {
        return $this->role === self::ROLE_DOKTER;
    }

    public function isAsisten(): bool
    {
        return $this->role === self::ROLE_ASISTEN;
    }
}
