<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_catalog_id')->constrained()->onDelete('cascade');
            
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled
            $table->text('specimen_type')->nullable();
            $table->text('specimen_source')->nullable();
            $table->text('results')->nullable();
            $table->text('reference_range')->nullable();
            $table->text('interpretation')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lab_tests');
    }
};