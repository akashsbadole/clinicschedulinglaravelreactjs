<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referring_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->foreignId('receiving_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('referring_facility')->nullable();
            $table->text('reason');
            $table->string('specialty')->nullable();
            $table->string('priority')->default('routine');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('referral_date');
            $table->string('external_referral_id')->nullable();
            $table->text('response_notes')->nullable();
            $table->dateTime('response_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->foreignId('encounter_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('referrals');
    }
};