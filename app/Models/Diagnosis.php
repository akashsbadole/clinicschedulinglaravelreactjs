<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Traits\Encryptable;

class Diagnosis extends Model
{
    use HasFactory, SoftDeletes, Auditable, Encryptable;

    protected $encryptable = ['clinical_description', 'supporting_evidence'];

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'diagnosis_type',
        'clinical_description',
        'date_of_diagnosis',
        'diagnosis_status',
        'supporting_evidence',
        'is_confirmed'
    ];

    protected $dates = ['date_of_diagnosis'];

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

    public function icd10Codes()
    {
        return $this->belongsToMany(Icd10Code::class, 'diagnosis_icd10')
            ->withPivot('code_type')
            ->withTimestamps();
    }
}