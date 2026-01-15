<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use App\Traits\Auditable;
class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes, Auditable;


    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'record_type',
        'content',
        'attachments',
        'severity',
        'is_confidential'
    ];

    protected $casts = [
        'attachments' => 'array',
        // 'content' => 'encrypted',
    ];

    /**
     * Get the record's content.
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (empty($value))
                    return $value;
                try {
                    return Crypt::decryptString($value);
                } catch (\Exception $e) {
                    return "[DECRYPT_FAILED: " . $e->getMessage() . "]";
                }
            }
        );
    }

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

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}