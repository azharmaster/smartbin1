<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capacity_settings', function (Blueprint $table) {

            // Add asset_id (nullable first to avoid breaking existing rows)
            $table->unsignedBigInteger('asset_id')->nullable()->after('id');

            // Foreign key
            $table->foreign('asset_id')
                  ->references('id')
                  ->on('assets')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('capacity_settings', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropColumn('asset_id');
        });
    }
};
