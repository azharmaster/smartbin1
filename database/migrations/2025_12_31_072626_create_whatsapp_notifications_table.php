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
    Schema::create('whatsapp_notifications', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // Notification name
        $table->text('message'); // WhatsApp message
        $table->string('target'); // Phone number
        $table->boolean('is_active')->default(true); // ON / OFF
        $table->timestamp('start_time')->nullable(); // Optional start time
        $table->timestamp('end_time')->nullable(); // Optional auto-off time
        $table->timestamp('last_sent_at')->nullable(); // Last time message sent
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('whatsapp_notifications');
}
};
