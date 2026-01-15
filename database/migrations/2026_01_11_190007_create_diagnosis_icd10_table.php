<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('diagnosis_icd10', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnosis_id')->constrained()->onDelete('cascade');
            $table->foreignId('icd10_code_id')->constrained()->onDelete('cascade');
            $table->string('code_type')->default('primary'); // primary, secondary, additional
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diagnosis_icd10');
    }
};