<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->string('appointment_number')->unique();
            $table->dateTime('appointment_date');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled, no-show
            $table->string('appointment_type'); // consultation, follow-up, emergency
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->string('priority')->default('normal'); // low, normal, high, emergency
            $table->boolean('is_first_visit')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};