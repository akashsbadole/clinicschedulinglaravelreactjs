<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            
            $table->string('note_type'); // SOAP, Progress, Discharge, etc.
            $table->text('subjective')->nullable(); // Patient's perspective
            $table->text('objective')->nullable(); // Clinical findings
            $table->text('assessment')->nullable(); // Clinical judgment
            $table->text('plan')->nullable(); // Treatment plan
            $table->text('clinical_impression')->nullable();
            $table->boolean('is_confidential')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clinical_notes');
    }
};