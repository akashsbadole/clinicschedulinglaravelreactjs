<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            
            $table->string('plan_type'); // Acute, Chronic, Preventive, Rehabilitation
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('goals')->nullable();
            $table->text('instructions')->nullable();
            $table->string('status')->default('active'); // active, completed, discontinued
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('treatment_plans');
    }
};