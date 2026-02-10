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
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('floor_id');  // e.g., 3.1420000
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude'); // e.g., 101.7180000
        });

        // Optional: insert TRX coordinates for an existing asset (replace asset_id)
        DB::table('assets')->updateOrInsert(
            ['id' => '9'], // or use 'id' => 1 if you know the asset
            [
                'latitude' => 3.1420000,
                'longitude' => 101.7180000,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
