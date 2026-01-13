<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_offs', function (Blueprint $table) {
            $table->id();
            
            // Link to the assets table (bins)
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');

            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_offs');
    }
};
