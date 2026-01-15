<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('procedure_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('procedure_code')->unique();
            $table->string('procedure_name');
            $table->text('description')->nullable();
            $table->string('category')->nullable(); // Surgical, Diagnostic, Therapeutic
            $table->string('specialty')->nullable();
            $table->decimal('estimated_duration', 5, 2)->nullable(); // minutes
            $table->text('preparation_instructions')->nullable();
            $table->text('post_procedure_instructions')->nullable();
            $table->boolean('requires_consent')->default(false);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('procedure_catalogs');
    }
};