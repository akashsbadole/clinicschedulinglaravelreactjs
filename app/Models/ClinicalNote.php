<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use App\Traits\Encryptable;

class ClinicalNote extends Model
{
    use HasFactory, SoftDeletes, Auditable, Encryptable;

    protected $encryptable = ['subjective', 'objective', 'assessment', 'plan', 'clinical_impression'];

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'note_type',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'clinical_impression',
        'is_confidential'
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}