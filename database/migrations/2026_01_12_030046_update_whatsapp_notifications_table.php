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
        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            // Add new columns
            $table->date('start_date')->nullable()->after('is_active');
            $table->date('end_date')->nullable()->after('start_date');
            $table->time('time')->nullable()->after('end_date');

            // Remove old datetime columns
            $table->dropColumn(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            // Recreate old datetime columns
            $table->timestamp('start_time')->nullable()->after('is_active');
            $table->timestamp('end_time')->nullable()->after('start_time');

            // Drop new columns
            $table->dropColumn(['start_date', 'end_date', 'time']);
        });
    }
};
