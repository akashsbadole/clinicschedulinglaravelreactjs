<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('icd10_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // ICD-10 code
            $table->text('description'); // Full description
            $table->string('category')->nullable();
            $table->string('chapter')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('icd10_codes');
    }
};