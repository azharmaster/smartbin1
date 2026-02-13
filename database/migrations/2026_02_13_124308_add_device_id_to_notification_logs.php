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
        Schema::table('notification_logs', function (Blueprint $table) {
            // Add device_id column
            $table->unsignedBigInteger('device_id')->nullable()->after('id');

            // Optional: add index for faster queries per device
            $table->index(['device_id', 'sent_at']);

            // Optional: if you want FK constraint (devices.id_device)
            // $table->foreign('device_id')->references('id_device')->on('devices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex(['device_id', 'sent_at']);

            // Drop foreign key if added
            // $table->dropForeign(['device_id']);

            // Drop the column
            $table->dropColumn('device_id');
        });
    }
};
