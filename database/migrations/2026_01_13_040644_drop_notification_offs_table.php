<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('notification_offs');
    }

    public function down(): void
    {
        Schema::create('notification_offs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('device_id')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });
    }
};
