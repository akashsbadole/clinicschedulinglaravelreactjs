<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('notification_type'); // appointment_created, appointment_updated, etc.
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false); // For future SMS integration
            $table->boolean('push_enabled')->default(true);
            $table->json('settings')->nullable(); // Additional settings like reminder timing
            $table->timestamps();

            $table->unique(['user_id', 'notification_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
