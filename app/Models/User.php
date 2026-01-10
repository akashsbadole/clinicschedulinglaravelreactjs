<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'clinic_id',
        'role',
        'phone',
        'address',
        'specialization',
        'license_number',
        'date_of_birth',
        'gender',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'date_of_birth' => 'date',
        ];
    }

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_DOCTOR = 'doctor';
    const ROLE_STAFF = 'staff';
    const ROLE_PATIENT = 'patient';

    // Role checking methods
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDoctor()
    {
        return $this->role === self::ROLE_DOCTOR;
    }

    public function isStaff()
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isPatient()
    {
        return $this->role === self::ROLE_PATIENT;
    }

    // Relationships
    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'created_by');
    }
}
