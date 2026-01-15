<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class TreatmentPlan extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'plan_type',
        'start_date',
        'end_date',
        'goals',
        'instructions',
        'status'
    ];

    protected $dates = ['start_date', 'end_date', 'deleted_at'];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
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

    public function medicationOrders()
    {
        return $this->hasMany(MedicationOrder::class);
    }
}