<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'generic_name',
        'brand_name',
        'drug_code',
        'description',
        'drug_class',
        'form',
        'strength',
        'indications',
        'contraindications',
        'side_effects',
        'is_controlled',
        'requires_prescription'
    ];

    public function medicationOrders()
    {
        return $this->hasMany(MedicationOrder::class);
    }
}