<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Appointment extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'appointment_number',
        'appointment_date',
        'start_time',
        'end_time',
        'scheduled_at',
        'status',
        'appointment_type',
        'reason',
        'notes',
        'priority',
        'is_first_visit'
    ];

    protected $dates = ['appointment_date', 'start_time', 'end_time', 'scheduled_at', 'deleted_at'];

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

    public function bills()
    {
        return $this->hasMany(Bill::class, 'appointment_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function getScheduledAtAttribute()
    {
        if ($this->attributes['scheduled_at'] ?? null) {
            return $this->attributes['scheduled_at'];
        }

        // Fallback to combining appointment_date and start_time
        if ($this->appointment_date && $this->start_time) {
            $appointmentDate = \Carbon\Carbon::parse($this->appointment_date);
            $startTime = \Carbon\Carbon::parse($this->start_time);
            return $appointmentDate->setTimeFrom($startTime);
        }

        return null;
    }
}