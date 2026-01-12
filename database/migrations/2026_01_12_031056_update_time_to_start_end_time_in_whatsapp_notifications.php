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
            // Remove the old 'time' column
            $table->dropColumn('time');

            // Add new 'start_time' and 'end_time' columns
            $table->time('start_time')->nullable()->after('end_date');
            $table->time('end_time')->nullable()->after('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_notifications', function (Blueprint $table) {
            // Drop the new columns
            $table->dropColumn(['start_time', 'end_time']);

            // Restore the old 'time' column
            $table->time('time')->nullable()->after('end_date');
        });
    }
};
