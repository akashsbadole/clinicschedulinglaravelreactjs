<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class LabTest extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'lab_order_id',
        'test_catalog_id',
        'status',
        'specimen_type',
        'specimen_source',
        'results',
        'reference_range',
        'interpretation',
        'comments',
        'processed_by'
    ];

    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function testCatalog()
    {
        return $this->belongsTo(TestCatalog::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}