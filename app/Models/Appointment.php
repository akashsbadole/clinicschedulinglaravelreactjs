<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'appointment_number',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'appointment_type',
        'reason',
        'notes',
        'priority',
        'is_first_visit'
    ];

    protected $dates = ['appointment_date', 'start_time', 'end_time', 'deleted_at'];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bill()
    {
        return $this->hasOne(Bill::class, 'appointment_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
}