<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'procedure_code',
        'procedure_name',
        'description',
        'category',
        'specialty',
        'estimated_duration',
        'preparation_instructions',
        'post_procedure_instructions',
        'requires_consent',
        'is_active'
    ];

    protected $casts = [
        'estimated_duration' => 'decimal:2',
        'requires_consent' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }
}