<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Service extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'clinic_id',
        'service_code',
        'name',
        'description',
        'base_price',
        'category',
        'taxable',
        'insurance_eligible',
        'is_active'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'taxable' => 'boolean',
        'insurance_eligible' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }
}
