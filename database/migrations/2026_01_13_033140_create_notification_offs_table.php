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
        Schema::create('notification_offs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('asset_id')->nullable();   // Bin
    $table->unsignedBigInteger('device_id')->nullable();  // Device (optional)
    $table->timestamp('start_at');
    $table->timestamp('end_at');
    $table->boolean('active')->default(true);
    $table->timestamps();

    $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
    $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_offs');
    }
};
