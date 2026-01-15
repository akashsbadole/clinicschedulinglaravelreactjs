<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            
            $table->string('diagnosis_type'); // Primary, Secondary, Differential
            $table->text('clinical_description')->nullable();
            $table->date('date_of_diagnosis');
            $table->string('diagnosis_status')->default('active'); // active, resolved, chronic
            $table->text('supporting_evidence')->nullable();
            $table->boolean('is_confirmed')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diagnoses');
    }
};