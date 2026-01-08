<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            // Holiday info
            $table->string('name');                 // e.g. "Hari Raya Aidilfitri"
            $table->date('holiday_date');           // 2026-04-22

            // Optional range (for multi-day holidays)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Control flags
            $table->boolean('is_active')->default(true);

            // Audit
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
