<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('clinic_id')->nullable()->constrained()->onDelete('set null');
            $table->string('role')->default('patient'); // admin, doctor, staff, patient
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('specialization')->nullable(); // For doctors
            $table->string('license_number')->nullable(); // For doctors
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('profile_photo_path')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'clinic_id',
                'role',
                'phone',
                'address',
                'specialization',
                'license_number',
                'date_of_birth',
                'gender',
                'profile_photo_path'
            ]);
        });
    }
};