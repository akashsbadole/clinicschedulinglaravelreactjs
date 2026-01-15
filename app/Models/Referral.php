<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'referring_doctor_id',
        'receiving_doctor_id',
        'referring_facility',
        'reason',
        'specialty',
        'priority',
        'notes',
        'status',
        'referral_date',
        'external_referral_id',
        'response_notes',
        'response_date',
        'completed_date',
        'encounter_id',
    ];

    protected $casts = [
        'referral_date' => 'datetime',
        'response_date' => 'datetime',
        'completed_date' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function referringDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'referring_doctor_id');
    }

    public function receivingDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'receiving_doctor_id');
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }
}