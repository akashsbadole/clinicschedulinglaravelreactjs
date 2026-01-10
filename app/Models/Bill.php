<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'created_by',
        'bill_number',
        'bill_date',
        'due_date',
        'amount',
        'tax',
        'discount',
        'total_amount',
        'status',
        'payment_method',
        'payment_date',
        'description',
        'items',
        'insurance_claim_status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'items' => 'array',
        'bill_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date'
    ];

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}