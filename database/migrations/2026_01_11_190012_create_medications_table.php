<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('generic_name');
            $table->string('brand_name')->nullable();
            $table->string('drug_code')->unique();
            $table->text('description')->nullable();
            $table->string('drug_class')->nullable();
            $table->string('form')->nullable(); // Tablet, Capsule, Injection, etc.
            $table->string('strength')->nullable();
            $table->text('indications')->nullable();
            $table->text('contraindications')->nullable();
            $table->text('side_effects')->nullable();
            $table->boolean('is_controlled')->default(false);
            $table->boolean('requires_prescription')->default(true);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medications');
    }
};