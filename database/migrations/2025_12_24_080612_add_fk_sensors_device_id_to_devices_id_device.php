<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            // Ensure device_id column is VARCHAR to match devices.id_device
            $table->string('device_id')->change();

            // Add foreign key with a custom name
            $table->foreign('device_id', 'fk_sensors_device')
                  ->references('id_device')
                  ->on('devices')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            // Drop foreign key using the custom name
            $table->dropForeign('fk_sensors_device');
        });
    }
};
