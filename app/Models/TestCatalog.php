<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_code',
        'test_name',
        'description',
        'category',
        'specimen_type',
        'reference_range',
        'unit_of_measure',
        'turnaround_time',
        'is_active'
    ];

    public function labTests()
    {
        return $this->hasMany(LabTest::class);
    }
}