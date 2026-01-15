<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('test_code')->unique();
            $table->string('test_name');
            $table->text('description')->nullable();
            $table->string('category'); // Hematology, Biochemistry, Microbiology, etc.
            $table->string('specimen_type');
            $table->text('reference_range')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->decimal('turnaround_time', 5, 2)->nullable(); // hours
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_catalog');
    }
};