<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
class Patient extends Model
{
    use HasFactory, SoftDeletes, Auditable;


    protected $fillable = [
        'clinic_id',
        'user_id',
        'patient_id',
        'emergency_contact_name',
        'emergency_contact_phone',
        'blood_type',
        'allergies',
        'medical_history',
        'insurance_provider',
        'insurance_policy_number',
        'last_visit_date',
        'date_of_birth',
        'gender',
        'marital_status',
        'occupation',
        'national_id',
        'preferred_language',
        'contact_preferences',
        'family_medical_history',
        'social_history',
        'lifestyle_factors'
    ];

    protected $dates = ['last_visit_date', 'date_of_birth', 'deleted_at'];

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

    public function encounters()
    {
        return $this->hasMany(Encounter::class);
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

    public function medicationOrders()
    {
        return $this->hasMany(MedicationOrder::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }
}