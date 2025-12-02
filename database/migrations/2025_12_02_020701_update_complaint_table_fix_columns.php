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
        Schema::table('complaint', function (Blueprint $table) {
            // rename the wrong table to proper plural if needed
            Schema::rename('complaint', 'complaints');
        });

        Schema::table('complaints', function (Blueprint $table) {
            // fix wrong column names
            $table->renameColumn('description8ju', 'description');

            // change asset_id to unsignedBigInteger (if referencing assets.id)
            $table->unsignedBigInteger('asset_id')->change();

            // Add foreign key (optional but recommended)
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // rollback foreign key
            $table->dropForeign(['asset_id']);

            // rollback column name change
            $table->renameColumn('description', 'description8ju');

            // rollback type if needed
            $table->string('asset_id')->change();
        });

        Schema::rename('complaints', 'complaint');
    }
};