<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Encounter extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'appointment_id',
        'doctor_id',
        'created_by',
        'encounter_type',
        'encounter_number',
        'start_time',
        'end_time',
        'status',
        'priority',
        'chief_complaint',
        'history_of_present_illness'
    ];

    protected $dates = ['start_time', 'end_time', 'deleted_at'];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    public function clinicalNotes()
    {
        return $this->hasMany(ClinicalNote::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }
}