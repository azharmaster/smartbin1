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
        Schema::table('floor', function (Blueprint $table) {
            $table->renameColumn('floorName', 'floor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('floor', function (Blueprint $table) {
            $table->renameColumn('floor_name', 'floorName');
        });
    }
};
