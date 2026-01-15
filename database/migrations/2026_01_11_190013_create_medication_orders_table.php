<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medication_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('prescribed_by')->constrained('users')->onDelete('set null');
            
            $table->foreignId('medication_id')->constrained()->onDelete('cascade');
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->string('route')->nullable(); // Oral, IV, IM, etc.
            $table->string('duration')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('refills')->default(0);
            $table->string('status')->default('active'); // active, completed, discontinued
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medication_orders');
    }
};