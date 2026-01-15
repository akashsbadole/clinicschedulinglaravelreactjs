<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('set null');
            
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->decimal('weight', 5, 2)->nullable(); // kg
            $table->decimal('bmi', 5, 2)->nullable(); // calculated
            $table->decimal('temperature', 4, 1)->nullable(); // °C
            $table->integer('systolic_bp')->nullable(); // mmHg
            $table->integer('diastolic_bp')->nullable(); // mmHg
            $table->integer('pulse_rate')->nullable(); // bpm
            $table->integer('respiratory_rate')->nullable(); // breaths/min
            $table->decimal('oxygen_saturation', 4, 1)->nullable(); // %
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vital_signs');
    }
};