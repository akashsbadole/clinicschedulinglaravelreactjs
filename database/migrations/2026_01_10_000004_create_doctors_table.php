<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('doctor_id')->unique(); // Custom doctor ID
            $table->string('specialization');
            $table->string('license_number')->unique();
            $table->string('qualifications');
            $table->integer('years_of_experience');
            $table->string('consultation_fee');
            $table->text('bio')->nullable();
            $table->string('availability')->default('Monday-Friday');
            $table->boolean('is_accepting_new_patients')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('doctors');
    }
};