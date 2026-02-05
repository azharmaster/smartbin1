<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('capacity_settings');
    }

    public function down(): void
    {
        Schema::create('capacity_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('empty_to');
            $table->unsignedInteger('half_to');
            $table->timestamps();
        });
    }
};