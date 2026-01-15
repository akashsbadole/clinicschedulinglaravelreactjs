<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Procedure extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'doctor_id',
        'performed_by',
        'procedure_catalog_id',
        'scheduled_time',
        'start_time',
        'end_time',
        'status',
        'indication',
        'findings',
        'complications',
        'follow_up_instructions'
    ];

    protected $dates = [
        'scheduled_time',
        'start_time',
        'end_time',
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

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function procedureCatalog()
    {
        return $this->belongsTo(ProcedureCatalog::class);
    }
}