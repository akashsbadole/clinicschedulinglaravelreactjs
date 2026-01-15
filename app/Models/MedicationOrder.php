<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class MedicationOrder extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'treatment_plan_id',
        'patient_id',
        'doctor_id',
        'prescribed_by',
        'medication_id',
        'dosage',
        'frequency',
        'route',
        'duration',
        'instructions',
        'quantity',
        'refills',
        'status',
        'start_date',
        'end_date'
    ];

    protected $dates = ['start_date', 'end_date', 'deleted_at'];

    public function treatmentPlan()
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function prescriber()
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}