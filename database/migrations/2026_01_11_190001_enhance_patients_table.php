<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            // New fields for comprehensive patient master
            $table->date('date_of_birth')->nullable()->after('insurance_policy_number');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('marital_status')->nullable()->after('gender');
            $table->string('occupation')->nullable()->after('marital_status');
            $table->string('national_id')->nullable()->after('occupation');
            $table->string('preferred_language')->nullable()->after('national_id');
            $table->json('contact_preferences')->nullable()->after('preferred_language');
            $table->json('family_medical_history')->nullable()->after('contact_preferences');
            $table->json('social_history')->nullable()->after('family_medical_history');
            $table->json('lifestyle_factors')->nullable()->after('social_history');
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'gender',
                'marital_status',
                'occupation',
                'national_id',
                'preferred_language',
                'contact_preferences',
                'family_medical_history',
                'social_history',
                'lifestyle_factors'
            ]);
        });
    }
};