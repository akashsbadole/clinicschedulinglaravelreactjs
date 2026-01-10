<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'user_id',
        'doctor_id',
        'specialization',
        'license_number',
        'qualifications',
        'years_of_experience',
        'consultation_fee',
        'bio',
        'availability',
        'is_accepting_new_patients'
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}