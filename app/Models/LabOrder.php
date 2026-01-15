<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class LabOrder extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'ordered_by',
        'order_number',
        'priority',
        'status',
        'clinical_indication',
        'specimen_collection_time',
        'results_available_time'
    ];

    protected $dates = [
        'specimen_collection_time',
        'results_available_time',
        'deleted_at'
    ];

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

    public function orderer()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function labTests()
    {
        return $this->hasMany(LabTest::class);
    }
}