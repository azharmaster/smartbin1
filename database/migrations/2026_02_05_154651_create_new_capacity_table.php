<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacity_settings', function (Blueprint $table) {
            $table->id();

            // Tie capacity to an asset
            $table->unsignedBigInteger('asset_id')->unique();
            $table->foreign('asset_id')
                  ->references('id')
                  ->on('assets')
                  ->onDelete('cascade');

            // Capacity thresholds
            $table->unsignedTinyInteger('empty_to'); // 0–99
            $table->unsignedTinyInteger('half_to');  // 1–100

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacity_settings');
    }
};
