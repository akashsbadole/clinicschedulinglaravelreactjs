<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icd10Code extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'category',
        'chapter',
        'is_active'
    ];

    public function diagnoses()
    {
        return $this->belongsToMany(Diagnosis::class, 'diagnosis_icd10')
            ->withPivot('code_type')
            ->withTimestamps();
    }
}