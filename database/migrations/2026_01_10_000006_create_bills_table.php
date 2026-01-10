<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->string('bill_number')->unique();
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('amount', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('unpaid'); // unpaid, paid, partially_paid, cancelled
            $table->string('payment_method')->nullable(); // cash, credit_card, insurance, online
            $table->date('payment_date')->nullable();
            $table->text('description')->nullable();
            $table->json('items')->nullable(); // Array of services/products
            $table->string('insurance_claim_status')->nullable(); // pending, approved, rejected
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bills');
    }
};