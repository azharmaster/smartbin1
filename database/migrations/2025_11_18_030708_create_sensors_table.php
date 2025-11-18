<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id'); // FK to devices
            $table->decimal('battery', 5, 2)->nullable(); // percentage 0-100
            $table->decimal('capacity', 8, 2)->nullable(); 
            $table->timestamp('time')->nullable();
            $table->string('network')->nullable(); // e.g., wifi, LTE
            $table->timestamps();

            // Foreign key
            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};
