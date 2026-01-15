<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('ordered_by')->constrained('users')->onDelete('set null');
            
            $table->string('order_number')->unique();
            $table->string('priority')->default('routine'); // routine, urgent, stat
            $table->string('status')->default('ordered'); // ordered, processing, completed, cancelled
            $table->text('clinical_indication')->nullable();
            $table->dateTime('specimen_collection_time')->nullable();
            $table->dateTime('results_available_time')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lab_orders');
    }
};